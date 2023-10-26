<?php

namespace App\Services;

use App\Exceptions\MapScreenShotException;
use App\Models\BusinessEntity;
use App\Models\BusinessEntityHeatMap;
use App\Models\Keyword;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Str;

class ReportService
{
    /**
     * @var AnalyticsService
     */
    public $analytics;

    /**
     * @var PuppeteerService
     */
    public $puppeteer;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * @var BusinessEntity
     */
    public $businessEntity;

    /**
     * ReportService constructor.
     * @param User $user
     * @param Keyword $keyword
     * @param BusinessEntity $businessEntity
     */
    public function __construct(User $user, Keyword $keyword, BusinessEntity $businessEntity)
    {
        $this->user = $user;
        $this->keyword = $keyword;
        $this->businessEntity = $businessEntity;
        $this->analytics = new AnalyticsService($keyword, $businessEntity, $user);
        $this->puppeteer = new PuppeteerService();
    }

    /**
     * Generate PDF report
     *
     * @param string $output
     * @param bool $debug
     * @return \Illuminate\Http\Response|string
     * @throws MapMissingException
     * @throws \App\Exceptions\HeathMapBatchMissingExcpetion
     * @throws \App\Exceptions\HeathMapNotPresentException
     * @throws \App\Exceptions\MapScreenShotException
     * @throws \App\Exceptions\ZipCodeRankingsNotReadyException
     */
    public function generatePdfReport($output = 'download', $debug = false)
    {
        $pdfData = $this->collectData();

        if ($debug) {
            $view = view('pdf.report', $pdfData)->render();
            return $view;
        }

        $reportsFolder = storage_path('app/reports');

        $pdf = SnappyPdf::loadView('pdf.report', $pdfData)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('margin-top', 0)
            ->setOption('exclude-from-outline', true)
            ->setOption('no-outline', true)
            ->setOption('outline-depth', 0)
            ->setOption('disable-smart-shrinking', true)
            ->setOption('page-height', 314.3)
            ->setOption('page-width', 242.9875);
            //->setTemporaryFolder($reportsFolder);

        $entityName = $this->businessEntity->name;
        $slug = Str::slug($entityName);
        $dateTimeString = Carbon::now($this->user->tz)->format('Y-m-d H:i:s');
        $fileName = "$slug-$dateTimeString.pdf";

        $fullPath = "$reportsFolder/$fileName";
        $pdf->save($fullPath);

        return $fullPath;

//        if ($output == 'download') {
//            return $pdf->download("$slug-$dateTimeString-report.pdf");
//        } else {
//            return $pdf->inline("$slug-$dateTimeString-report.pdf");
//        }
    }

    /**
     * Get map binary
     *
     * @param BusinessEntityHeatMap $heatMap
     * @return string|null
     * @throws MapMissingException
     * @throws \App\Exceptions\MapScreenShotException
     */
    private function getMapBinary(BusinessEntityHeatMap $heatMap)
    {
        try {
            $mapBinary = $this->puppeteer->screenShotMap($heatMap->id);
        } catch (\Exception $exception) {
            activity()->event('MAP_SCREENSHOT_ERROR')->log($exception->getMessage());
            throw new MapScreenShotException();
        }

        return $mapBinary;
    }

    /**
     * Collect data for the report
     *
     * @return array
     * @throws MapMissingException
     * @throws \App\Exceptions\HeathMapBatchMissingExcpetion
     * @throws \App\Exceptions\HeathMapNotPresentException
     * @throws \App\Exceptions\MapScreenShotException
     * @throws \App\Exceptions\ZipCodeRankingsNotReadyException
     */
    private function collectData()
    {
        list($zipcodeRadiusRankingTotal, $zipcodeRadiusRankingHigh, $zipcodeRadiusRankingMedium, $zipcodeRadiusRankingLow, $heatMap) = $this->analytics->getZipcodeRadiusRanking();
        $top5WeeklyReviewCountArray = $this->analytics->getTopCompetitorsWeeklyReviewCount();
        $top5WeeklyRankPercentArray = $this->analytics->getTopCompetitorsWeeklyRankPercent();
        list($dailyAvgRank, $dailyAvgRankPercentage) = $this->analytics->getDailyAvgRank();
        list($weeklyAvgRank, $weeklyAvgRankPercentage) = $this->analytics->getWeeklyAvgRank();
        list($dailyReviewCount, $dailyReviewCountPercentage) = $this->analytics->getDailyReviews();
        list($weeklyReviewCount, $weeklyReviewCountPercentage) = $this->analytics->getWeeklyReviews();

        $mapBinary = $this->getMapBinary($heatMap);

        $keywordDisplay = ucfirst($this->keyword->original_keyword) . " near " . str_replace('+', ' ', $this->keyword->location);

        $data = [
            'generated' => Carbon::now()->format('Y-m-d H:i:s'),
            'entityName' => $this->businessEntity->name,
            'entityPhone' => $this->businessEntity->phone,
            'keywordDisplay' => $keywordDisplay,
            'top5WeeklyReview' => $top5WeeklyReviewCountArray,
            'top5WeeklyRank' => $top5WeeklyRankPercentArray,
            'dailyAvgRank' => $dailyAvgRank,
            'dailyAvgRankPercentage' => $dailyAvgRankPercentage,
            'weeklyAvgRank' => $weeklyAvgRank,
            'weeklyAvgRankPercentage' => $weeklyAvgRankPercentage,
            'dailyReviewCount' => $dailyReviewCount,
            'dailyReviewCountPercentage' => $dailyReviewCountPercentage,
            'weeklyReviewCount' => $weeklyReviewCount,
            'weeklyReviewCountPercentage' => $weeklyReviewCountPercentage,
            'zipcodeRadiusRankingHigh' => $zipcodeRadiusRankingHigh,
            'zipcodeRadiusRankingHighPercent' => round($zipcodeRadiusRankingHigh / $zipcodeRadiusRankingTotal, 2) * 100,
            'zipcodeRadiusRankingMedium' => $zipcodeRadiusRankingMedium,
            'zipcodeRadiusRankingMediumPercent' => round($zipcodeRadiusRankingMedium / $zipcodeRadiusRankingTotal, 2) * 100,
            'zipcodeRadiusRankingLow' => $zipcodeRadiusRankingLow,
            'zipcodeRadiusRankingLowPercent' => round($zipcodeRadiusRankingLow / $zipcodeRadiusRankingTotal, 2) * 100,
            'mapBinary' => $mapBinary
        ];

        return $data;
    }
}
