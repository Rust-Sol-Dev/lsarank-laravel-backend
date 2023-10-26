<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimezoneUpdater extends Component
{
    /**
     * @var array
     */
    protected $listeners = ['timeZoneDetected'];

    /**
     * Timezone detected
     *
     * @param $timeZone
     * @return bool
     */
    public function timeZoneDetected($timeZone)
    {
        /** @var User $user */
        $user = Auth::user();

        $user->updateTimezone($timeZone);

        return true;
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.timezone-updater');
    }
}
