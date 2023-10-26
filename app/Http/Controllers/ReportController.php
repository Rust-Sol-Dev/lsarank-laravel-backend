<?php

namespace App\Http\Controllers;

use App\Exports\LsaRankExport;
use App\Http\Requests\ReportDownloadRequest;
use App\Models\BusinessEntity;
use App\Models\BusinessEntityHeatMap;
use App\Models\Keyword;
use App\Models\User;
use App\Repositories\KeywordMetricRepository;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BusinessEntityZipcodeRadiusRanking;
use App\Jobs\GenerateAndSendWeeklyReport;

class ReportController extends Controller
{
    /**
     * List report periods
     *
     * @param KeywordMetricRepository $repository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function list(KeywordMetricRepository $repository)
    {
        $userId = Auth::user()->id;

        $reportPeriodsArray = $repository->getReportingPeriods($userId);

        return view('reporting', ['reportPeriodsArray' => $reportPeriodsArray]);
    }

    /**
     * Download report
     *
     * @param ReportDownloadRequest $request
     * @param LsaRankExport $lsaRankExport
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(ReportDownloadRequest $request, LsaRankExport $lsaRankExport)
    {
        $keywordId = $request->query('keyword_id');
        $keyword = Keyword::find($keywordId);
        $keywordName = $keyword->keyword;
        $periodStart = $request->query('start_date');
        $periodEnd = $request->query('end_date');
        $name = "$keywordName-$periodStart-$periodEnd";

        $fileName = "$name.xlsx";

        return Excel::download($lsaRankExport, $fileName);
    }

    /**
     * Render map for reporting
     *
     * @param Request $request
     * @param BusinessEntityHeatMap $heatMap
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function renderMap(Request $request, BusinessEntityHeatMap $heatMap)
    {
        $batchId = $heatMap->last_batch_id;
        $businessEntityId = $heatMap->business_entity_id;
        $userId = $heatMap->user_id;
        $initialLat = $heatMap->latitude;
        $initialLon = $heatMap->longitude;
        $carbon = Carbon::now('UTC');
        $end = $carbon->copy();
        $start = $carbon->copy()->subHours(24);
        $businessEntityRadiusRanking = BusinessEntityZipcodeRadiusRanking::where('heat_map_id', $heatMap->id)->where('business_entity_id', $businessEntityId)->where('user_id', $userId)->where('batch_id', $batchId)->where('created_at', '>=', $start->format('Y-m-d H:i:s'))->where('created_at', '<=', $end->format('Y-m-d H:i:s'))->get();

        if (!count($businessEntityRadiusRanking)) {
            $businessEntityRadiusRanking = BusinessEntityZipcodeRadiusRanking::where('heat_map_id', $heatMap->id)->where('business_entity_id', $businessEntityId)->where('user_id', $userId)->where('batch_id', $batchId)->orderBy('created_at', 'DESC')->get();
        }

        $zipRadiusArray = json_decode($heatMap->zip_radius, true);
        $zipRadiusCollection = collect($zipRadiusArray)->keyBy('postal_code');

        $resultArray = [];
        $processedArray = [];
        foreach ($businessEntityRadiusRanking as $radiusRankingItem) {
            $zipCode = $radiusRankingItem->zipcode;
            $lsaRank = (int) $radiusRankingItem->lsa_rank;
            $maxRank = (int) $radiusRankingItem->max_rank;

            if (isset($processedArray[$zipCode])) {
                break;
            }

            if (isset($zipRadiusCollection[$zipCode])) {
                if ($lsaRank <= 3) {
                    $color = 'green';
                } elseif ($lsaRank > 3 && $lsaRank <= 10) {
                    $color = 'yellow';
                } else {
                    $color = 'red';
                }

                $lat = $zipRadiusCollection[$zipCode]['lat'];
                $lng = $zipRadiusCollection[$zipCode]['lng'];

                $distance = $this->distance($initialLat, $initialLon, $lat, $lng, 'K');

                if ($distance > 60) {
                    continue;
                }

                array_push($resultArray, [
                    'lat' => $zipRadiusCollection[$zipCode]['lat'],
                    'lng' => $zipRadiusCollection[$zipCode]['lng'],
                    'place_name' => $zipRadiusCollection[$zipCode]['place_name'],
                    'state' => $zipRadiusCollection[$zipCode]['state'],
                    'zipcode' => $zipCode,
                    'lsa_rank' => $lsaRank,
                    'max_rank' => $maxRank,
                    'color' => $color,
                ]);

                $processedArray[$zipCode] = true;
            }
        }

        return view('pdf.map', ['resultArray' => $resultArray]);
    }

    /**
     * Coordinate distance
     *
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @param $unit
     * @return float
     */
    private function distance($lat1, $lon1, $lat2, $lon2, $unit) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    /**
     * Generate PDF
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|string
     * @throws \App\Exceptions\HeathMapBatchMissingExcpetion
     * @throws \App\Exceptions\HeathMapNotPresentException
     * @throws \App\Exceptions\MapMissingException
     * @throws \App\Exceptions\MapScreenShotException
     * @throws \App\Exceptions\ZipCodeRankingsNotReadyException
     */
    public function generatePdf(Request $request)
    {
        $debug = false;

        if ($request->has('debug')) {
            $debug = true;
        }

        $heatmap = $request->input('heatmap', null);

        if (!$heatmap) {
            dd('stop');
        }

        $heatmap = BusinessEntityHeatMap::where('id', $heatmap)->first();

        $user = User::find($heatmap->user_id);

        $entity = BusinessEntity::find($heatmap->business_entity_id);

        $keyword = Keyword::find($heatmap->keyword_id);

        $reportService = new ReportService($user, $keyword, $entity);

        return $reportService->generatePdfReport('inline', $debug);
    }

    public function sendPDFoverEmail()
    {
        $heatmap = BusinessEntityHeatMap::find(1);

        $heatMapAttributes = $heatmap->getAttributes();

        GenerateAndSendWeeklyReport::dispatch($heatMapAttributes)->onQueue('low');
    }
}
