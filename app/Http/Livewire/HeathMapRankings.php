<?php

namespace App\Http\Livewire;

use App\Models\BusinessEntityHeatMap;
use App\Models\BusinessEntityZipcodeRadiusRanking;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use App\Models\Keyword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class HeathMapRankings extends Component
{
    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * @var string
     */
    public $currentDate;

    /**
     * @var string
     */
    public $businessEntityId;

    /**
     * @var bool
     */
    public $show = false;

    /**
     * @var bool
     */
    public $enabled = false;

    /**
     * @var string
     */
    public $lat = -25.344;

    /**
     * @var string
     */
    public $lng = 131.031;

    /**
     * @var array
     */
    public $zipCodeRankings;

    /**
     * @var integer
     */
    public $keywordId;

    /**
     * @var integer
     */
    public $googleId;

    /**
     * Mount the component
     *
     * @param Keyword $keyword
     * @param string $currentDate
     */
    public function mount(Keyword $keyword, string $currentDate)
    {
        $user = Auth::user();

        $this->googleId = $user->google_id;
        $this->currentDate = $currentDate;
        $this->keyword = $keyword;
        $this->keywordId = $keyword->id;

        $this->mapReady();
    }

    /**
     * Mount the component
     *
     * @param Keyword $keyword
     * @param string $currentDate
     */
    public function update(Keyword $keyword, string $currentDate)
    {
        $this->currentDate = $currentDate;
        $this->keyword = $keyword;

        $this->mapReady();
    }

    /**
     * Check if map ready
     *
     * @return bool
     */
    public function mapReady()
    {
        /** @var User $user */
        $user = Auth::user();

        $timezone = $user->tz;

        $preference = $user->preference($this->keyword->id)->first();

        if (!$preference || !$user->isPaid()) {
            $this->show = false;
            $this->enabled = false;
            return true;
        }

        $this->enabled = true;

        $this->businessEntityId = $preference->business_entity_id;

        /** @var BusinessEntityHeatMap $businessEntityHeatMap */
        $businessEntityHeatMap = BusinessEntityHeatMap::where('user_id', $user->id)->where('keyword_id', $this->keyword->id)->where('business_entity_id', $this->businessEntityId)->orderBy('created_at', 'DESC')->first();

        if (!$businessEntityHeatMap) {
            return true;
        }

        $this->show = true;
        $this->lat = $businessEntityHeatMap->latitude;
        $this->lng = $businessEntityHeatMap->longitude;
        $batchId = $businessEntityHeatMap->last_batch_id;

        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $this->currentDate, $timezone);
        $end = $carbon->copy();
        $start = $carbon->copy()->subHours(24);
        $end->setTimezone('UTC');
        $start->setTimezone('UTC');

        $businessEntityRadiusRanking = BusinessEntityZipcodeRadiusRanking::where('heat_map_id', $businessEntityHeatMap->id)->where('business_entity_id', $this->businessEntityId)->where('user_id', $user->id)->where('batch_id', $batchId)->where('created_at', '>=', $start->format('Y-m-d H:i:s'))->where('created_at', '<=', $end->format('Y-m-d H:i:s'))->get();

        if (!count($businessEntityRadiusRanking)) {
            $businessEntityRadiusRanking = BusinessEntityZipcodeRadiusRanking::where('heat_map_id', $businessEntityHeatMap->id)->where('business_entity_id', $this->businessEntityId)->where('user_id', $user->id)->where('batch_id', $batchId)->orderBy('created_at', 'DESC')->get();
        }

        $zipRadiusArray = json_decode($businessEntityHeatMap->zip_radius, true);
        $zipRadiusCollection = collect($zipRadiusArray)->keyBy('postal_code');

        $resultArray = [];

        $processedArray = [];
        foreach ($businessEntityRadiusRanking as $radiusRankingItem) {
            $zipCode = $radiusRankingItem->zipcode;
            $lsaRank = (int) $radiusRankingItem->lsa_rank;
            $maxRank = (int) $radiusRankingItem->max_rank;

            if (isset($processedArray[$zipCode])) {
                break;
            }

            if (isset($zipRadiusCollection[$zipCode])) {
                if ($lsaRank <= 3) {
                    $color = 'green';
                } elseif ($lsaRank > 3 && $lsaRank <= 10) {
                    $color = 'yellow';
                } else {
                    $color = 'red';
                }

//                if ($maxRank > 10) {
//                    $relativePercent = (float) 1/$maxRank;
//                    $oneThird = (float) $maxRank/3;
//                    $twoThird = $oneThird * 2;
//                    $yellowStarts = (float) ($oneThird * $relativePercent);
//                    $redStarts = (float) ($twoThird * $relativePercent);
//                    $rankPercent = (float) ($lsaRank/$maxRank);
//                    $color = null;
//
//                    if ($rankPercent < $yellowStarts) {
//                        $color = 'green';
//                    } elseif ($rankPercent >= $yellowStarts && $rankPercent < $redStarts) {
//                        $color = 'yellow';
//                    } else {
//                        $color = 'red';
//                    }
//                } else {
//                    if ($lsaRank <= 5) {
//                        $color = 'green';
//                    } else {
//                        $color = 'yellow';
//                    }
//                }

                array_push($resultArray, [
                    'lat' => $zipRadiusCollection[$zipCode]['lat'],
                    'lng' => $zipRadiusCollection[$zipCode]['lng'],
                    'place_name' => $zipRadiusCollection[$zipCode]['place_name'],
                    'state' => $zipRadiusCollection[$zipCode]['state'],
                    'zipcode' => $zipCode,
                    'lsa_rank' => $lsaRank,
                    'max_rank' => $maxRank,
                    'color' => $color,
                ]);

                $processedArray[$zipCode] = true;
            }
        }


        if (count($resultArray)) {
            $this->zipCodeRankings = $resultArray;
        }
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        $this->mapReady();

        return view('livewire.heath-map-rankings');
    }
}
