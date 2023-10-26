<?php

namespace App\Services;

use App\Exceptions\HeathMapBatchMissingExcpetion;
use App\Exceptions\HeathMapNotPresentException;
use App\Exceptions\ZipCodeRankingsNotReadyException;
use App\Models\BusinessEntity;
use App\Models\BusinessEntityHeatMap;
use App\Models\BusinessEntityReviewCount;
use App\Models\BusinessEntityZipcodeRadiusRanking;
use App\Models\DailyAvgRank;
use App\Models\Keyword;
use App\Models\User;
use App\Models\WeeklyAvgRank;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * @var BusinessEntity
     */
    public $businessEntity;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $tz;

    /**
     * @var Carbon
     */
    public $carbon;

    /**
     * AnalyticsService constructor.
     * @param Keyword $keyword
     * @param BusinessEntity $businessEntity
     * @param User $user
     */
    public function __construct(Keyword $keyword, BusinessEntity $businessEntity, User $user)
    {
        $this->keyword = $keyword;
        $this->businessEntity = $businessEntity;
        $this->user = $user;
        $this->tz = $this->user->tz;
        $this->carbon = Carbon::now($this->tz);
    }

    /**
     * Get zipcode radius ranking
     *
     * @return array
     * @throws HeathMapBatchMissingExcpetion
     * @throws HeathMapNotPresentException
     * @throws ZipCodeRankingsNotReadyException
     */
    public function getZipcodeRadiusRanking()
    {
        $heathMap = BusinessEntityHeatMap::where('user_id', $this->user->id)->where('keyword_id', $this->keyword->id)->where('business_entity_id', $this->businessEntity->id)->first();

        if (!$heathMap) {
            activity()->event('PDF_REPORT_HEATH_MAP_MISSING')->log("Heath map missing for user " . $this->user->id . " keyword " . $this->keyword->id . " entity " . $this->businessEntity->id);
            throw new HeathMapNotPresentException("Heath map never generated.");
        }

        $lastBatchId = $heathMap->last_batch_id;

        $batch = DB::table('job_batches')
            ->where('id',  $lastBatchId)
            ->first();

        if (!$batch) {
            activity()->event('PDF_REPORT_HEATH_MAP_BATCH_MISSING')->log("Heath map batch missing for user " . $this->user->id . " keyword " . $this->keyword->id . " entity " . $this->businessEntity->id . " heath map " . $heathMap->id . " batch id " . $lastBatchId);
            throw new HeathMapBatchMissingExcpetion("Heath map batch is missing.");
        }

        $totalJobs = $batch->total_jobs;
        $pendingJobs = $batch->pending_jobs;
        $failedJobs = $batch->failed_jobs;

        $jobThreshold = $totalJobs * 0.5;

        $notCompletedJobs = $pendingJobs + $failedJobs;

        if ($notCompletedJobs > $jobThreshold) {
            $zipcodeRankingObject = BusinessEntityZipcodeRadiusRanking::select('batch_id')->where('heat_map_id', $heathMap->id)->where('business_entity_id', $this->businessEntity->id)->where('user_id', $this->user->id)->whereNotIn('batch_id', [$lastBatchId])->groupBy('batch_id')->orderByRaw('MAX(id) DESC')->first();

            if (!$zipcodeRankingObject->batch_id) {
                activity()->event('PDF_REPORT_HEATH_MAP_NOT_ENOUGH_RANKINGS')->log("Not enough eligible zipcode rankigs found for user " . $this->user->id . " keyword " . $this->keyword->id . " entity " . $this->businessEntity->id . " heath map " . $heathMap->id);
                throw new ZipCodeRankingsNotReadyException("Not enough eligible zipcode rankigs found.");
            }

            $businessEntityRadiusRankingCollection = BusinessEntityZipcodeRadiusRanking::where('heat_map_id', $heathMap->id)->where('business_entity_id', $this->businessEntity->id)->where('user_id', $this->user->id)->where('batch_id', $zipcodeRankingObject->batch_id)->orderBy('created_at', 'DESC')->get();
        } else {
            $businessEntityRadiusRankingCollection = BusinessEntityZipcodeRadiusRanking::where('heat_map_id', $heathMap->id)->where('business_entity_id', $this->businessEntity->id)->where('user_id', $this->user->id)->where('batch_id', $batch->id)->orderBy('created_at', 'DESC')->get();
        }

        $zipcodeRadiusRankingTotal = 0;
        $zipcodeRadiusRankingHigh = 0;
        $zipcodeRadiusRankingMedium = 0;
        $zipcodeRadiusRankingLow = 0;

        foreach ($businessEntityRadiusRankingCollection as $radiusRankingObject) {
            $lsaRank = $radiusRankingObject->lsa_rank;

            if ($lsaRank <= 3) {
                $zipcodeRadiusRankingHigh++;
            } elseif ($lsaRank > 3 && $lsaRank <= 10) {
                $zipcodeRadiusRankingMedium++;
            } else {
                $zipcodeRadiusRankingLow++;
            }

            $zipcodeRadiusRankingTotal++;
        }

        return [$zipcodeRadiusRankingTotal, $zipcodeRadiusRankingHigh, $zipcodeRadiusRankingMedium, $zipcodeRadiusRankingLow, $heathMap];
    }

    /**
     * Get top 5 competitors per weekly new reviews
     *
     * @return array
     */
    public function getTopCompetitorsWeeklyReviewCount()
    {
        $weekStart = $this->carbon->copy()->startOfWeek();
        $weekEnd = $this->carbon->copy()->endOfWeek();
        $lastWeekStart = $weekStart->copy()->subDays(7);
        $lastWeekEnd = $weekEnd->copy()->subDays(7);

        $weekStart->setTimezone('UTC');
        $weekEnd->setTimezone('UTC');
        $lastWeekStart->setTimezone('UTC');
        $lastWeekEnd->setTimezone('UTC');

        $currentWeeklyCountRanking = $this->getCompetitorsMaxCountCollection($weekStart, $weekEnd);

        if (!count($currentWeeklyCountRanking)) {
            $weekEnd = $this->carbon->copy();
            $weekStart = $this->carbon->copy()->subDays(7);
            $lastWeekEnd = $weekEnd->copy()->subDays(7);
            $lastWeekStart = $weekStart->copy()->subDays(7);
            $weekStart->setTimezone('UTC');
            $weekEnd->setTimezone('UTC');
            $lastWeekStart->setTimezone('UTC');
            $lastWeekEnd->setTimezone('UTC');

            $currentWeeklyCountRanking = $this->getCompetitorsMaxCountCollection($weekStart, $weekEnd);
            $lastWeeklyCountRanking = $this->getCompetitorsMaxCountCollection($lastWeekStart, $lastWeekEnd);
        } else {
            $lastWeeklyCountRanking = $this->getCompetitorsMaxCountCollection($lastWeekStart, $lastWeekEnd);
        }

        $currentWeeklyCountRanking = $currentWeeklyCountRanking->keyBy('business_entity_id');
        $lastWeeklyCountRanking = $lastWeeklyCountRanking->keyBy('business_entity_id');

        $countDifferenceArray = [];

        foreach ($currentWeeklyCountRanking as $businessEntityId => $weeklyCountData) {
            if ($lastWeeklyCountRanking->has($businessEntityId)) {
                $lastWeeklyCountData = $lastWeeklyCountRanking->get($businessEntityId);
                $thisWeekCount = (int) $weeklyCountData->max_count;
                $lastWeekCount = (int) $lastWeeklyCountData->max_count;

                $reviewCountDifference = $thisWeekCount - $lastWeekCount;

                array_push($countDifferenceArray, [
                   'business_entity_id' => $businessEntityId,
                   'business_entity_name' => $weeklyCountData->businessEntity->name,
                   'review_count_difference' => $reviewCountDifference,
                ]);

            }
        }

        usort($countDifferenceArray, function ($item1, $item2) {
            return $item2['review_count_difference'] <=> $item1['review_count_difference'];
        });

        $top5countWeeklyDifferenceArray = array_slice($countDifferenceArray, 0, 5);

        $totalNewReviews = 0;

        foreach ($top5countWeeklyDifferenceArray as $countWeeklyDifData) {
            $totalNewReviews = $totalNewReviews + $countWeeklyDifData['review_count_difference'];
        }

        foreach ($top5countWeeklyDifferenceArray as &$countWeeklyDifData) {
            $countWeeklyDifData['new_review_percent'] = round($countWeeklyDifData['review_count_difference'] / $totalNewReviews, 2) * 100;
        }

        return $top5countWeeklyDifferenceArray;
    }

    /**
     * Get competitors max count collection in given period
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getCompetitorsMaxCountCollection(Carbon $start, Carbon $end)
    {
        $collection = BusinessEntityReviewCount::with('businessEntity')
            ->select(DB::raw("business_entity_id, MAX(review_count) AS max_count"))
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', '!=', $this->businessEntity->id)
            ->where('timestamp', '>=', $start->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $end->format('Y-m-d H:i:s'))
            ->groupBy('business_entity_id')
            ->get();

        return $collection;
    }

    /**
     * Get top 5 competitors per weekly rank difference
     *
     * @return array
     */
    public function getTopCompetitorsWeeklyRankPercent()
    {
        $weekStart = $this->carbon->copy()->startOfWeek();
        $weekEnd = $this->carbon->copy()->endOfWeek();
        $lastWeekStart = $weekStart->copy()->subDays(7);
        $lastWeekEnd = $weekEnd->copy()->subDays(7);

        $weekStart->setTimezone('UTC');
        $weekEnd->setTimezone('UTC');
        $lastWeekStart->setTimezone('UTC');
        $lastWeekEnd->setTimezone('UTC');

        $currentWeeklyAvgRank = $this->getCompetitorsMaxRankCollection($weekStart, $weekEnd);

        if (!count($currentWeeklyAvgRank)) {
            $weekEnd = $this->carbon->copy();
            $weekStart = $this->carbon->copy()->subDays(7);
            $lastWeekEnd = $weekEnd->copy()->subDays(7);
            $lastWeekStart = $weekStart->copy()->subDays(7);
            $weekStart->setTimezone('UTC');
            $weekEnd->setTimezone('UTC');
            $lastWeekStart->setTimezone('UTC');
            $lastWeekEnd->setTimezone('UTC');

            $currentWeeklyAvgRank = $this->getCompetitorsMaxRankCollection($weekStart, $weekEnd);
            $lastWeeklyAvgRank = $this->getCompetitorsMaxRankCollection($lastWeekStart, $lastWeekEnd);
        } else {
            $lastWeeklyAvgRank = $this->getCompetitorsMaxRankCollection($lastWeekStart, $lastWeekEnd);
        }

        $currentWeeklyAvgRank = $currentWeeklyAvgRank->keyBy('business_entity_id');
        $lastWeeklyAvgRank = $lastWeeklyAvgRank->keyBy('business_entity_id');

        $rankDifferenceArray = [];

        foreach ($currentWeeklyAvgRank as $businessEntityId => $weeklyAvgRankData) {
            if ($lastWeeklyAvgRank->has($businessEntityId)) {
                $lastWeeklyRankAvgData = $lastWeeklyAvgRank->get($businessEntityId);

                $thisWeekAvg = (float) $weeklyAvgRankData->best_weekly_ranking;
                $lastWeekAvg = (float) $lastWeeklyRankAvgData->best_weekly_ranking;

                if ($thisWeekAvg > $lastWeekAvg) {
                    $percentChange = (1 - $thisWeekAvg / $lastWeekAvg) * 100;
                    $percentChange = round($percentChange, 2);
                } elseif ($thisWeekAvg < $lastWeekAvg) {
                    $percentChange = (1 - $lastWeekAvg / $thisWeekAvg) * 100;
                    $percentChange = round($percentChange, 2);
                    $percentChange = $percentChange * (-1);
                } else {
                    $percentChange = 0;
                }

                array_push($rankDifferenceArray, [
                    'business_entity_id' => $businessEntityId,
                    'business_entity_name' => $weeklyAvgRankData->businessEntity->name,
                    'percent_change' => $percentChange,
                    'this_week_avg_rank' => $thisWeekAvg,
                ]);

            }
        }

        usort($rankDifferenceArray, function ($item1, $item2) {
            return $item2['percent_change'] <=> $item1['percent_change'];
        });

        $top5WeeklyAvgDifferenceArray = array_slice($rankDifferenceArray, 0, 5);

        return $top5WeeklyAvgDifferenceArray;

    }

    /**
     * Get competitors max count collection in given period
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getCompetitorsMaxRankCollection(Carbon $start, Carbon $end)
    {
        $collection = WeeklyAvgRank::with('businessEntity')
            ->select(DB::raw("business_entity_id, MIN(rank_avg) AS best_weekly_ranking"))
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', '!=', $this->businessEntity->id)
            ->where('current_date', '>=', $start->format('Y-m-d H:i:s'))
            ->where('current_date', '<=', $end->format('Y-m-d H:i:s'))
            ->groupBy('business_entity_id')
            ->get();

        return $collection;
    }

    /////////////////////////

    /**
     * Get daily average rank
     *
     * @return array|string
     */
    public function getDailyAvgRank()
    {
        $dayEnd = $this->carbon->copy();
        $dayStart = $this->carbon->copy()->subHours(24);
        $dayEnd->setTimezone('UTC');
        $dayStart->setTimezone('UTC');

        $currentDailyAvg = DailyAvgRank::select('rank_avg')
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', $this->businessEntity->id)
            ->where('updated_at', '>=', $dayStart->format('Y-m-d H:i:s'))
            ->where('updated_at', '<=', $dayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        $lastWeekDayEnd = $dayEnd->copy()->subDays(7);
        $lastWeekDayStart = $dayStart->copy()->subDays(7);

        $lastDailyAvg = DailyAvgRank::select('rank_avg')
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', $this->businessEntity->id)
            ->where('updated_at', '>=', $lastWeekDayStart->format('Y-m-d H:i:s'))
            ->where('updated_at', '<=', $lastWeekDayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$currentDailyAvg || !$lastDailyAvg) {
            return 'N/A';
        }

        $currentAvgValue = (float) $currentDailyAvg->rank_avg;
        $lastWeekAvgValue = (float) $lastDailyAvg->rank_avg;

        if ($currentAvgValue > $lastWeekAvgValue) {
            $percentChange = (1 - $currentAvgValue / $lastWeekAvgValue) * 100;
            $percentChange = round($percentChange, 2);
        } elseif ($currentAvgValue < $lastWeekAvgValue) {
            $percentChange = (1 - $lastWeekAvgValue / $currentAvgValue) * 100;
            $percentChange = round($percentChange, 2);
            $percentChange = $percentChange * (-1);
        } else {
            $percentChange = 0;
        }

        return [$currentAvgValue, $percentChange];
    }

    /**
     * Get weekly average rank (on for current day)
     *
     * @return array|string
     */
    public function getWeeklyAvgRank()
    {
        $dayEnd = $this->carbon->copy();
        $dayStart = $this->carbon->copy()->subHours(24);
        $dayEnd->setTimezone('UTC');
        $dayStart->setTimezone('UTC');

        $currentWeeklyAvg = WeeklyAvgRank::select('rank_avg')
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', $this->businessEntity->id)
            ->where('current_date', '>=', $dayStart->format('Y-m-d H:i:s'))
            ->where('current_date', '<=', $dayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        $lastWeekDayEnd = $dayEnd->copy()->subDays(7);
        $lastWeekDayStart = $dayStart->copy()->subDays(7);

        $lastWeeklyAvg = WeeklyAvgRank::select('rank_avg')
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', $this->businessEntity->id)
            ->where('current_date', '>=', $lastWeekDayStart->format('Y-m-d H:i:s'))
            ->where('current_date', '<=', $lastWeekDayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$currentWeeklyAvg || !$lastWeeklyAvg) {
            return 'N/A';
        }

        $currentAvgValue = (float) $currentWeeklyAvg->rank_avg;
        $lastAvgValue = (float) $lastWeeklyAvg->rank_avg;

        if ($currentAvgValue > $lastAvgValue) {
            $percentChange = (1 - $currentAvgValue / $lastAvgValue) * 100;
            $percentChange = round($percentChange, 2);
        } elseif ($currentAvgValue < $lastAvgValue) {
            $percentChange = (1 - $lastAvgValue / $currentAvgValue) * 100;
            $percentChange = round($percentChange, 2);
            $percentChange = $percentChange * (-1);
        } else {
            $percentChange = 0;
        }

        return [$currentAvgValue, $percentChange];
    }

    /**
     * Get daily reviews
     *
     * @return array|string
     */
    public function getDailyReviews()
    {
        $dayEnd = $this->carbon->copy();
        $dayStart = $this->carbon->copy()->subHours(24);
        $dayEnd->setTimezone('UTC');
        $dayStart->setTimezone('UTC');

        $currentDailyCount = BusinessEntityReviewCount::select('review_count')
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', $this->businessEntity->id)
            ->where('timestamp', '>=', $dayStart->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $dayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        $lastDayEnd = $dayEnd->copy()->subHours(24);
        $lastDayStart = $dayStart->copy()->subHours(24);

        $lastDailyCount = BusinessEntityReviewCount::select('review_count')
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', $this->businessEntity->id)
            ->where('timestamp', '>=', $lastDayStart->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $lastDayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$currentDailyCount || !$lastDailyCount) {
            return 'N/A';
        }

        $currentReviewCount = (integer) $currentDailyCount->review_count;
        $lastReviewCount = (integer) $lastDailyCount->review_count;

        //$currentReviewCount = 4;

        $todayReviews = $currentReviewCount - $lastReviewCount;

        if ($currentReviewCount < $lastReviewCount) {
            $percentChange = (1 - $currentReviewCount / $lastReviewCount) * 100;
            $percentChange = round($percentChange, 2);
            $percentChange = $percentChange * (-1);
        } elseif ($currentReviewCount > $lastReviewCount) {
            $percentChange = (1 - $currentReviewCount / $lastReviewCount) * 100;
            $percentChange = $percentChange * (-1);
            $percentChange = round($percentChange, 2);
        } else {
            $percentChange = 0;
        }

        return [$todayReviews, $percentChange];
    }

    /**
     * Calculate weekly reviews metrics
     *
     * @return array|string
     */
    public function getWeeklyReviews()
    {
        $dayEnd = $this->carbon->copy();
        $dayStart = $this->carbon->copy()->subHours(24);
        $dayEnd->setTimezone('UTC');
        $dayStart->setTimezone('UTC');
        $weekStart = $dayStart->copy()->startOfWeek();
        $weekEnd = $dayEnd->copy()->endOfWeek();

        $currentWeeklyCount = BusinessEntityReviewCount::selectRaw('MAX(review_count) as max_review_count')
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', $this->businessEntity->id)
            ->where('timestamp', '>=', $weekStart->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $weekEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        $lastWeekStart = $weekStart->copy()->subDays(7);
        $lastWeekEnd = $weekEnd->copy()->subDays(7);

        $lastWeeklyCount = BusinessEntityReviewCount::selectRaw('MAX(review_count) as max_review_count')
            ->where('keyword_id', $this->keyword->id)
            ->where('business_entity_id', $this->businessEntity->id)
            ->where('timestamp', '>=', $lastWeekStart->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $lastWeekEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$currentWeeklyCount->max_review_count || !$lastWeeklyCount->max_review_count) {
            return 'N/A';
        }

        $currentReviewCount = (integer) $currentWeeklyCount->max_review_count;
        $lastReviewCount = (integer) $lastWeeklyCount->max_review_count;

        //$currentReviewCount = 250;

        $weeklyReviews = $currentReviewCount - $lastReviewCount;

        if ($currentReviewCount < $lastReviewCount) {
            $percentChange = (1 - $currentReviewCount / $lastReviewCount) * 100;
            $percentChange = round($percentChange, 2);
            $percentChange = $percentChange * (-1);
        } elseif ($currentReviewCount > $lastReviewCount) {
            $percentChange = (1 - $currentReviewCount / $lastReviewCount) * 100;
            $percentChange = $percentChange * (-1);
            $percentChange = round($percentChange, 2);
        } else {
            $percentChange = 0;
        }

        return [$weeklyReviews, $percentChange];
    }
}
