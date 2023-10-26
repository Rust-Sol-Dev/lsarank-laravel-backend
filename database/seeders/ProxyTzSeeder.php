<?php

namespace Database\Seeders;

use App\Models\ProxyData;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProxyTzSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_get('allow_url_fopen');

        $proxyDataCollection = ProxyData::all();

        foreach ($proxyDataCollection as $proxyData) {
            $ipInfo = file_get_contents('http://ip-api.com/json/' . $proxyData->ip_address);
            $ipInfo = json_decode($ipInfo);
            $timezone = $ipInfo->timezone;

            $proxyData->tz = $timezone;

            $proxyData->save();
        }
    }
}
