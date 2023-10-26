<?php

namespace App\Http\Livewire;

use App\Models\Keyword;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KeywordMetrics extends Component
{
    use AuthorizesRequests;

    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * @var string
     */
    public $day;

    /**
     * @var string
     */
    public $date;

    /**
     * @var integer
     */
    public $mapKey;

    /**
     * @var array
     */
    protected $listeners = ['toggleAnalytics' => 'refreshMap'];

    /**
     * Mount the component
     *
     * @param Keyword $keyword
     */
    public function mount(Keyword $keyword)
    {
        $user = Auth::user();
        $timezone = $user->tz;
        $this->keyword = $keyword;
        $carbon = Carbon::now($timezone);
        $dayInWeek = strtolower($carbon->englishDayOfWeek);
        $this->day = $dayInWeek;
        $this->date = $carbon->format('Y-m-d H:i:s');
        $this->mapKey = rand(11111, 99999999);
    }

    /**
     *
     */
    public function refreshMap()
    {
        $this->mapKey = rand(11111, 99999999);
    }

    /**
     * Render the component
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function render()
    {
        $this->authorize('show', $this->keyword);

        return view('livewire.keyword-metrics')->layout('layouts.app');;
    }
}
