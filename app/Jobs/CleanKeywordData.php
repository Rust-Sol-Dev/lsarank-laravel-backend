<?php

namespace App\Jobs;

use App\Models\BusinessEntityRanking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\HttpKernel\Log\record;
use function Termwind\ValueObjects\format;

class CleanKeywordData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 90000000;

    public $tries = 3;

    /**
     * @var array
     */
    public $attributes;

    /**
     * @var integer
     */
    public $keywordId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;

        $this->keywordId = $attributes['id'];
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '9000000000');

        DB::beginTransaction();

        try {
            $counter = 0;

            $rankingLazy = BusinessEntityRanking::where('keyword_id', $this->keywordId)->cursor();

            foreach ($rankingLazy as $ranking) {
                $counter++;
                $ranking->delete();
            }

        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
    }
}
