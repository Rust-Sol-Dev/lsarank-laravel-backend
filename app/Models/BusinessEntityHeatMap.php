<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use function Symfony\Component\Finder\in;

class BusinessEntityHeatMap extends Model
{
    /**
     * @var string
     */
    protected $table = 'business_entity_heat_map';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'keyword_id',
        'business_entity_id',
        'location',
        'latitude',
        'longitude',
        'place_id',
        'zip_code',
        'zip_radius',
        'last_batch_id',
    ];

    /**
     * Heat map has many radius ranking
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function radiusRanking()
    {
        return $this->hasMany(BusinessEntityZipcodeRadiusRanking::class, 'heat_map_id', 'id');
    }

    /**
     * Business entity heat map belongs to business entity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessEntity()
    {
        return $this->belongsTo(BusinessEntity::class, 'business_entity_id', 'id');
    }

    /**
     * Business entity heat map belongs to business entity
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function keyword()
    {
        return $this->belongsTo(Keyword::class, 'keyword_id', 'id');
    }

    /**
     * Business entity heat map belongs to user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
