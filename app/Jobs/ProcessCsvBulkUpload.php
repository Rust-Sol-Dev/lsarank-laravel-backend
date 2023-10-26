<?php

namespace App\Jobs;

use App\Models\KeywordBulkUpload;
use App\Services\LsaCrawler;
use App\Traits\ProxyRotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessCsvBulkUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ProxyRotation;

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var array
     */
    public $data;

    /**
     * ProcessCsvBulkUpload constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function handle()
    {
        $path = $this->data['filepath'];
        $id = $this->data['id'];
        $userId = $this->data['user_id'];

        Auth::loginUsingId($userId);

        $keywordBulkUpload = KeywordBulkUpload::find($id);

        $pathToFile = storage_path("app/$path");

        try {
            $keywordArray = [];
            // $rows is an instance of Illuminate\Support\LazyCollection
            $rows = SimpleExcelReader::create($pathToFile)->getRows();

            foreach ($rows as $key => $row) {
                array_push($keywordArray, [
                    'keyword' => $row['keyword'],
                    'location' => $row['location'],
                ]);
            }
        } catch (\Exception $exception) {
            activity()->event('CSV_IMPORT_PARSING_ERROR')->log("Error:" . $exception->getMessage());
            throw $exception;
        }

        $lsaCrawler = App::make(LsaCrawler::class);

        $failedCount = 0;
        $successCount = 0;
        $totalCount = count($keywordArray);

        foreach ($keywordArray as $keyword) {
            try {
                $result = $this->handleKeyword($lsaCrawler, $keyword);
                $successCount++;
            } catch (\Exception $exception) {
                $failedCount++;
                $keywordJson = json_encode($keyword);
                activity()->event('CSV_BULK_IMPORT_LIST_INITIAL_GLOBAL')->log("Keyword $keywordJson." . $exception->getMessage());
                continue;
            }

        }

        $keywordBulkUpload->failed_count = $failedCount;
        $keywordBulkUpload->success_count = $successCount;
        $keywordBulkUpload->total_count = $totalCount;
        $keywordBulkUpload->save();

        return true;
    }

    /**
     * Handle keyword
     *
     * @param LsaCrawler $lsaCrawler
     * @param array $keyword
     * @return bool
     * @throws \Exception
     */
    private function handleKeyword(LsaCrawler $lsaCrawler, array $keyword)
    {
        $originalKeyword = $keyword['keyword'];
        $location = $keyword['location'];

        try {
            $keyword = $lsaCrawler->checkKeyword($originalKeyword, $location);
        } catch (\Exception $exception) {
            $keywordJson = json_encode($keyword);
            activity()->event('CSV_BULK_IMPORT_LIST_INITIAL_KEYWORD_CHECK')->log("Keyword $keywordJson." . $exception->getMessage());
            throw $exception;
        }

        $url = "https://www.google.com/localservices/prolist?src=1&q=$keyword";
        $proxyData = $this->getRandomProxy();

        try {
            $lsaListHtml = $lsaCrawler->scrapeUrl($url, $proxyData);
        } catch (\Exception $exception) {
            $keywordJson = json_encode($keyword);
            activity()->event('CSV_BULK_IMPORT_LIST_INITIAL_SCRAPE')->log("Keyword $keywordJson." . $exception->getMessage());
            throw  $exception;
        }

        try {
            $businessEntitiesCollection = $lsaCrawler->getBusinessEntities($lsaListHtml);
        } catch (\Exception $exception) {
            activity()->event('CSV_BULK_IMPORT_PARSE_LIST')->log($exception->getMessage());
            Log::channel('list_parsing_error')->info($lsaListHtml);
            Log::channel('list_parsing_error')->info("=========================||||||========================================");
            throw $exception;
        }

        if (!count($businessEntitiesCollection)) {
            $keywordJson = json_encode($keyword);
            activity()->event('CSV_BULK_IMPORT_LIST_INITIAL_COUNT')->log("Keyword $keywordJson is invalid. Count is 0");
            throw new \Exception("Count is 0");
        }

        DB::beginTransaction();

        try {
            $keywordModel = $lsaCrawler->storeKeyword($originalKeyword, str_replace(' ', '+', $originalKeyword), null, $keyword = str_replace(' ', '+', $location));
            //Part after this line will be refactored to dispatch queue job later @todo

            $lsaCrawler->storeBusinessEntitiesCollection($keywordModel, $businessEntitiesCollection);
        } catch (\Exception $exception) {
            DB::rollBack();
            activity()->event('STORING_DATA_CSV_BULK_IMPORT_ERROR_INITIAL')->log($exception->getMessage());
            //Add logging
            throw $exception;
        }

        DB::commit();
        return true;
    }
}
