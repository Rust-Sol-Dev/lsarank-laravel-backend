<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class KeywordMetricFilter extends Component
{
    /**
     * @var string
     */
    public $selectedDay;

    /**
     * @var string
     */
    public $currentDay;

    /**
     * @var string
     */
    public $currentDate;

    /**
     * @var array
     */
    public $dayMapping = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
    ];

    /**
     * Mount the component
     *
     * @param $currentDay
     * @param $currentDate
     */
    public function mount($currentDay, $currentDate)
    {
        $this->selectedDay = $currentDay;
        $this->currentDay = $currentDay;
        $this->currentDate = $currentDate;
    }

    /**
     * Filter by day
     *
     * @param $day
     * @return bool
     */
    public function filter($day)
    {
        if ($day === $this->selectedDay) {
            return true;
        }

        $currentDayValue = $this->dayMapping[$this->currentDay];
        $dayValue = $this->dayMapping[$day];

        $dayDifference = $currentDayValue - $dayValue;

        $user = Auth::user();
        $timezone = $user->tz;
        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $this->currentDate, $timezone);

        $filteredDate = $carbon->subDays($dayDifference)->format('Y-m-d H:i:s');

        $this->selectedDay = $day;
        $this->emit('filter', ['day' => $day, 'date' => $filteredDate]);
    }

    /**
     * Get button class
     *
     * @param $day
     * @return string
     */
    public function getButtonClass($day)
    {
        if ($day === $this->selectedDay) {
            return 'btn btn-soft-success rounded-pill waves-effect waves-light';
        }

        $dayValue = $this->dayMapping[$day];

        $currentDayValue = $this->dayMapping[$this->currentDay];

        if ($dayValue <= $currentDayValue) {
            return 'btn btn-soft-primary rounded-pill waves-effect waves-light';
        }

        return 'btn btn-soft-danger rounded-pill waves-effect waves-light';
    }

    /**
     * Get disabled attribute
     *
     * @param $day
     * @return string
     */
    public function getDisabledAttribute($day)
    {
        $dayValue = $this->dayMapping[$day];

        $currentDayValue = $this->dayMapping[$this->currentDay];

        if ($dayValue <= $currentDayValue) {
            return '';
        }

        return 'disabled';
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.keyword-metric-filter');
    }
}
