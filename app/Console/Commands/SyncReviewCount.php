<?php

namespace App\Console\Commands;

use App\Models\BusinessEntityReviewCount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SyncReviewCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:review:count {timestamp}';

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

        $startDate = '2023-02-26 00:00:00';

        $reviewCountCollection = BusinessEntityReviewCount::on('production')->where('keyword_id', 1)->where('created_at', '>=', $startDate)->get();

        $this->info("Fetching review count is completed");

        $reviewCount = count($reviewCountCollection);

        $bar = $this->output->createProgressBar($reviewCount);

        $bar->start();

        foreach ($reviewCountCollection as $reviewCount) {
            $payload = [
                'user_id' => $reviewCount->user_id,
                'keyword_id' => $reviewCount->keyword_id,
                'business_entity_id' => $reviewCount->business_entity_id,
                'review_count' => $reviewCount->review_count,
                'date' => $reviewCount->date,
                'timestamp' => $reviewCount->timestamp,
                'created_at' => $reviewCount->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $reviewCount->updated_at->format('Y-m-d H:i:s')
            ];

            try {
                DB::connection('mysql')->table('business_entity_review_count')->insert($payload);
            } catch (\Exception $exception) {
                $bar->advance();
                continue;
            }


            $bar->advance();
        }

        $bar->finish();

        return Command::SUCCESS;
    }
}
