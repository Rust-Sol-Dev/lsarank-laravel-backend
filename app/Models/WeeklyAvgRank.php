<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyAvgRank extends Model
{
    /**
     * @var string
     */
    protected $table = 'lsa_ranking_average_weekly';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'keyword_id',
        'business_entity_id',
        'week_start',
        'week_end',
        'current_date',
        'rank_avg',
    ];

    /**
     * Count belongs to business entity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessEntity()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_entity_id', 'id');
    }
}
