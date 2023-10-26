<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEntityPreference extends Model
{
    /**
     * @var string
     */
    protected $table = 'user_business_entity_preference';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'keyword_id',
        'business_entity_id',
    ];
}
