<?php

namespace App\Http\Livewire;

use App\Jobs\SwitchPremiumTagForUser;
use Livewire\Component;
use App\Models\User;

class UserManagement extends Component
{
    /**
     * @var int
     */
    public $perPage = 10;
    /**
     * Sort direction
     *
     * @var string
     */
    public $sortDirection = 'asc';

    /**
     * Sort field
     *
     * @var null
     */
    public $sortField;

    /**
     * Render user management list
     *
     * @return mixed
     */
    public function render()
    {
        return view('livewire.user-management', ['users' => $this->getUsersProperty()])->layout('layouts.app');;
    }

    /**
     * Get transactions
     *
     * @return mixed
     */
    public function getUsersProperty()
    {
        $query = User::role('Customer');

        return $query->paginate($this->perPage);
    }

    /**
     * Disable or enable user
     *
     * @param $id
     */
    public function toggleActive($id)
    {
        $user = User::find($id);

        $activeFlag = $user->active;

        $user->active = !$activeFlag;
        $user->save();

        $this->redirect(route('admin'));
    }

    /**
     * Disable or enable user
     *
     * @param $id
     */
    public function togglePaid($id)
    {
        $user = User::find($id);

        $paidFlag = $user->paid;

        $user->paid = !$paidFlag;
        $user->save();

        if ($user->paid) {
            $newTag = 'Premium';
        } else {
            $newTag = 'Freemium';
        }

        SwitchPremiumTagForUser::dispatch($user, $newTag)->onQueue('low');

        $this->redirect(route('admin'));
    }

    /**
     * Delete user
     *
     * @param $id
     */
    public function deleteUser($id)
    {
        $user = User::find($id);

        $user->delete();

        $this->redirect(route('admin'));
    }
}
