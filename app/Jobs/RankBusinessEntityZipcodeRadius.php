<?php

namespace App\Jobs;

use App\Models\BusinessEntity;
use App\Models\BusinessEntityZipcodeRadiusRanking;
use App\Models\Keyword;
use App\Models\ProxyData;
use App\Traits\ProxyRotation;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use App\Services\LsaCrawler;
use Illuminate\Support\Facades\Log;

class RankBusinessEntityZipcodeRadius implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable, ProxyRotation;

    /**
     * @var int
     */
    public $timeout = 999999;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * @var array
     */
    public $data;

    /**
     * @var array
     */
    public $proxyData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data, ProxyData $proxyData)
    {
        $this->data = $data;
        $this->proxyData = $proxyData;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $batch = $this->batch();

        $keywordId = $this->data['keyword_id'];
        $userId = $this->data['user_id'];
        $businessEntityId = $this->data['business_entity_id'];
        $heathMapId = $this->data['heatmap_id'];
        $keyword = Keyword::find($keywordId);
        $businessEntity = BusinessEntity::find($businessEntityId);
        $zipcode = $this->data['zip_code'];

        $attempts = $this->attempts();

        if ($attempts > 1) {
            $proxyData = $this->getRandomProxy();
        } else {
            $proxyData = $this->proxyData;
        }

        $zipcodeKeyword = "$keyword->keyword+near+$zipcode,USA";
        $crawler = App::make(LsaCrawler::class);

        $url = "https://www.google.com/localservices/prolist?src=1&q=$zipcodeKeyword";

        try {
            $lsaListHtml = $crawler->scrapeUrl($url, $proxyData);
        } catch (\Exception $exception) {
            activity()->event('GETTING_LIST_RADIUS_ERROR_QUEUE')->log("Keyword Id: $keywordId. Zipcode: $zipcode. Heathmap id: $heathMapId." . $exception->getMessage());
            throw $exception;
        }

        try {
            $businessEntitiesCollection = $crawler->getBusinessEntities($lsaListHtml);
        } catch (\Exception $exception) {
            activity()->event('PARSING_ENTITIES_LIST_RADIUS_ERROR_QUEUE')->log("Keyword Id: $keywordId. Zipcode: $zipcode. Heathmap id: $heathMapId." . $exception->getMessage());
            Log::channel('list_parsing_error')->info($lsaListHtml);
            Log::channel('list_parsing_error')->info("=========================||||||========================================");
            throw $exception;
        }

        $maxRank = count($businessEntitiesCollection);

        if ($maxRank == 0) {
            return true;
        }

        $lsaRank = $maxRank;

        $lsaCustomerId = $businessEntity->customer_id;

        foreach ($businessEntitiesCollection as $key => $businessEntityData) {
            $lsaRank = $key + 1;

            if ($lsaCustomerId == $businessEntityData->data_customer_id) {
                break;
            }
        }

        BusinessEntityZipcodeRadiusRanking::create([
            'heat_map_id' => $heathMapId,
            'business_entity_id' => $businessEntityId,
            'user_id' => $userId,
            'zipcode' => $zipcode,
            'lsa_rank' => $lsaRank,
            'max_rank' => $maxRank,
            'keyword' => $zipcodeKeyword,
            'batch_id' => $batch->id,
        ]);
    }

    /**
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception)
    {
        activity()->event('ZIPCODE_RADIUS_JOB_FAILED')->log($exception->getMessage());
    }
}
