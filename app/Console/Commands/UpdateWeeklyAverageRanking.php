<?php

namespace App\Console\Commands;

use App\Models\BusinessEntityRanking;
use App\Models\WeeklyAvgRank;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Keyword;
use Illuminate\Support\Facades\DB;

class UpdateWeeklyAverageRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:avg:rank:week';

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
        $today = Carbon::now('UTC');

        $keyWordCollection = Keyword::with('owner')->where(['enabled' => 1])->get();

        foreach ($keyWordCollection as &$keyWord) {
            $user = $keyWord->owner;

            if (!$user->isActive()) {
                continue;
            }

            $weekStart = $today->copy()->startOfWeek()->format('Y-m-d H:i:s');
            $weekEnd = $today->copy()->endOfWeek()->format('Y-m-d H:i:s');

            $currentDate = $today->format('Y-m-d H:i:s');

            $businessEntityRanking = BusinessEntityRanking::query();

            $groupedBusinessEntityCollection = $businessEntityRanking->selectRaw('business_entity_id, AVG(lsa_rank) as rank_avg')
                ->where('keyword_id', $keyWord->id)
                ->where('created_at', '>=', $weekStart)
                ->where('created_at', '<=', $currentDate)
                ->groupBy('business_entity_id')
                ->get();

            foreach ($groupedBusinessEntityCollection as $groupedData) {
                $avgRank = WeeklyAvgRank::create([
                    'keyword_id' => $keyWord->id,
                    'business_entity_id' => $groupedData->business_entity_id,
                    'week_start' => $weekStart,
                    'week_end' => $weekEnd,
                    'current_date' => $currentDate,
                    'rank_avg' => $groupedData->rank_avg
                ]);

                $this->info($avgRank->id);
            }
        }

        return Command::SUCCESS;
    }
}
