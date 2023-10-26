<?php

namespace App\Console\Commands;

use App\Models\BusinessEntityRanking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class SyncRawRankings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:raw:rankings {timestamp}';

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
        $env = App::environment();

        if ($env == 'production') {
            dd("This is only run locally");
        }

        $startDate = $this->argument('timestamp')[0];

        if (!$startDate) {
            dd("Start date is missing");
        }

        $keywordRankingCollection = BusinessEntityRanking::on('production')->where('keyword_id', 1)->where('created_at', '>', $startDate)->get();

        $this->info("Fetching raw rankings for keyword 1 is completed");

        $rankingCount = count($keywordRankingCollection);

        $bar = $this->output->createProgressBar($rankingCount);

        $bar->start();

        foreach ($keywordRankingCollection as $keywordRanking) {
            $payload = [
                'user_id' => $keywordRanking->user_id,
                'keyword_id' => $keywordRanking->keyword_id,
                'business_entity_id' => $keywordRanking->business_entity_id,
                'lsa_rank' => $keywordRanking->lsa_rank,
                'day' => $keywordRanking->day,
                'created_at' => $keywordRanking->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $keywordRanking->updated_at->format('Y-m-d H:i:s')
            ];

            DB::connection('mysql')->table('business_entities_ranking')->insert($payload);

            $bar->advance();
        }

        $bar->finish();

        return Command::SUCCESS;
    }
}
