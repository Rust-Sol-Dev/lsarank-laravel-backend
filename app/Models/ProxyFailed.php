<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProxyFailed extends Model
{
    /**
     * @var string
     */
    protected $table = 'failed_proxy';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'proxy_id'
    ];
}
