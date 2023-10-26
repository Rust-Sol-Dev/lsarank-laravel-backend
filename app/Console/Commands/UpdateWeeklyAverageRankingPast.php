<?php

namespace App\Console\Commands;

use App\Models\DailyAvgRank;
use App\Models\WeeklyAvgRank;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use App\Models\Keyword;
use Illuminate\Support\Facades\App;

class UpdateWeeklyAverageRankingPast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:past:avg:rank:week  {timestamp}';

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

        $endDate = '2023-02-25 00:00:00';

        $carbonCurrent = Carbon::parse($startDate)->timezone('UTC')->addDay();
        $carbonEndDate = Carbon::parse($endDate)->timezone('UTC');

        do {
            $weekStart = $carbonCurrent->copy()->startOfWeek();
            $weekEnd = $carbonCurrent->copy()->endOfWeek();
            $weekStartString = $weekStart->format('Y-m-d H:i:s');
            $currentString = $carbonCurrent->format('Y-m-d H:i:s');

            $groupedBusinessEntityCollection = DailyAvgRank::selectRaw('business_entity_id, AVG(rank_avg) as avgrank')
                ->where('keyword_id', 1)
                ->where('date', '>=', $weekStartString)
                ->where('date', '<=', $currentString)
                ->groupBy("business_entity_id")
                ->havingRaw("avgrank")
                ->get();

            foreach ($groupedBusinessEntityCollection as $groupedData) {
                WeeklyAvgRank::create([
                    'keyword_id' => 1,
                    'business_entity_id' => $groupedData->business_entity_id,
                    'week_start' => $weekStart->format('Y-m-d H:i:s'),
                    'week_end' => $weekEnd->format('Y-m-d H:i:s'),
                    'current_date' => $carbonCurrent->format('Y-m-d H:i:s'),
                    'rank_avg' => $groupedData->avgrank
                ]);
            }

            $carbonCurrent->addDay();
            $result = $carbonCurrent->lte($carbonEndDate);
        } while ($result);

        return Command::SUCCESS;
    }
}
