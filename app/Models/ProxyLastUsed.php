<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProxyLastUsed extends Model
{
    /**
     * @var string
     */
    protected $table = 'last_used_proxy';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'proxy_id'
    ];
}
