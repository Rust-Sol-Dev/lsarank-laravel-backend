<?php

namespace App\Console\Commands;

use App\Jobs\CleanKeywordData;
use App\Models\Keyword;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class MigrateExistingKeywords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:keywords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $mapping = [
        8 => [
            'keyword' => 'Personal+injury+lawyer',
            'original_keyword' => 'Personal injury lawyer',
            'location' => 'Los Angeles',
        ],
        10 => [
            'keyword' => 'personal+injury+lawyers',
            'original_keyword' => 'personal injury lawyers',
            'location' => '',
        ],
        11 => [
            'keyword' => 'personal+injury+lawyers',
            'original_keyword' => 'personal injury lawyers',
            'location' => 'tampa',
        ],
        12 => [
            'keyword' => 'car+accident+lawyers',
            'original_keyword' => 'car accident lawyers',
            'location' => 'tampa',
        ],
        13 => [
            'keyword' => 'car+accident+lawyer',
            'original_keyword' => 'car accident lawyer',
            'location' => 'tampa',
        ],
        14 => [
            'keyword' => 'personal+injury+lawyer',
            'original_keyword' => 'personal injury lawyer',
            'location' => 'tampa',
        ],
        15 => [
            'keyword' => 'motorcycle+accident+lawyer',
            'original_keyword' => 'motorcycle accident lawyer',
            'location' => 'tampa',
        ],
        16 => [
            'keyword' => 'personal+injury+lawyer',
            'original_keyword' => 'personal injury lawyer',
            'location' => 'st petersburg',
        ],
        17 => [
            'keyword' => 'personal+injury+lawyer',
            'original_keyword' => 'personal injury lawyer',
            'location' => 'clearwater',
        ],
        18 => [
            'keyword' => 'personal+injury+lawyer',
            'original_keyword' => 'personal injury lawyer',
            'location' => 'new port richey',
        ],
        19 => [
            'keyword' => 'car+accident+lawyer',
            'original_keyword' => 'car accident lawyer',
            'location' => ' new port richey',
        ],
        20 => [
            'keyword' => 'car+accident+lawyer',
            'original_keyword' => 'car accident lawyer',
            'location' => 'clearwater',
        ],
        21 => [
            'keyword' => 'car+accident+lawyer',
            'original_keyword' => 'car accident lawyer',
            'location' => 'st petersburg',
        ],
        22 => [
            'keyword' => 'motorcycle+accident+lawyer',
            'original_keyword' => 'motorcycle accident lawyer',
            'location' => 'clearwater',
        ],
        23 => [
            'keyword' => 'motorcycle+accident+lawyer',
            'original_keyword' => 'motorcycle accident lawyer',
            'location' => 'new port richey',
        ],
        24 => [
            'keyword' => 'motorcycle+accident+lawyer',
            'original_keyword' => 'motorcycle accident lawyer',
            'location' => 'st petersburg',
        ],
        25 => [
            'keyword' => 'criminal+defense+lawyer',
            'original_keyword' => 'criminal defense lawyer',
            'location' => 'colorado springs',
        ],
        26 => [
            'keyword' => 'dui+defense+lawyer',
            'original_keyword' => 'dui defense lawyer',
            'location' => 'colorado springs',
        ],
        27 => [
            'keyword' => 'real+estate+agents',
            'original_keyword' => 'real estate agents',
            'location' => 'boston',
        ],
        28 => [
            'keyword' => 'real+estate+agents',
            'original_keyword' => 'real estate agents',
            'location' => 'Jacksonville, NC',
        ],
        29 => [
            'keyword' => 'realtors',
            'original_keyword' => 'realtors',
            'location' => 'Jacksonville, NC',
        ],
        30 => [
            'keyword' => 'realtors',
            'original_keyword' => 'realtors',
            'location' => 'boston',
        ],
        31 => [
            'keyword' => 'malpractice+lawyers',
            'original_keyword' => 'malpractice lawyers',
            'location' => 'baltimore',
        ],
        32 => [
            'keyword' => 'malpractice+lawyers',
            'original_keyword' => 'malpractice lawyers',
            'location' => 'chicago',
        ],
        33 => [
            'keyword' => 'real+estate+agents',
            'original_keyword' => 'real estate agents',
            'location' => 'phoenix',
        ],
        34 => [
            'keyword' => 'real+estate+agents',
            'original_keyword' => 'real estate agents',
            'location' => 'scottsdale',
        ],
        35 => [
            'keyword' => 'real+estate+agents',
            'original_keyword' => 'real estate agents',
            'location' => 'coronado',
        ],
        36 => [
            'keyword' => 'portland+criminal+defense+attorney',
            'original_keyword' => 'criminal defense attorney',
            'location' => 'portland',
        ],
        37 => [
            'keyword' => 'DUI+defense+attorney',
            'original_keyword' => 'DUI defense attorney',
            'location' => 'portland',
        ],
        38 => [
            'keyword' => 'Personal+Injury+Lawyer',
            'original_keyword' => 'Personal Injury Lawyer ',
            'location' => '',
        ],
        40 => [
            'keyword' => 'personal+injury+lawyer',
            'original_keyword' => 'personal injury lawyer',
            'location' => 'orangeburg sc',
        ],
        41 => [
            'keyword' => 'personal+injury+law+firm',
            'original_keyword' => 'personal injury law firm',
            'location' => 'sumter sc',
        ],
        42 => [
            'keyword' => 'personal+injury+lawyer',
            'original_keyword' => 'personal injury lawyer',
            'location' => 'greenville sc',
        ],
        43 => [
            'keyword' => 'dui+lawyer',
            'original_keyword' => 'dui lawyer',
            'location' => 'columbia sc',
        ],
        44 => [
            'keyword' => 'Plumber',
            'original_keyword' => 'Plumber',
            'location' => 'massapequa ny',
        ],
        45 => [
            'keyword' => 'plumber',
            'original_keyword' => 'plumber',
            'location' => 'wantagh ny',
        ],
        46 => [
            'keyword' => 'personal+injury+lawyer',
            'original_keyword' => 'personal injury lawyer',
            'location' => 'charleston sc',
        ],
        47 => [
            'keyword' => 'heating+repair',
            'original_keyword' => 'heating repair',
            'location' => 'hudsonville mi',
        ],
        48 => [ //39
            'keyword' => 'hvac+contractor',
            'original_keyword' => 'hvac contractor',
            'location' => 'tulsa ok',
        ],
        49 => [
            'keyword' => 'heating+repair',
            'original_keyword' => 'heating repair',
            'location' => 'glenpool ok',
        ],
        50 => [
            'keyword' => 'plumber',
            'original_keyword' => 'plumber',
            'location' => 'tulsa ok',
        ],
        51 => [
            'keyword' => 'plumber',
            'original_keyword' => 'plumber',
            'location' => 'glenpool ok',
        ],
        65 => [
            'keyword' => 'family+lawyer+services',
            'original_keyword' => 'family lawyer services',
            'location' => 'los angeles',
        ],
        68 => [
            'keyword' => 'Movers',
            'original_keyword' => 'Movers',
            'location' => 'Los Angeles',
        ],
        71 => [
            'keyword' => 'Personal+Injury+Lawyer',
            'original_keyword' => 'Personal Injury Lawyer',
            'location' => 'Los Angeles',
        ],
        79 => [
            'keyword' => 'garage+door+repair',
            'original_keyword' => 'garage door repair',
            'location' => '',
        ],
        89 => [
            'keyword' => 'movers',
            'original_keyword' => 'movers',
            'location' => 'new york',
        ],
        91 => [
            'keyword' => 'Personal+Injury+Lawyer',
            'original_keyword' => 'Personal Injury Lawyer',
            'location' => 'New York',
        ],
        92 => [
            'keyword' => 'water+damage+service',
            'original_keyword' => 'water damage service',
            'location' => '',
        ],
        95 => [
            'keyword' => 'Personal+injury+lawyer',
            'original_keyword' => 'Personal injury lawyer',
            'location' => 'lakeland',
        ],
        98 => [
            'keyword' => 'movers',
            'original_keyword' => 'movers',
            'location' => 'Los Angeles',
        ],
        101 => [
            'keyword' => 'crimininal+defense',
            'original_keyword' => 'crimininal defense',
            'location' => '',
        ],
        102 => [
            'keyword' => 'pest+control+services',
            'original_keyword' => 'pest control services',
            'location' => 'denver, co',
        ],
        122 => [
            'keyword' => 'commercial+pest+control',
            'original_keyword' => 'commercial pest control',
            'location' => 'New York',
        ],
        158 => [
            'keyword' => 'General+Contractor',
            'original_keyword' => 'General Contractor',
            'location' => 'New York',
        ]
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $keywordCollection = Keyword::all();

        $counter = 0;

        foreach ($keywordCollection as $keyword) {
//            if ($keyword->id > 161) {
//                continue;
//            }

            $urlKeyword = $keyword->keyword;
            $urlKeyword = strtolower($urlKeyword);
            $originalKeyword = $keyword->original_keyword;
            $originalKeyword = strtolower($originalKeyword);


            if (str_contains($urlKeyword, 'near')) {
                $explodedUrlKeyword = explode('near', $urlKeyword);
                $newKeyword = substr_replace($explodedUrlKeyword[0] ,"", -1);
                $location =  substr($explodedUrlKeyword[1],1);

                $explodedKeyword = explode('near', $originalKeyword);
                $newOriginalKeyword = substr_replace($explodedKeyword[0] ,"", -1);
                $explodedKeyword[1] =  substr($explodedKeyword[1],1);

                $keyword->keyword = $newKeyword;
                $keyword->original_keyword = $newOriginalKeyword;
                $keyword->location = $location;

                $keyword->save();
            } else {
                if (!isset($this->mapping[$keyword->id])) {
                    continue;
                }

                $mapping = $this->mapping[$keyword->id];
                $keyword->keyword = $mapping['keyword'];
                $keyword->original_keyword = $mapping['original_keyword'];
                $keyword->location = $mapping['location'];

                $keyword->save();
            }
        }

        $emptyKeywordCollection = Keyword::where('location', '')->oRwhereNull('location')->get();

        foreach ($emptyKeywordCollection as $emptyLocationKeyword) {
            $exploded = explode('+', $emptyLocationKeyword->keyword);

            if (count($exploded) < 3) {
                continue;
            }

            $partsStart = count($exploded) - 2;
            $partsEnd = count($exploded) - 1;



            $location = implode(' ', [$exploded[$partsStart], $exploded[$partsEnd]]);

            $result = in_array($location, ['new york', 'denver', 'las vegas', 'orlando', 'beavercreek oh', 'dayton oh', 'huntington ny', 'los angeles', 'Las Vegas', 'New York', 'indianapolis in', 'canton ma'] );

            if ($result != false) {

                $originalKeyword = implode(' ', [$exploded[0], $exploded[$partsStart-1]]);
                $keyword = str_replace(' ', '+', $originalKeyword);

                $emptyLocationKeyword->location = strtolower($location) ;
                $emptyLocationKeyword->keyword = strtolower($keyword) ;
                $emptyLocationKeyword->original_keyword = strtolower($originalKeyword) ;
                $emptyLocationKeyword->save();
            }

        }

        foreach ($emptyKeywordCollection as $emptyLocationKeyword) {
            $exploded = explode('+', $emptyLocationKeyword->keyword);

            if (count($exploded) < 2) {
                continue;
            }

            $partsStart = count($exploded) - 1;

            $location = implode(' ', [$exploded[$partsStart]]);

            $result = in_array($location, ['new york', 'denver', 'las vegas', 'orlando', 'beavercreek oh', 'dayton oh', 'huntington ny', 'los angeles', 'Las Vegas', 'New York', 'indianapolis in', 'canton ma'] );

            if ($result != false) {

                $originalKeyword = implode(' ', [$exploded[0], $exploded[$partsStart-1]]);
                $keyword = str_replace(' ', '+', $originalKeyword);

                $emptyLocationKeyword->location = strtolower($location) ;
                $emptyLocationKeyword->keyword = strtolower($keyword) ;
                $emptyLocationKeyword->original_keyword = strtolower($originalKeyword) ;
                $emptyLocationKeyword->save();
            }

        }

        $emptyKeywordIds = Keyword::where('location', '')->oRwhereNull('location')->pluck('id')->toArray();

        $filledKeywords = Keyword::whereNotIn('id', $emptyKeywordIds)->get();

        foreach ($filledKeywords as $filledKeyword) {
            $location = $filledKeyword->location;
            $trimmed = trim($location, '+ " "');
            $locationNew = str_replace(' ', '+', $trimmed);
            $filledKeyword->location = $locationNew;
            $filledKeyword->save();
        }

        $emptyKeywords = Keyword::whereIn('id', $emptyKeywordIds)->get();

        foreach ($emptyKeywords as $emptyKeyword) {
            /** @var Model $emptyKeyword */
            $attributes = $emptyKeyword->getAttributes();

            CleanKeywordData::dispatch($attributes)->onQueue('low');

            $emptyKeyword->delete();
        }

        $this->info("Completed");

        return Command::SUCCESS;
    }
}
