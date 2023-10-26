<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class UpdatePastData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:past:data';

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

        $this->info("Sync raw rankings from production");

        $this->call('sync:raw:rankings', ['timestamp' => ['2023-02-23 20:35:00']]);

        $this->info("==============================================");
        $this->info("Sync raw rankings completed");

        $this->info("==============================================");

        $this->info("Update past daily average ranking.");

        $this->call('update:past:avg:rank', ['timestamp' => ['2023-02-25 00:00:00']]);

        $this->info("==============================================");

        $this->info("Update past daily average ranking completed");

        $this->info("==============================================");

        $this->info("Update past weekly average ranking.");

        $this->call('update:past:avg:rank:week', ['timestamp' => ['2023-02-23 20:35:00']]);

        $this->info("==============================================");

        $this->info("Update past weekly average ranking completed");

        return Command::SUCCESS;
    }
}
