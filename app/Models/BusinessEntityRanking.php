<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEntityRanking extends Model
{
    /**
     * @var string
     */
    protected $table = 'business_entities_ranking';

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'keyword_id',
        'business_entity_id',
        'lsa_rank',
        'day',
    ];

    /**
     * Business entity ranking belongs to business entity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessEntity()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_entity_id', 'id');
    }
}
