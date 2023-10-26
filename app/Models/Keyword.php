<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    /**
     * @var string
     */
    protected $table = 'lsa_keyword';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'keyword',
        'keyword_slug',
        'original_keyword',
        'location',
        'full_lsa_list_url',
        'user_id',
        'enabled'
    ];

    /**
     * Keyword belongs to User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Keyword has many Business Entities
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function businessEntities()
    {
        return $this->hasMany(BusinessEntity::class, 'keyword_id', 'id');
    }
}
