<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ViewHelper
{
    /**
     * Display time in correct timezone
     *
     * @param $timeStamp
     * @return string
     */
    public static function displayTime($timeStamp)
    {
        $user = Auth::user();
        $carbon = Carbon::parse($timeStamp);
        $carbon->setTimezone($user->tz);

        return $carbon->format('H:i');
    }
}
