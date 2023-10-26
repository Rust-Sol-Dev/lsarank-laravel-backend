<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEntityReviewCount extends Model
{
    /**
     * @var string
     */
    protected $table = 'business_entity_review_count';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'keyword_id',
        'business_entity_id',
        'review_count',
        'date',
        'timestamp',
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
