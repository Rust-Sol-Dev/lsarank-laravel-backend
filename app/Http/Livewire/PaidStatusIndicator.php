<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PaidStatusIndicator extends Component
{
    /**
     * @var boolean
     */
    public $paid = 0;

    /**
     * @var integer
     */
    public $rand;

    /**
     * Mount the component
     */
    public function mount()
    {
        /** @var User $user */
        $user = Auth::user();

        $this->rand = rand(1111,999999);

        $this->paid = (integer) $user->isPaid();
    }

    /**
     * Update the component
     */
    public function update()
    {
        /** @var User $user */
        $user = Auth::user();

        $this->rand = rand(1111,999999);

        $this->paid = $user->isPaid();
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.paid-status-indicator');
    }
}
