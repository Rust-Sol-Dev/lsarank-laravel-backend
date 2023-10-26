<?php

namespace App\Jobs;

use App\Models\BusinessEntityHeatMap;
use App\Traits\ProxyRotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class DispatchZipCodeJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ProxyRotation;

    /**
     * @var integer
     */
    public $id;

    /**
     * Create a new job instance.
     *
     * DispatchZipCodeJobs constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return bool
     * @throws \Throwable
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '100000000');

        $heatMapData = BusinessEntityHeatMap::find($this->id);

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

        return true;
    }
}
