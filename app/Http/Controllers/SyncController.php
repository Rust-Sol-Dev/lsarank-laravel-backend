<?php

namespace App\Http\Controllers;

use App\Models\BusinessEntityHeatMap;
use App\Models\BusinessEntityZipcodeRadiusRanking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SyncController
{
    /**
     * Sync radius
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncRadius(Request $request)
    {
        $keywordId = $request->input('keyword_id');
        $currentDate = $request->input('date');
        $googleId = $request->input('googleId');

        $user = User::where('google_id', $googleId)->first();
        $timezone = $user->tz;


        $preference = $user->preference($keywordId)->first();

        if (!$preference) {
            $this->show = false;
            return true;
        }

        $businessEntityId = $preference->business_entity_id;

        /** @var BusinessEntityHeatMap $businessEntityHeatMap */
        $businessEntityHeatMap = BusinessEntityHeatMap::where('user_id', $user->id)->where('keyword_id', $keywordId)->where('business_entity_id', $businessEntityId)->orderBy('created_at', 'DESC')->first();

        if (!$businessEntityHeatMap) {
            return true;
        }

        $batchId = $businessEntityHeatMap->last_batch_id;

        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate, $timezone);
        $end = $carbon->copy();
        $start = $carbon->copy()->subHours(24);
        $end->setTimezone('UTC');
        $start->setTimezone('UTC');

        $businessEntityRadiusRanking = BusinessEntityZipcodeRadiusRanking::where('heat_map_id', $businessEntityHeatMap->id)->where('business_entity_id', $businessEntityId)->where('user_id', $user->id)->where('batch_id', $batchId)->where('created_at', '>=', $start->format('Y-m-d H:i:s'))->where('created_at', '<=', $end->format('Y-m-d H:i:s'))->get();

        if (!count($businessEntityRadiusRanking)) {
            $businessEntityRadiusRanking = BusinessEntityZipcodeRadiusRanking::where('heat_map_id', $businessEntityHeatMap->id)->where('business_entity_id', $businessEntityId)->where('user_id', $user->id)->where('batch_id', $batchId)->orderBy('created_at', 'DESC')->get();
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


        return response()->json($resultArray);
    }

}
