<?php

namespace App\Policies;

use App\Models\Keyword;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeywordPolicy
{
    use HandlesAuthorization;

    /**
     * Check if user can see keyword metrics
     *
     * @param User $user
     * @param Keyword $keyword
     * @return bool
     */
    public function show(User $user, Keyword $keyword)
    {
        return $user->id === $keyword->user_id;
    }
}
