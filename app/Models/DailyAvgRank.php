<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAvgRank extends Model
{
    /**
     * @var string
     */
    protected $table = 'lsa_ranking_average';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'keyword_id',
        'business_entity_id',
        'date',
        'rank_avg',
    ];
}
