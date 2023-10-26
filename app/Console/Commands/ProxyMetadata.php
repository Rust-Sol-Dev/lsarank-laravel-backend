<?php

namespace App\Console\Commands;

use App\Models\ProxyData;
use Illuminate\Console\Command;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;

class ProxyMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:proxy:meta:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save Proxy Meta Data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var LengthAwarePaginator $proxyDataColllection */
        $proxyDataArray = ProxyData::all()->toArray();

        $counter = 0;

        $payloadArray = [];

        foreach ($proxyDataArray as $proxyData) {
            array_push($payloadArray, $proxyData['ip_address']);
            $counter++;

            if ($counter % 100 == 0) {
                /** @var  $response */
                $response = Http::post("http://ip-api.com/batch", $payloadArray);

                $body = $response->body();

                $responseArray = json_decode($body,true);

                foreach ($responseArray as $proxyInfo) {
                    $ip = $proxyInfo['query'];

                    $proxyData = ProxyData::where('ip_address', $ip)->first();

                    if (!$proxyData) {
                        continue;
                    }

                    $explodedIp = explode('.', $ip);

                    $proxyData->country = $proxyInfo['country'];
                    $proxyData->region = $proxyInfo['region'];
                    $proxyData->city = $proxyInfo['city'];
                    $proxyData->zipcode = $proxyInfo['zip'];
                    $proxyData->lat = $proxyInfo['lat'];
                    $proxyData->lng = $proxyInfo['lon'];
                    $proxyData->tz = $proxyInfo['timezone'];
                    $proxyData->as = $proxyInfo['as'];
                    $proxyData->subnet = $explodedIp[2];
                    $proxyData->save();

                }

                $payloadArray = [];
            }
        }


        return Command::SUCCESS;
    }
}
