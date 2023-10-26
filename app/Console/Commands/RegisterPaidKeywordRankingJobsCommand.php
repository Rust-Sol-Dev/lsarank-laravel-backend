<?php

namespace App\Console\Commands;

use App\Jobs\RankLSABusinessEntitiesByKeywordJob;
use App\Models\Keyword;
use App\Models\User;
use App\Traits\ProxyRotation;
use Illuminate\Console\Command;

class RegisterPaidKeywordRankingJobsCommand extends Command
{
    use ProxyRotation;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register:paid:lsa:ranking:job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a batch of LSA ranking (scraping) job for keywords where enabled (paid)';

    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * RegisterKeywordRankingJobsCommand constructor.
     * @param Keyword $keyword
     */
    public function __construct(Keyword $keyword)
    {
        $this->keyword = $keyword;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $keyWordCollection = $this->keyword->with('owner')->where(['enabled' => 1])->get();

        foreach ($keyWordCollection as &$keyWord) {
            /** @var User $user */
            $user = $keyWord->owner;

            if (!$user->isActive()) {
                continue;
            }

            if (!$user->isPaid()) {
                continue;
            }

            $proxyData = $this->getRandomProxy();

            RankLSABusinessEntitiesByKeywordJob::dispatch($keyWord, $proxyData)->onQueue('high');
        }

        return Command::SUCCESS;
    }
}
