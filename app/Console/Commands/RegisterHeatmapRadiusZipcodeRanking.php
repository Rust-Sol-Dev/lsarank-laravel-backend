<?php

namespace App\Console\Commands;

use App\Jobs\RankBusinessEntityZipcodeRadius;
use App\Models\BusinessEntityHeatMap;
use App\Traits\ProxyRotation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class RegisterHeatmapRadiusZipcodeRanking extends Command
{
    use ProxyRotation;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register:heatmap:radius:ranking:job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register job batches for continuation of zipcode radius crapping';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Throwable
     */
    public function handle()
    {
        $heatMapCollection = BusinessEntityHeatMap::get();

        foreach ($heatMapCollection as $heatMapData) {
            $zipCodeCoordinates = json_decode($heatMapData->zip_radius, true);

            $dataArray = [
                'user_id' => $heatMapData->user_id,
                'business_entity_id' => $heatMapData->business_entity_id,
                'keyword_id' => $heatMapData->keyword_id,
                'heatmap_id' => $heatMapData->id,
            ];

            $jobsArray = [];

            foreach ($zipCodeCoordinates as $zipCodeCoordinate) {
                try {
                    $dataArray['zip_code'] = $zipCodeCoordinate['postal_code'];

                    $proxyData = $this->getRandomProxy();

                    $jobInstance = new RankBusinessEntityZipcodeRadius($dataArray, $proxyData);

                    array_push($jobsArray, $jobInstance);
                } catch (\Exception $exception) {
                    activity()->event('DISPATCH_ZIP_ERROR')->log($exception->getMessage());
                    continue;
                }
            }

            $batch = Bus::batch($jobsArray)->onQueue('radius')->allowFailures()->dispatch();

            $heatMapData->last_batch_id = $batch->id;
            $heatMapData->save();
        }

        return Command::SUCCESS;
    }
}
