<?php

namespace App\Repositories;

use App\Models\BusinessEntity;
use App\Models\BusinessEntityRanking;
use App\Models\DailyAvgRank;
use App\Models\Keyword;
use App\Models\User;
use App\Models\UserEntityPreference;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Jobs\CreateBusinessEntityHeatMap;

class KeywordMetricRepository
{
    /**
     * @var BusinessEntity
     */
    public $businessEntity;

    /**
     * @var Collection
     */
    public $businessEntities;

    /**
     * @var UserEntityPreference
     */
    public $userPreference;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var int
     */
    public $keywordId;

    /**
     * @var DailyAvgRank
     */
    public $dailyAvgRank;

    /**
     * @var DailyAvgRank
     */
    public $entityRanking;

    /**
     * @var DailyAvgRank
     */
    public $keyword;

    /**
     * KeywordMetricRepository constructor.
     * @param BusinessEntity $businessEntity
     * @param UserEntityPreference $entityPreference
     */
    public function __construct(
        BusinessEntity $businessEntity,
        UserEntityPreference $entityPreference,
        DailyAvgRank $dailyAvgRank,
        BusinessEntityRanking $businessEntityRanking,
        Keyword $keyword
    ) {
        $this->businessEntity = $businessEntity;
        $this->userPreference = $entityPreference;
        $this->dailyAvgRank = $dailyAvgRank;
        $this->entityRanking = $businessEntityRanking;
        $this->keyword = $keyword;
    }

    /**
     * Get reporting periods
     *
     * @param int $userId
     * @return array
     */
    public function getReportingPeriods(int $userId)
    {
        $keywordCollection = $this->keyword->where('user_id', $userId)->get();

        $keywordReportArray = [];

        foreach ($keywordCollection as $keyword) {
            $minTimeStamp = $keyword->created_at->format('Y-m-d');
            $maxTimeStamp = $this->dailyAvgRank->where('keyword_id', $keyword->id)->orderBy('id', 'DESC')->first()->created_at->format('Y-m-d');
            array_push($keywordReportArray, [
                'keyword_id' => $keyword->id,
                'keyword' => $keyword->original_keyword,
                'min_time' => $minTimeStamp,
                'max_time' => $maxTimeStamp,
            ]);
        }


        $weekReportData = [];

        $periodStart = null;

        foreach ($keywordReportArray as $reportData) {
            $carbonPeriod = CarbonPeriod::create($reportData['min_time'], $reportData['max_time']);
            $periodCount = $carbonPeriod->count();
            $startCarbon = Carbon::parse($reportData['min_time']);
            if (!$startCarbon->isMonday()) {
                $dayOfWeek = $startCarbon->dayOfWeek;
                $hasWeeksCount = $dayOfWeek + $periodCount;

                if ($hasWeeksCount > 7) {
                    $weekStart = $startCarbon->startOf('week');
                    $diff = $weekStart->diffInWeeks($reportData['max_time']);

                    if ($diff) {
                        $startDate = $weekStart;
                        $weekReportData[$reportData['keyword']] = [];
                        for ($i = 1; $i <= $diff; $i++) {
                            array_push($weekReportData[$reportData['keyword']], [
                                'week_num' => $i,
                                'title' => "Week $i",
                                'start_date' => $startDate->format('Y-m-d'),
                                'end_date' => $startDate->addWeek()->format('Y-m-d'),
                                'keyword_id' => $reportData['keyword_id'],
                            ]);
                        }
                    }
                }
            } else {
                /** @var CarbonPeriod $carbonPeriod */
                $diff = Carbon::parse($reportData['min_time'])->diffInWeeks($reportData['max_time']);

                if ($diff) {
                    $startDate = Carbon::parse($reportData['min_time']);
                    $weekReportData[$reportData['keyword']] = [];
                    for ($i = 1; $i <= $diff; $i++) {
                        array_push($weekReportData[$reportData['keyword']], [
                            'week_num' => $i,
                            'title' => "Week $i",
                            'start_date' => $startDate->format('Y-m-d'),
                            'end_date' => $startDate->addWeek()->format('Y-m-d'),
                            'keyword_id' => $reportData['keyword_id'],
                        ]);
                    }
                }
            }
        }

        return $weekReportData;
    }

//    private function formatWeekReportData(int $weeks, array $reportData, Carbon $startDate)
//    {
//        $weekReportData = [];
//
//        for ($i = 1; $i <= $weeks; $i++) {
//            array_push($weekReportData[$reportData['keyword']], [
//                'week_num' => $i,
//                'title' => "Week $i",
//                'start_date' => $startDate->format('Y-m-d'),
//                'end_date' => $startDate->addWeek()->format('Y-m-d'),
//                'keyword_id' => $reportData['keyword_id'],
//            ]);
//        }
//
//        return $weekReportData;
//    }

    /**
     * Get entities by period
     *
     * @param int $userId
     * @param int $keywordId
     * @param string $periodStart
     * @param string $periodEnd
     * @return mixed
     */
    public function getEntitiesByPeriod(int $userId, int $keywordId, string $periodStart, string $periodEnd)
    {
        $this->userId = $userId;
        $this->keywordId = $keywordId;
        $businessEntityCollection = $this->businessEntity->where('user_id', $userId)->where('keyword_id', $keywordId)->whereDate('updated_at', '<=', $periodEnd)->get();

        return $businessEntityCollection;
    }

    /**
     * Get rankings by period
     *
     * @param Collection $businessEntityCollection
     * @param string $periodStart
     * @param string $periodEnd
     * @return array
     */
    public function getRankingsByPeriod(Collection $businessEntityCollection, string $periodStart, string $periodEnd)
    {
        $firstPartQuery = "SELECT created_at,";

        $selectQuery = '';
        foreach ($businessEntityCollection as $businessEntity){
            $selectQuery.="SUM(CASE WHEN business_entity_id = " . $businessEntity->id . " THEN lsa_rank ELSE 0 END) AS '" . $businessEntity->id . "', ";
        }

        $selectQuery = substr_replace($selectQuery ,"",-2);
        $thirdPartQuery = " FROM business_entities_ranking WHERE (user_id = $this->userId AND keyword_id = $this->keywordId) AND date(created_at) BETWEEN '$periodStart' AND '$periodEnd' GROUP BY created_at ORDER BY created_at ASC;";
        $results = DB::select( DB::raw($firstPartQuery . $selectQuery . $thirdPartQuery));

        return $results;
    }

    /**
     * @param int $userId
     * @param int $keywordId
     * @param string $selectedDate
     * @param null $userPreference
     * @return array
     */
    public function getBusinessEntitiesMapping(int $userId, int $keywordId, string $selectedDate, $userPreference = null)
    {
        $this->userId = $userId;
        $this->keywordId = $keywordId;

        ///If column preference is not interfering
        if ($userPreference) {

            $preferredEntityModel = $this->businessEntity->find($userPreference);

            $businessEntityCollection = $this->getBusinessEntityCollection($keywordId, $selectedDate, $userPreference);
            //$businessEntityCollection = $this->businessEntity->where(['user_id' => $userId, 'keyword_id' => $keywordId])->whereNotIn('id', [$userPrefernece])->get();

            $businessEntityCollection->prepend($preferredEntityModel);

            $businessEntityMapping = $this->createBusinessEntityMapping($businessEntityCollection);

            //$businessEntityMapping = $businessEntityCollection->pluck('name');

            return [$businessEntityCollection, $businessEntityMapping];
        }

        //At this point old logic (the one commented out) is replaced in favour of average displaying
        $businessEntityCollection = $this->getBusinessEntityCollection($keywordId, $selectedDate);

        $businessEntityMapping = $this->createBusinessEntityMapping($businessEntityCollection);
        //$businessEntityCollection = $this->businessEntity->where(['user_id' => $userId, 'keyword_id' => $keywordId])->get();

        return [$businessEntityCollection, $businessEntityMapping];
    }

    /**
     * Create business entity mapping from collection
     *
     * @param Collection $businessEntityCollection
     * @return Collection
     *
     */
    private function createBusinessEntityMapping(Collection $businessEntityCollection)
    {
        $businessEntityMapping = collect([]);

        foreach ($businessEntityCollection as $key => $entity) {
            $businessEntityMapping->push([
                'id' => $entity->id,
                'name' => $entity->name,
            ]);
        }

        return $businessEntityMapping;
    }


    /**
     * Get business entity collection from daily averages
     *
     * @param $keywordId
     * @param $selectedDate
     * @param null $exclude
     * @return Collection
     */
    private function getBusinessEntityCollection($keywordId, $selectedDate, $exclude = null)
    {
        //Add cacheing here if averages hasn't been generated yet (around midnight)


//        $query = DB::table('lsa_business_entities')
//            ->select(['lsa_business_entities.id', 'lsa_business_entities.name'])
//            ->join('lsa_ranking_average', function ($join) {
//                $join->on('lsa_business_entities.id', '=', 'lsa_ranking_average.business_entity_id')->on('lsa_business_entities.keyword_id', '=', 'lsa_ranking_average.keyword_id');
//            })->where('lsa_business_entities.keyword_id', $keywordId)
//            ->where('lsa_ranking_average.date', $selectedDate);
//
//        if ($exclude) {
//            $query->whereNotIn('lsa_business_entities.id', [$exclude]);
//        }
//
//        $businessEntityCollection = $query->orderBy('lsa_ranking_average.rank_avg', 'asc')->get();


        $user = Auth::user();
        $timezone = $user->tz;
        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $selectedDate, $timezone);
        $dayStart = $carbon->startOfDay();
        $dayStart->setTimezone('UTC');
        $dayStart = $dayStart->format('Y-m-d');

        $dayEnd = $carbon->addHours(24);
        $dayEnd = $dayEnd->format('Y-m-d');

        $avgRank = $this->dailyAvgRank->where(['keyword_id' => $keywordId])->where('date', '>=', $dayStart)->where('date', '<=', $dayEnd);

        if ($exclude) {
            $businessEntityIds = $avgRank->whereNotIn('business_entity_id', [$exclude])->orderBy('rank_avg', 'ASC')->get('business_entity_id')->pluck('business_entity_id')->toArray();
        } else {
            $businessEntityIds = $avgRank->orderBy('rank_avg', 'ASC')->get('business_entity_id')->pluck('business_entity_id')->toArray();
        }

        if (!count($businessEntityIds)) {
            return collect([]);
        }

        $businessEntityIds = array_unique($businessEntityIds);

        $businessEntityCollection = $this->businessEntity->whereIn('id', $businessEntityIds)->orderByRaw("FIELD(id, " . implode(", ", $businessEntityIds) . ")")->get();

        return $businessEntityCollection;
    }

    /**
     * Get business entities ranking
     *
     * @param Collection $businessEntityCollection
     * @param string $day
     * @param string $date
     * @param Request $request
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function getBusinessEntitiesRanking(Collection $businessEntityCollection, string $day, string $date, Request $request, int $page)
    {
        $user = Auth::user();
        $timezone = $user->tz;
        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $date, $timezone);

        $dayStart = $carbon->startOfDay();
        $dayStart->setTimezone('UTC');
        $dayStart = $dayStart->format('Y-m-d H:i:s');

        $dayEnd = $carbon->addHours(24);
        $dayEnd = $dayEnd->format('Y-m-d H:i:s');

        if (!count($businessEntityCollection)) {
            $paginatedCollection = new LengthAwarePaginator(collect([]), 0, 20, 1);
            return $paginatedCollection;
        }

        $firstPartQueryCount = "SELECT count(grouped.created_at) AS total_count FROM (";
        $firstPartQuery = "SELECT created_at,";

        $selectQuery = '';
        foreach ($businessEntityCollection as $businessEntity){
            $selectQuery.="SUM(CASE WHEN business_entity_id = " . $businessEntity->id . " THEN lsa_rank ELSE 0 END) AS '" . $businessEntity->id . "', ";
        }

        $selectQuery = substr_replace($selectQuery ,"",-2);

        $offset = 20 * ($page - 1);

        $thirdPartQuery = " FROM business_entities_ranking WHERE (user_id = $this->userId AND keyword_id = $this->keywordId) AND created_at BETWEEN '$dayStart' AND '$dayEnd' GROUP BY created_at ORDER BY created_at DESC LIMIT 20 OFFSET $offset;";
        $thirdPartQueryCount = " FROM business_entities_ranking WHERE (user_id = $this->userId AND keyword_id = $this->keywordId) AND created_at BETWEEN '$dayStart' AND '$dayEnd' GROUP BY created_at ORDER BY created_at DESC) AS grouped";

        $resultsCount = DB::select( DB::raw($firstPartQueryCount . $firstPartQuery . $selectQuery . $thirdPartQueryCount));

        $total = $resultsCount[0]->total_count;

        $results = DB::select( DB::raw($firstPartQuery . $selectQuery . $thirdPartQuery));

        $paginatedCollection = new LengthAwarePaginator($results, $total, 20, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return $paginatedCollection;
    }

    /**
     * Toggle user preference
     *
     * @param int $userId
     * @param int $keywordId
     * @param string $businessId
     * @param null $reset
     * @return bool
     */
    public function toggleUserPreference(int $userId, int $keywordId, string $businessId, $reset = null)
    {
        if ($reset) {
            $this->userPreference->where('user_id', $userId)->where('keyword_id', $keywordId)->delete();
            return true;
        }

        $totalPreferences = $this->userPreference->where('user_id', $userId)->where('keyword_id', $keywordId)->count();

        $keywordPreferences = $this->userPreference->where('user_id', $userId)->where('keyword_id', $keywordId)->where('business_entity_id', $businessId)->count();

        if ($totalPreferences >= 1 && $keywordPreferences == 0) {
            return true;
        }

        $this->userPreference->create(['user_id' => $userId, 'keyword_id' => $keywordId, 'business_entity_id' => $businessId]);

        /** @var User $user */
        $user = User::find($userId);

        if ($user->isPaid()) {
            CreateBusinessEntityHeatMap::dispatch(['user_id' => $userId, 'keyword_id' => $keywordId, 'business_entity_id' => $businessId])->onQueue('radius');
        }

        return true;
    }
}
