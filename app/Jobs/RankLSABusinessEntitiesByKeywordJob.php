<?php

namespace App\Jobs;

use App\Models\BusinessEntityReviewCount;
use App\Models\BusinessEntity;
use App\Models\BusinessEntityRanking;
use App\Models\Keyword;
use App\Models\ProxyData;
use App\Models\ProxyFailed;
use App\Models\ProxyLastUsed;
use App\Services\LsaCrawler;
use App\Traits\ProxyRotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RankLSABusinessEntitiesByKeywordJob implements ShouldQueue
{
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
     * @var LsaCrawler
     */
    public $crawler;

    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * @var ProxyData
     */
    public $proxyData;

    /**
     * @var BusinessEntity
     */
    public $businessEntity;

    /**
     * @var BusinessEntityRanking
     */
    public $ranking;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ProxyRotation;

    /**
     * RankLSABusinessEntitiesByKeywordJob constructor.
     * @param Keyword $keyword
     * @param ProxyData $proxyData
     */
    public function __construct(Keyword $keyword, ProxyData $proxyData)
    {
        $this->keyword = $keyword;
        $this->proxyData = $proxyData;
    }

    /**
     * Execute the job.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');

        $this->crawler = App::make(LsaCrawler::class);
        $this->businessEntity = new BusinessEntity();
        $this->ranking = new BusinessEntityRanking();

        $attempts = $this->attempts();

        if ($attempts > 1) {
            $proxyData = $this->getRandomProxy();
        } else {
            $proxyData = $this->proxyData;
        }

        $userId = $this->keyword->user_id;
        $keywordId = $this->keyword->id;
        $keyword = $this->keyword->keyword;
        $location = $this->keyword->location;

        $lsaListUrl = $this->keyword->full_lsa_list_url;

        if ($lsaListUrl) {
            $lsaListUrl = $this->modifyProListParams($lsaListUrl);
            $url = "https://www.google.com$lsaListUrl";
        } else {
            $keyword = "$keyword+near+$location";
            $url = "https://www.google.com/localservices/prolist?src=1&q=$keyword";
        }


        try {
            $lsaListHtml = $this->crawler->scrapeUrl($url, $proxyData);
        } catch (\Exception $exception) {
            activity()->event('GETTING_LIST_ERROR_QUEUE')->log($exception->getMessage() . "Keyword Id " . $this->keyword->id);
            throw $exception;
        }

        try {
            $businessEntitiesCollection = $this->crawler->getBusinessEntities($lsaListHtml);
        } catch (\Exception $exception) {
            activity()->event('PARSING_ENTITIES_LIST_ERROR_QUEUE')->log($exception->getMessage() . "Keyword Id " . $this->keyword->id);
            Log::channel('list_parsing_error')->info($lsaListHtml);
            Log::channel('list_parsing_error')->info("=========================||||||========================================");
            throw $exception;
        }

        DB::beginTransaction();

        try {
            $result = $this->saveRankingInfo($businessEntitiesCollection, $userId, $keywordId, $keyword);
        } catch (\Exception $exception) {
            DB::rollBack();
            activity()->event('SAVING_RANKING_DATA_ERROR_QUEUE')->log($exception->getMessage() . "Keyword Id " . $this->keyword->id);
            throw $exception;
        }

        DB::commit();

        return true;
    }

    /**
     * Modify prolist params remove identity
     *
     * @param string $params
     * @return string
     */
    public function modifyProListParams(string $params)
    {
        $explodedUrl = explode('/localservices/prolist?', $params);

        $paramArray = [];

        parse_str($explodedUrl[1], $paramArray);

        $paramArray['g2lbs'] = '';

        $paramsString = http_build_query($paramArray);

        $url = "/localservices/prolist?" . $paramsString;

        return $url;
    }

    /**
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception)
    {
        activity()->event('RANKING_JOB_FAILED')->log($exception->getMessage());
    }

    /**
     * Save ranking info
     *
     * @param Collection $businessEntitiesCollection
     * @param int $userId
     * @param int $keywordId
     * @param string $keyword
     */
    private function saveRankingInfo(Collection $businessEntitiesCollection, int $userId, int $keywordId, string $keyword)
    {
        foreach ($businessEntitiesCollection as $key => $businessEntityObject) {
            $carbon = Carbon::now('UTC');
            $dayInWeek = strtolower($carbon->englishDayOfWeek);

            $lsaRank = $key + 1;

            $businessEntity = $this->businessEntity->firstOrCreate([
                'user_id' => $userId,
                'keyword_id' => $keywordId,
                'customer_id' => $businessEntityObject->data_customer_id,
                'slug' => $businessEntityObject->slug,
            ], [
                'profile_url_path' => $businessEntityObject->data_profile_url_path,
                'name' => $businessEntityObject->name,
                'occupation' => $businessEntityObject->occupation,
                'phone' => $businessEntityObject->phone,
                'keyword' => $keyword,
                'lsa_ranking' => $lsaRank,
            ]);

            BusinessEntityReviewCount::updateOrCreate([
                'user_id' => $userId,
                'keyword_id' => $keywordId,
                'business_entity_id' => $businessEntity->id,
                'date' => $carbon->format('Y-m-d')
            ], [
                'timestamp' => $carbon->format('Y-m-d H:i:s'),
                'review_count' => $businessEntityObject->review_count,
            ]);

            $payload = [
                'user_id' => $userId,
                'keyword_id' => $keywordId,
                'business_entity_id' => $businessEntity->id,
                'lsa_rank' => $lsaRank,
                'day' => $dayInWeek,
                'created_at' => $carbon->format('Y-m-d H:i'),
                'updated_at' => $carbon->format('Y-m-d H:i'),
            ];

            DB::table('business_entities_ranking')->insert($payload);
        }

    }
}
