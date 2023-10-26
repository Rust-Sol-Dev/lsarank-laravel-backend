<?php

namespace App\Traits;

use App\Models\ProxyLastUsed;
use App\Models\ProxyFailed;
use App\Models\ProxyData;

trait ProxyRotation {
    /**
     * Get random proxy
     *
     * @return mixed
     */
    private function getRandomProxy()
    {
        $lastProxy = ProxyLastUsed::latest()->first();

        $failedProxyCollection = ProxyFailed::get();

        $skipProxyArray = [];

        if (count($failedProxyCollection)) {
            $skipProxyArray = $failedProxyCollection->pluck('proxy_id')->toArray();
        }

        $region = null;
        $as = null;
        $subnet = null;

        if ($lastProxy) {
            array_push($skipProxyArray, $lastProxy->proxy_id);

            $proxyData = ProxyData::find($lastProxy->proxy_id);

            $region = $proxyData->region;
            $as = $proxyData->as;
            $subnet = $proxyData->subnet;
        }

        $skipProxyArray = array_unique($skipProxyArray);

        if (!count($skipProxyArray)) {
            $proxyCollection = ProxyData::get();

            $proxyData = $proxyCollection->random();

            return $proxyData;
        }

        $proxyDataQuery = ProxyData::query();

        $proxyDataQuery->whereNotIn('id', $skipProxyArray);

        if ($region) {
            $proxyDataQuery->where('region', '!=', $region);
        }

        if ($as) {
            $proxyDataQuery->where('as', '!=', $as);
        }

        if ($subnet) {
            $proxyDataQuery->where('as', '!=', $subnet);
        }

        $proxyDataCollection = $proxyDataQuery->orderBy('weight', 'ASC')->get();

        $proxyData = $proxyDataCollection->first();

        $weight = $proxyData->weight;
        $weight++;

        $proxyData->weight = $weight;
        $proxyData->save();

        ProxyLastUsed::create([
            'proxy_id' => $proxyData->id
        ]);

        return $proxyData;
    }
}
