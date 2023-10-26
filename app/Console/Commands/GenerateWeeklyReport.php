<?php

namespace App\Console\Commands;

use App\Jobs\GenerateAndSendWeeklyReport;
use App\Models\BusinessEntityHeatMap;
use App\Models\BusinessEntityZipcodeRadiusRanking;
use App\Models\Keyword;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateWeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:weekly:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $heatMapCollection = BusinessEntityHeatMap::with(['user', 'keyword'])->get();

        /** @var BusinessEntityHeatMap $heatMapData */
        foreach ($heatMapCollection as $heatMapData) {
            /** @var User $user */
            $user = $heatMapData->user;
            /** @var Keyword $keyword */
            $keyword = $heatMapData->keyword;

            if (!$keyword->enabled || !$user->isPaid() || !$user->isActive()) {
                activity()->event('PDF_REPORT_WEEKLY_HEAT_MAP_SKIPPED')->log("Heath map skipped for user " . $user->id . " keyword " . $keyword->id . " heath map " . $heatMapData->id);
                continue;
            }

            $result = $user->preference($keyword->id)->count();

            if (!$result) {
                activity()->event('PDF_REPORT_WEEKLY_USER_PREFERENCE_MISSING')->log("Heath map skipped for user " . $user->id . " keyword " . $keyword->id . " heath map " . $heatMapData->id);
                continue;
            }

            $batchId = $heatMapData->last_batch_id;

            if (!$batchId) {
                activity()->event('PDF_REPORT_WEEKLY_BATCH_ID_MISSING')->log("Heath map skipped for user " . $user->id . " keyword " . $keyword->id . " heath map " . $heatMapData->id);
                continue;
            }

            $batch = DB::table('job_batches')
                ->where('id',  $batchId)
                ->first();

            if (!$batch) {
                activity()->event('PDF_REPORT_WEEKLY_BATCH_MISSING')->log("Heath map skipped for user " . $user->id . " keyword " . $keyword->id . " heath map " . $heatMapData->id);
                continue;
            }

            $totalJobs = $batch->total_jobs;
            $pendingJobs = $batch->pending_jobs;
            $failedJobs = $batch->failed_jobs;

            $jobThreshold = $totalJobs * 0.5;

            $notCompletedJobs = $pendingJobs + $failedJobs;

            if ($notCompletedJobs > $jobThreshold) {
                $zipcodeRankingObject = BusinessEntityZipcodeRadiusRanking::select('batch_id')->where('heat_map_id', $heatMapData->id)->where('business_entity_id', $heatMapData->business_entity_id)->where('user_id', $user->id)->whereNotIn('batch_id', [$batchId])->groupBy('batch_id')->orderByRaw('MAX(id) DESC')->first();

                if (!$zipcodeRankingObject->batch_id) {
                    activity()->event('PDF_REPORT_WEEKLY_LAST_BATCH_SECOND_ATTEMPT_MISSING')->log("Heath map skipped for user " . $user->id . " keyword " . $keyword->id . " heath map " . $heatMapData->id);
                    continue;
                }
            }

            $heatMapAttributes = $heatMapData->getAttributes();

            GenerateAndSendWeeklyReport::dispatch($heatMapAttributes)->onQueue('low');
        }
    }
}
