<?php

namespace App\Console\Commands;

use App\Models\BusinessEntityRanking;
use App\Models\DailyAvgRank;
use App\Models\Keyword;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateDailyAverageRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:avg:rank';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update daily avg rank ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now('UTC')->format('Y-m-d');

        $keyWordCollection = Keyword::with('owner')->where(['enabled' => 1])->get();

        foreach ($keyWordCollection as &$keyWord) {
            $user = $keyWord->owner;

            if (!$user->isActive()) {
                continue;
            }

            $dailyAverageRanking = BusinessEntityRanking::query();

            $dailyAverageCollection = $dailyAverageRanking->selectRaw('business_entity_id, AVG(lsa_rank) as rank_avg, DATE_FORMAT(created_at,"%Y-%m-%d") AS date')
                ->from('business_entities_ranking')
                ->where('keyword_id', $keyWord->id)
                ->groupBy(['business_entity_id', 'date'])
                ->having('date', $today)
                ->get();

            foreach ($dailyAverageCollection as $dailyAverage) {
                DailyAvgRank::updateOrCreate([
                    "business_entity_id" => $dailyAverage->business_entity_id,
                    "date" => $dailyAverage->date,
                    "keyword_id" => $keyWord->id,
                ], [
                    "rank_avg" => $dailyAverage->rank_avg,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
