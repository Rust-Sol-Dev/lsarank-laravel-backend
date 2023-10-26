<?php

use Carbon\Carbon;
use Carbon\CarbonTimeZone;

trait SetTimeZone
{
    public $tz = 'UTC';
    public function getTz(){
        $this->tz = auth()->user()->tz;
    }

    public function getCreatedAtAttribute($value){
        try {
            return (new Carbon($value))->setTimezone(new CarbonTimeZone($this->getTz()));
        } catch (\Exception $e) {
            return 'Invalid DateTime Exception: '.$e->getMessage();
        }
    }
    public function getUpdatedAtAttribute($value){
        try {
            return (new Carbon($value))->setTimezone(new CarbonTimeZone($this->getTz()));
        } catch (\Exception $e) {
            return 'Invalid DateTime Exception: '.$e->getMessage();
        }
    }
}
