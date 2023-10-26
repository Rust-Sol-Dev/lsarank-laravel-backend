<?php

namespace App\Http\Livewire;

use App\Models\BusinessEntityReviewCount;
use App\Models\DailyAvgRank;
use App\Models\Keyword;
use App\Models\User;
use App\Models\UserEntityPreference;
use App\Models\WeeklyAvgRank;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AverageMetrics extends Component
{
    /**
     * @var string
     */
    public $weeklyRank;

    /**
     * @var integer
     */
    public $weeklyPercentage;

    /**
     * @var string
     */
    public $weeklyTrend = 'up';

    /**
     * @var bool
     */
    public $weeklyDataMissing = false;

    /**
     * @var string
     */
    public $dailyRank;

    /**
     * @var integer
     */
    public $dailyPercentage;

    /**
     * @var string
     */
    public $dailyTrend = 'up';

    /**
     * @var bool
     */
    public $dailyDataMissing = false;

    /**
     * @var integer
     */
    public $weeklyReviews;

    /**
     * @var integer
     */
    public $weeklyReviewsPercentage;

    /**
     * @var string
     */
    public $weeklyReviewsTrend = 'up';

    /**
     * @var bool
     */
    public $weeklyReviewsDataMissing = false;

    /**
     * @var integer
     */
    public $dailyReviews;

    /**
     * @var integer
     */
    public $dailyReviewsPercentage;

    /**
     * @var string
     */
    public $dailyReviewsTrend = 'up';

    /**
     * @var bool
     */
    public $dailyReviewsDataMissing = false;

    /**
     * @var bool
     */
    public $show = false;

    /**
     * @var array
     */
    protected $listeners = ['toggleAnalytics', 'filterAnalyticsDashboard'];

    /**
     * Mount the component
     *
     * @param Keyword $keyword
     * @param string $currentDate
     */
    public function mount(Keyword $keyword, string $currentDate)
    {
        $this->fetchData($keyword, $currentDate);
    }

    /**
     * Toggle analytics
     *
     * @param array $analyticsData
     */
    public function toggleAnalytics(array $analyticsData)
    {
        $keywordId = $analyticsData['keywordId'];
        $currentDate = $analyticsData['currentDate'];

        $keyword = Keyword::find($keywordId);

        $this->mount($keyword, $currentDate);
    }

    /**
     * Apply daily filter to average analytics dashboard
     *
     * @param array $filterData
     */
    public function filterAnalyticsDashboard(array $filterData)
    {
        $currentDate = $filterData['currentDate'];
        $keywordId = $filterData['keywordId'];

        $keyword = Keyword::find($keywordId);

        $this->mount($keyword, $currentDate);
    }

    /**
     * Component updated
     *
     * @param Keyword $keyword
     * @param string $currentDate
     */
    public function updated(Keyword $keyword, string $currentDate)
    {
        $this->fetchData($keyword, $currentDate);
    }

    /**
     * Fetch data
     *
     * @param Keyword $keyword
     * @param string $currentDate
     */
    private function fetchData(Keyword $keyword, string $currentDate)
    {
        /** @var User $user */
        $user = Auth::user();
        $timezone = $user->tz;
        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate, $timezone);
        $dayEnd = $carbon->copy();
        $dayStart = $carbon->copy()->subHours(24);
        $weekStart = $dayStart->copy()->startOfWeek();
        $weekEnd = $dayEnd->copy()->endOfWeek();
        $dayStart->setTimezone('UTC');
        $dayEnd->setTimezone('UTC');
        $weekStart->setTimezone('UTC');
        $weekEnd->setTimezone('UTC');

        $preference = $user->preference($keyword->id)->first();

        if ($preference) {
            $this->show = true;

            $this->calculateWeeklyAvgRank($keyword, $preference, $dayStart, $dayEnd);
            $this->calculateDailyAvgRank($keyword, $preference, $dayStart, $dayEnd);
            $this->calculateDailyReviews($keyword, $preference, $dayStart, $dayEnd);
            $this->calculateWeeklyReviews($keyword, $preference, $weekStart, $weekEnd);
        } else {
            $this->show = false;
        }
    }

    /**
     * Calculate weekly reviews metrics
     *
     * @param Keyword $keyword
     * @param UserEntityPreference $preference
     * @param Carbon $dayStart
     * @param Carbon $dayEnd
     * @return bool
     */
    private function calculateWeeklyReviews(Keyword $keyword, UserEntityPreference $preference, Carbon $weekStart, Carbon $weekEnd)
    {
        $currentWeeklyCount = BusinessEntityReviewCount::selectRaw('MAX(review_count) as max_review_count')
            ->where('keyword_id', $keyword->id)
            ->where('business_entity_id', $preference->business_entity_id)
            ->where('timestamp', '>=', $weekStart->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $weekEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        $lastWeekStart = $weekStart->copy()->subDays(7);
        $lastWeekEnd = $weekEnd->copy()->subDays(7);

        $lastWeeklyCount = BusinessEntityReviewCount::selectRaw('MAX(review_count) as max_review_count')
            ->where('keyword_id', $keyword->id)
            ->where('business_entity_id', $preference->business_entity_id)
            ->where('timestamp', '>=', $lastWeekStart->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $lastWeekEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$currentWeeklyCount->max_review_count || !$lastWeeklyCount->max_review_count) {
            $this->weeklyReviewsDataMissing = true;
            return true;
        }

        $currentReviewCount = (integer) $currentWeeklyCount->max_review_count;
        $lastReviewCount = (integer) $lastWeeklyCount->max_review_count;

        $this->weeklyReviews = $currentReviewCount - $lastReviewCount;

        if ($this->weeklyReviews == 0) {
            $this->weeklyReviewsTrend = 'no-trend';
            $this->weeklyReviewsPercentage = 0;
            return true;
        }

        if ($currentReviewCount < $lastReviewCount) {
            $this->weeklyReviewsTrend = 'down';
        }

        if ($this->weeklyReviewsTrend == 'up') {
            $percentChange = (1 - $currentReviewCount / $lastReviewCount) * 100;
        } else {
            $percentChange = (1 - $lastReviewCount / $currentReviewCount) * 100;
        }

        $this->weeklyReviewsPercentage = abs(round($percentChange,2));
    }


    /**
     * Calculate daily reviews metrics
     *
     * @param Keyword $keyword
     * @param UserEntityPreference $preference
     * @param Carbon $dayStart
     * @param Carbon $dayEnd
     * @return bool
     */
    private function calculateDailyReviews(Keyword $keyword, UserEntityPreference $preference, Carbon $dayStart, Carbon $dayEnd)
    {
        $currentDailyCount = BusinessEntityReviewCount::select('review_count')
            ->where('keyword_id', $keyword->id)
            ->where('business_entity_id', $preference->business_entity_id)
            ->where('timestamp', '>=', $dayStart->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $dayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        $lastDayStart = $dayStart->copy()->subHours(24);
        $lastDayEnd = $dayEnd->copy()->subHours(24);

        $lastDailyCount = BusinessEntityReviewCount::select('review_count')
            ->where('keyword_id', $keyword->id)
            ->where('business_entity_id', $preference->business_entity_id)
            ->where('timestamp', '>=', $lastDayStart->format('Y-m-d H:i:s'))
            ->where('timestamp', '<=', $lastDayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$currentDailyCount || !$lastDailyCount) {
            $this->dailyReviewsDataMissing = true;
            return true;
        }

        $currentReviewCount = (integer) $currentDailyCount->review_count;
        $lastReviewCount = (integer) $lastDailyCount->review_count;

        $this->dailyReviews = $currentReviewCount - $lastReviewCount;

        if ($this->dailyReviews == 0) {
            $this->dailyReviewsTrend = 'no-trend';
            $this->dailyReviewsPercentage = 0;
            return true;
        }

        if ($currentReviewCount < $lastReviewCount) {
            $this->dailyReviewsTrend = 'down';
        }

        if ($this->dailyReviewsTrend == 'up') {
            $percentChange = (1 - $currentReviewCount / $lastReviewCount) * 100;
        } else {
            $percentChange = (1 - $lastReviewCount / $currentReviewCount) * 100;
        }

        $this->dailyReviewsPercentage = abs(round($percentChange,2));
    }

    /**
     * Calculate daily average
     *
     * @param Keyword $keyword
     * @param UserEntityPreference $preference
     * @param Carbon $dayStart
     * @param Carbon $dayEnd
     * @return bool
     */
    private function calculateDailyAvgRank(Keyword $keyword, UserEntityPreference $preference, Carbon $dayStart, Carbon $dayEnd)
    {
        $currentDailyAvg = DailyAvgRank::select('rank_avg')
            ->where('keyword_id', $keyword->id)
            ->where('business_entity_id', $preference->business_entity_id)
            ->where('updated_at', '>=', $dayStart->format('Y-m-d H:i:s'))
            ->where('updated_at', '<=', $dayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        $lastWeekDayStart = $dayStart->copy()->subDays(7);
        $lastWeekDayEnd = $dayEnd->copy()->subDays(7);

        $lastDailyAvg = DailyAvgRank::select('rank_avg')
            ->where('keyword_id', $keyword->id)
            ->where('business_entity_id', $preference->business_entity_id)
            ->where('updated_at', '>=', $lastWeekDayStart->format('Y-m-d H:i:s'))
            ->where('updated_at', '<=', $lastWeekDayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$currentDailyAvg || !$lastDailyAvg) {
            $this->dailyDataMissing = true;
            return true;
        }

        $this->dailyRank = $currentDailyAvg->rank_avg;
        $currentAvgValue = (float) $currentDailyAvg->rank_avg;
        $lastAvgValue = (float) $lastDailyAvg->rank_avg;

        if ($currentAvgValue == $lastAvgValue) {
            $this->dailyTrend = 'no-trend';
            $this->dailyPercentage = 0;
            return true;
        }

        if ($currentAvgValue > $lastAvgValue) {
            $this->dailyTrend = 'down';
        }

        if ($this->dailyTrend == 'up') {
            $percentChange = (1 - $lastAvgValue / $currentAvgValue) * 100;
        } else {
            $percentChange = (1 - $currentAvgValue / $lastAvgValue) * 100;
        }

        $this->dailyPercentage = abs(round($percentChange,2));
    }

    /**
     * Calculate weekly average
     *
     * @param Keyword $keyword
     * @param UserEntityPreference $preference
     * @param Carbon $dayStart
     * @param Carbon $dayEnd
     * @return bool
     */
    private function calculateWeeklyAvgRank(Keyword $keyword, UserEntityPreference $preference, Carbon $dayStart, Carbon $dayEnd)
    {
        $currentWeeklyAvg = WeeklyAvgRank::select('rank_avg')
            ->where('keyword_id', $keyword->id)
            ->where('business_entity_id', $preference->business_entity_id)
            ->where('current_date', '>=', $dayStart->format('Y-m-d H:i:s'))
            ->where('current_date', '<=', $dayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        $lastWeekDayStart = $dayStart->copy()->subDays(7);
        $lastWeekDayEnd = $dayEnd->copy()->subDays(7);

        $lastWeeklyAvg = WeeklyAvgRank::select('rank_avg')
            ->where('keyword_id', $keyword->id)
            ->where('business_entity_id', $preference->business_entity_id)
            ->where('current_date', '>=', $lastWeekDayStart->format('Y-m-d H:i:s'))
            ->where('current_date', '<=', $lastWeekDayEnd->format('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        if (!$currentWeeklyAvg || !$lastWeeklyAvg) {
            $this->weeklyDataMissing = true;
            return true;
        }

        $this->weeklyRank = $currentWeeklyAvg->rank_avg;
        $currentAvgValue = (float) $currentWeeklyAvg->rank_avg;
        $lastAvgValue = (float) $lastWeeklyAvg->rank_avg;

        if ($currentAvgValue == $lastAvgValue) {
            $this->weeklyTrend = 'no-trend';
            $this->weeklyPercentage = 0;
            return true;
        }

        if ($currentAvgValue > $lastAvgValue) {
            $this->weeklyTrend = 'down';
        }

        if ($this->weeklyTrend == 'up') {
            $percentChange = (1 - $lastAvgValue / $currentAvgValue) * 100;
        } else {
            $percentChange = (1 - $currentAvgValue / $lastAvgValue) * 100;
        }

        $this->weeklyPercentage = abs(round($percentChange,2));
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.average-metrics');
    }
}
