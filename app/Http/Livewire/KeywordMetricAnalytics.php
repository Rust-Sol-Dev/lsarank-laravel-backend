<?php

namespace App\Http\Livewire;

use App\Models\BusinessEntity;
use App\Models\BusinessEntityRanking;
use App\Models\DailyAvgRank;
use App\Models\Keyword;
use App\Models\User;
use App\Models\UserEntityPreference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class KeywordMetricAnalytics extends Component
{
    /**
     * @var bool
     */
    public $show = false;

    /**
     * @var string
     */
    private $currentDate;

    /**
     * @var Keyword
     */
    private $keyword;

    /**
     * @var string
     */
    public $keywordName;

    /**
     * @var int
     */
    private $keywordId;

    /**
     * @var UserEntityPreference
     */
    private $preference;

    public $names;

    public $values;

    /**
     * @var array
     */
    protected $listeners = ['toggleAnalytics', 'filterGraph', 'redraw' => '$refresh'];

    /**
     * Mount the component
     *
     * @param Keyword $keyword
     * @param string $currentDate
     */
    public function mount(Keyword $keyword, string $currentDate)
    {
        $this->currentDate = $currentDate;
        $this->keyword = $keyword;

        $this->displayTemplate($keyword->id);
    }

    /**
     * Toggle analytics
     *
     * @param array $analyticsData
     */
    public function toggleAnalytics(array $analyticsData)
    {
        $this->currentDate = $analyticsData['currentDate'];

        $this->displayTemplate($analyticsData['keywordId']);
    }

    /**
     * Check should it display analytics or not
     *
     * @param $keywordId
     */
    private function displayTemplate($keywordId)
    {
        /** @var User $user */
        $user = Auth::user();

        $preference = $user->preference($keywordId)->first();

        if ($preference) {
            $this->show = true;
            $this->preference = $preference;
            $this->keywordId = $keywordId;
        } else {
            $this->show = false;
        }
    }

    /**
     * Structure fethced data
     */
    public function getDataProperty()
    {
        if (!$this->show) {
            return [
                'names' => [],
                'values' => []
            ];
        }

        $user = Auth::user();
        $timezone = $user->tz;
        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $this->currentDate, $timezone);

        $dayStart = $carbon->copy()->startOfDay();
        $dayStart->setTimezone('UTC');
        $dayStartString = $dayStart->format('Y-m-d');

        $dayEnd = $dayStart->copy()->addHours(24);
        $dayEndString = $dayEnd->format('Y-m-d');

        $topRankings = DailyAvgRank::where(['keyword_id' => $this->keywordId])->where('date', '>=', $dayStartString)->where('date', '<=', $dayEndString)->whereNotIn('business_entity_id', [$this->preference->business_entity_id])->orderBy('rank_avg', 'ASC')->limit(40)->get()->toArray();

        if (!count($topRankings)) {
            $topRankings = BusinessEntityRanking::selectRaw('business_entity_id, AVG(lsa_rank) as rank_avg')
                ->where('keyword_id', $this->keywordId)
                ->where('created_at', '>=', $dayStartString)
                ->where('created_at', '<=', $dayEndString)
                ->groupBy('business_entity_id')
                ->orderBy('rank_avg', 'ASC')->limit(40)->get()->toArray();
        }

        $topRankingsFiltered = [];

        foreach ($topRankings as $ranking) {
            if (!isset($topRankingsFiltered[$ranking['business_entity_id']])) {
                $topRankingsFiltered[$ranking['business_entity_id']] = $ranking;
            }
        }


        if (!count($topRankings)) {
            return [
                'names' => [],
                'values' => []
            ];
        }

        $preferenceRanking = DailyAvgRank::where(['keyword_id' => $this->keywordId, 'business_entity_id' => $this->preference->business_entity_id])->where('date', '>=', $dayStartString)->where('date', '<=', $dayEndString)->first();

        if (!$preferenceRanking) {
            $preferenceRanking = BusinessEntityRanking::selectRaw('business_entity_id, AVG(lsa_rank) as rank_avg')
                ->where('keyword_id', $this->keywordId)
                ->where('created_at', '>=', $dayStartString)
                ->where('created_at', '<=', $dayEndString)
                ->where('business_entity_id', $this->preference->business_entity_id)
                ->first();

            if (!$preferenceRanking->rank_avg) {
                $preferenceRanking->business_entity_id = $this->preference->business_entity_id;
                $preferenceRanking->rank_avg = 50.00;
            }
        }

        $preferenceRanking = $preferenceRanking->toArray();

        $topRankings = array_slice($topRankingsFiltered, 0, 9);

        $entityIds = [];

        array_push($entityIds, $preferenceRanking['business_entity_id']);

        foreach ($topRankings as $ranking) {
            array_push($entityIds, $ranking['business_entity_id']);
        }

        $businessEntityCollection = BusinessEntity::whereIn('id', $entityIds)->get();

        $rankingCollection = collect([]);

        $keyedCollection = $businessEntityCollection->keyBy('id');

        foreach ($topRankings as $ranking) {
            $rankingCollection->push([
               'name' => $keyedCollection[$ranking['business_entity_id']]->name,
               'rank_avg' => (float) $ranking['rank_avg']
            ]);
        }

        $rankingCollection->push([
            'name' => $keyedCollection[$preferenceRanking['business_entity_id']]->name,
            'rank_avg' => (float) $preferenceRanking['rank_avg']
        ]);

        $sortedCollection = $rankingCollection->sortBy('rank_avg');

        $namesArray = [];
        $valuesArray = [];

        $this->keywordName = Keyword::find($this->keywordId)->original_keyword;

        $counter = 0;

        foreach ($sortedCollection as $sortedItem) {
            array_push($namesArray, $sortedItem['name']);

            if ($counter == 0) {
                array_push($valuesArray, 27.6);
            } elseif ($counter == 1) {
                array_push($valuesArray, 15.8);
            } elseif ($counter == 2) {
                array_push($valuesArray, 11.0);
            } elseif ($counter == 3) {
                array_push($valuesArray, 8.4);
            } elseif ($counter == 4) {
                array_push($valuesArray, 6.3);
            } elseif ($counter == 5) {
                array_push($valuesArray, 4.9);
            } elseif ($counter == 6) {
                array_push($valuesArray, 3.9);
            } elseif ($counter == 7) {
                array_push($valuesArray, 3.3);
            } elseif ($counter == 8) {
                array_push($valuesArray, 2.7);
            } elseif ($counter == 9) {
                array_push($valuesArray, 2.4);
            }

            $counter++;
        }

        $namesArrayShuffled = [$namesArray[0], $namesArray[9], $namesArray[1], $namesArray[8], $namesArray[2], $namesArray[7], $namesArray[3], $namesArray[6], $namesArray[4], $namesArray[5]];
        $valuesArrayShuffled = [$valuesArray[0], $valuesArray[9], $valuesArray[1], $valuesArray[8], $valuesArray[2], $valuesArray[7], $valuesArray[3], $valuesArray[6], $valuesArray[4], $valuesArray[5]];

        $this->names = $namesArrayShuffled;
        $this->values = $valuesArrayShuffled;

        return [
            'names' => $namesArrayShuffled,
            'values' => $valuesArrayShuffled
        ];
    }

    /**
     * Set filter
     *
     * @param array $filterData
     */
    public function filterGraph(array $filterData)
    {
        $this->currentDate = $filterData['currentDate'];
        $this->keywordId = $filterData['keywordId'];
        $this->preference = UserEntityPreference::where('business_entity_id', $filterData['preference'])->first();
        if ($this->preference) {
            $this->show = true;
        }

        $keyword = Keyword::find($filterData['keywordId']);

        $this->mount($keyword, $filterData['currentDate']);
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.keyword-metric-analytics',  [
            'data' => $this->data
        ]);
    }
}
