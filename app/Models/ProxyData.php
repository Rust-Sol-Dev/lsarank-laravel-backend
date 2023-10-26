<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProxyData extends Model
{
    /**
     * @var string
     */
    protected $table = 'proxy_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ip_address',
        'port',
        'username',
        'password',
        'description',
        'country',
        'region',
        'city',
        'zipcode',
        'lat',
        'lng',
        'subnet',
        'as',
        'weight',
        'tz',
    ];
}
