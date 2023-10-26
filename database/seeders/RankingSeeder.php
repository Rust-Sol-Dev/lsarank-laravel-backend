<?php

namespace Database\Seeders;

use App\Models\BusinessEntityRanking;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rankingCollection = BusinessEntityRanking::where('day', 'wednesday')->get();

        foreach ($rankingCollection as $ranking) {
            $createdAt = $ranking->created_at;
            $nextDay = $createdAt->addDays(2)->format('Y-m-d H:i:s');
            $ranking->created_at = $nextDay;
            $ranking->save();
        }

        dd('done');

        dd($rankingCollection->first());

//        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
//
//        foreach ($days as $day) {
//            foreach ($rankingCollection as $ranking) {
//                $data = $ranking->getAttributes();
//                /** @var Carbon $carbon */
//                $carbon = $ranking->created_at;
//                $carbon->addDay();
//                $data['day'] = $day;
//                $data['created_at'] = $carbon->format('Y-m-d H:i:s');
//                $data['updated_at'] = $carbon->format('Y-m-d H:i:s');
//                unset($data['id']);
//                DB::table('business_entities_ranking')->insert($data);
//            }
//        }
//
//        dd('done');



        //($result);
    }
}
