<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Repositories\KeywordMetricRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Keyword;
use Livewire\WithPagination;

class KeywordMetricTable extends Component
{
    use WithPagination;

    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * @var array
     */
    public $businessEntityMapping;

    /**
     * @var Collection
     */
    protected $businessEntityRankings;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $selectedDay;

    /**
     * @var string
     */
    public $selectedDate;

    /**
     * @var null
     */
    public $userPreference = null;

    /**
     * @var array
     */
    protected $listeners = ['filter'];

    /**
     * @param Keyword $keyword
     * @param string $currentDay
     * @param string $currentDate
     */
    public function mount(Keyword $keyword, string $currentDay, string $currentDate)
    {
        $this->user = Auth::user();
        $this->keyword = $keyword;
        $this->selectedDay = $currentDay;
        $this->selectedDate = $currentDate;
    }

    /**
     * Structure fethced data
     */
    public function getDataProperty()
    {
        $request = Request::capture();

        $preference = $this->user->preference($this->keyword->id)->first();

        if ($preference) {
            $this->userPreference = $preference->business_entity_id;
        } else {
            $this->userPreference = null;
        }

        /** @var KeywordMetricRepository $repository */
        $repository = App::make(KeywordMetricRepository::class);

        list($businessEntityCollection, $businessEntityMapping) = $repository->getBusinessEntitiesMapping($this->user->id, $this->keyword->id, $this->selectedDate, $this->userPreference);

        //This property determines table heading
        $this->businessEntityMapping = $businessEntityMapping;

        $page = $this->page;

        $data = $repository->getBusinessEntitiesRanking($businessEntityCollection, $this->selectedDay, $this->selectedDate, $request, $page);

        return $data;
    }

    /**
     * @deprecated
     * @return array
     */
//    public function getOldDataProperty()
//    {
//        $businessEntity = BusinessEntity::query();
//
//        $bussinessEntityCollection = $businessEntity->where(['user_id' => $this->user->id, 'keyword_id' => $this->keyword->id])->get();
//
//        $this->businessEntityMapping = $bussinessEntityCollection->pluck('name', 'id');
//
//        $ranking = BusinessEntityRanking::query();
//                //DB::connection()->enableQueryLog();
//        $this->businessEntityRankings = $ranking->where(['user_id' => $this->user->id, 'keyword_id' => $this->keyword->id,  'day' => $this->selectedDay])->whereDate('created_at', '=', $this->selectedDate)->orderBy("created_at", 'DESC')->get();
//        $rankingsGrouped = $this->businessEntityRankings->groupBy('created_at');
//
//        $businessEntityKeyArray = array_keys($this->businessEntityMapping->toArray());
//
//        $oldData = [];
//
//        foreach ($rankingsGrouped as $dateTimeString => $dayCollection) {
//            $row = array_fill(0, (count($this->businessEntityMapping)), null);
//            foreach ($dayCollection as $ranking) {
//                $businessEntityId = $ranking->business_entity_id;
//
//                $index = array_search($businessEntityId, $businessEntityKeyArray);
//
//                if ($index === false) {
//                    continue;
//                }
//
//                $row[$index] = $ranking->lsa_rank;
//            }
//
//            $timeString = Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeString)->format('H:i:s');
//
//            array_unshift($row, $timeString);
//
//            array_push($oldData, $row);
//        }
//
//        //$queries = DB::getQueryLog();
//
//        //$last_query = end($queries);
//
//        return $oldData;
//    }

    /**
     * Set filter
     *
     * @param array $filterData
     */
    public function filter(array $filterData)
    {
        $this->selectedDay = $filterData['day'];
        $this->selectedDate = $filterData['date'];
        $this->setPage(1, 'page');
        $this->emit('filterGraph', ['keywordId' => $this->keyword->id, 'currentDate' => $filterData['date'], 'preference' => $this->userPreference]);
        $this->emit('filterAnalyticsDashboard', ['keywordId' => $this->keyword->id, 'currentDate' => $filterData['date'], 'preference' => $this->userPreference]);
    }

    /**
     * Toggle user preference
     *
     * @param $key
     * @param null $reset
     */
    public function toggleUserPreference($key, $reset = null)
    {
        $repository = App::make(KeywordMetricRepository::class);

        $repository->toggleUserPreference($this->user->id, $this->keyword->id, $key, $reset);

        $this->emit('toggleAnalytics', ['keywordId' => $this->keyword->id, 'currentDate' => $this->selectedDate]);
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.keyword-metric-table',  [
            'data' => $this->data
        ]);
    }
}
