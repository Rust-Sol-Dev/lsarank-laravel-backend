<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEntityZipcodeRadiusRanking extends Model
{
    /**
     * @var string
     */
    protected $table = 'business_entities_zipcode_rankings';

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
        'heat_map_id',
        'business_entity_id',
        'user_id',
        'zipcode',
        'lsa_rank',
        'max_rank',
        'keyword',
        'batch_id',
    ];
}
