<?php

namespace App\Jobs;

use App\Mail\WeeklyReport;
use App\Models\BusinessEntity;
use App\Models\Keyword;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class GenerateAndSendWeeklyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 999999;

    /**
     * @var int
     */
    public $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * @var array
     */
    public $heatMapData;

    /**
     * GenerateAndSendWeeklyReport constructor.
     * @param array $heatMapAttributes
     */
    public function __construct(array $heatMapAttributes)
    {
        $this->heatMapData = $heatMapAttributes;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function handle()
    {
        $userId = $this->heatMapData['user_id'];
        $keywordId = $this->heatMapData['keyword_id'];
        $businessEntityId = $this->heatMapData['business_entity_id'];

        $user = User::find($userId);
        $keyword = Keyword::find($keywordId);
        $businessEntity = BusinessEntity::find($businessEntityId);

        $reportService = new ReportService($user, $keyword, $businessEntity);

        try {
            $pdfReportPath = $reportService->generatePdfReport('download');
        } catch (\Exception $exception) {
            activity()->event('PDF_REPORT_WEEKLY_JOB_REPORT_GENERATION_FAIL')->log("Fail for user " . $userId . " keyword " . $keywordId . " business entity " . $businessEntityId . ". Exception: " . $exception->getMessage());
            throw $exception;
        }

        Mail::to($user)->send(new WeeklyReport($pdfReportPath));

        return true;
    }

    /**
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception)
    {
        activity()->event('PDF_GENERATION_JOB_FAILURE')->log($exception->getMessage());
    }
}
