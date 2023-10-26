<?php

namespace App\Console\Commands;

use App\Models\Keyword;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\BusinessEntityRanking;
use App\Models\DailyAvgRank;
use Illuminate\Support\Facades\App;

class UpdateDailyAverageRankingPast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:past:avg:rank {timestamp}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update weekly avg rank ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $env = App::environment();

        if ($env == 'production') {
            dd("This is only run locally");
        }

        $startDate = $this->argument('timestamp')[0];

        if (!$startDate) {
            dd("Start date is missing");
        }

        $today = Carbon::parse($startDate)->timezone('UTC');
        $endDate = Carbon::now('UTC');
        $endDateString = $endDate->format('Y-m-d H:i:s');

        do {
            $dailyAverageRanking = BusinessEntityRanking::query();

            $dailyAverageCollection = $dailyAverageRanking->selectRaw('business_entity_id, AVG(lsa_rank) as rank_avg, DATE_FORMAT(created_at,"%Y-%m-%d") AS date')
                ->from('business_entities_ranking')
                ->where('keyword_id', 1)
                ->groupBy(['business_entity_id', 'date'])
                ->having('date', $today->format('Y-m-d'))
                ->get();

            foreach ($dailyAverageCollection as $dailyAverage) {
                try {
                    DailyAvgRank::updateOrCreate([
                        "business_entity_id" => $dailyAverage->business_entity_id,
                        "date" => $dailyAverage->date,
                        "keyword_id" => 1,
                    ], [
                        "rank_avg" => $dailyAverage->rank_avg,
                    ]);
                } catch (\Exception $exception) {
                    continue;
                }
            }

            $today->addHours(24);
            $result = $today->lte($endDateString);
        } while ($result);

        return Command::SUCCESS;
    }
}
