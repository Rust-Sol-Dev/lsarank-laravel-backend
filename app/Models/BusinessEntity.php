<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEntity extends Model
{
    /**
     * @var string
     */
    protected $table = 'lsa_business_entities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile_url_path',
        'customer_id',
        'name',
        'slug',
        'occupation',
        'phone',
        'keyword',
        'lsa_ranking',
        'keyword_id',
        'user_id',
    ];

    /**
     * Entity belongs to keyword search
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lsaKeyword()
    {
        return $this->belongsTo(Keyword::class, 'keyword_id', 'id');
    }
}
