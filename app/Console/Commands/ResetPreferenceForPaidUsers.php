<?php

namespace App\Console\Commands;

use App\Models\BusinessEntityHeatMap;
use App\Models\User;
use App\Models\UserEntityPreference;
use Illuminate\Console\Command;

class ResetPreferenceForPaidUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:preference:paid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset preference for paid users without heatmap generated';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $heathMapUserIdArray = BusinessEntityHeatMap::selectRaw("DISTINCT(user_id)")->get()->keyBy('user_id')->toArray();

        $heathMapUserIdArray = array_keys($heathMapUserIdArray);

        $paidUserArray = User::where('paid', 1)->get('id')->keyBy('id')->toArray();

        $paidUserIdArray = array_keys($paidUserArray);

        $paidUserIdArrayToReset = array_diff($paidUserIdArray, $heathMapUserIdArray);

        $result = UserEntityPreference::whereIn('user_id', $paidUserIdArrayToReset)->delete();

        $this->info("$result preferences deleted (reseted)");

        return Command::SUCCESS;
    }
}
