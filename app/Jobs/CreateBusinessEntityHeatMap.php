<?php

namespace App\Jobs;

use App\Exceptions\CoordinatesNotFound;
use App\Exceptions\LocationNotFoundException;
use App\Exceptions\ZipCodeNotFound;
use App\Models\BusinessEntityHeatMap;
use App\Models\Keyword;
use Biscolab\GoogleMaps\Http\Result\GeocodingResult;
use Biscolab\GoogleMaps\Object\AddressComponent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Biscolab\GoogleMaps\Object\Geometry;
use Biscolab\GoogleMaps\Object\Location;
use Biscolab\GoogleMaps\Api\Geocoding;
use Biscolab\GoogleMaps\Enum\GoogleMapsApiConfigFields;
use Biscolab\GoogleMaps\Object\LatLng;
use Biscolab\GoogleMaps\Fields\LatLngFields;
use Illuminate\Support\Facades\DB;

class CreateBusinessEntityHeatMap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 999999;

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var Geocoding
     */
    public $geocoding;

    /**
     * @var Keyword
     */
    public $keyword;

    /**
     * @var integer
     */
    public $businessEntityId;

    /**
     * @var integer
     */
    public $userId;

    /**
     * @var string
     */
    public $location;

    /**
     * CreateBusinessEntityHeatMap constructor.
     * @param $keywordId
     * @param $businessEntityId
     * @param $userId
     */
    public function __construct($data)
    {
        $this->keyword = Keyword::find($data['keyword_id']);

        $this->businessEntityId = $data['business_entity_id'];

        $this->userId = $data['user_id'];
    }

    /**
     * Handle the job
     *
     * @throws CoordinatesNotFound
     * @throws LocationNotFoundException
     * @throws ZipCodeNotFound
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $location = $this->keyword->location;

        $this->location = str_replace('+', ' ', $location);

        $geocoding = new Geocoding([
            GoogleMapsApiConfigFields::KEY => env('GOOGLE_MAPS_API_KEY', null)
        ]);

        $results = $geocoding->getByAddress($this->location);

        if (!count($results)) {
            activity()->event('MAP_LOCATION_NOT_FOUND')->log($this->location . "not found");
            throw new LocationNotFoundException("$this->location not found");
        }

        $result = $results->first();

        $placeId = $result->getPlaceId();

        /** @var Geometry $geometry */
        $geometry = $result->getGeometry();

        /** @var Location $location */
        $location = $geometry->getLocation();

        $lat = $location->getLat();
        $lng = $location->getLng();

        $results = $geocoding->getByLatLng(new LatLng([
            LatLngFields::LAT => $lat,
            LatLngFields::LNG => $lng,
        ]));

        if (!count($results)) {
            activity()->event('MAP_COORDINATES_NOT_FOUND')->log($lat . ' ' . $lng . " not found");
            throw new CoordinatesNotFound("$lat $lng not found");
        }

        /** @var GeocodingResult $coordinateGeocodeResult */
        $coordinateGeocodeResult = $results->first();

        $addressComponents = $coordinateGeocodeResult->getAddressComponents();

        $zipCodeSingle = null;

        foreach ($addressComponents as $addressComponent) {
            /** @var AddressComponent $addressComponent */
            $types = $addressComponent->getTypes();

            if (in_array('postal_code', $types)) {
                $zipCodeSingle = $addressComponent->getLongName();
                break;
            }
        }

        if (!$zipCodeSingle) {
            throw new ZipCodeNotFound();
        }

        $client = new \GuzzleHttp\Client();
        $res = $client->get("https://zip-api.eu/api/v1/radius/US-$zipCodeSingle/10/km");
        $zipCodeRadiusResults = (string) $res->getBody();

        if (!$zipCodeRadiusResults) {
            throw new ZipCodeNotFound("Radius of $zipCodeSingle not found");
        }

        $zipCodeRadiusArray = json_decode($zipCodeRadiusResults, true);


        if (!count($zipCodeRadiusArray)) {
            throw new ZipCodeNotFound("Radius of $zipCodeSingle not found");
        }

        $duplicateMapping = [];
        $duplicateArray = [];

        foreach ($zipCodeRadiusArray as $key => $zipCodeGeo) {
            $lat = (float) $zipCodeGeo['lat'];
            $lng = (float) $zipCodeGeo['lng'];

            $result = $lat - $lng;

            $stringResult = (string) $result;

            if (!isset($duplicateMapping[$stringResult])) {
                $duplicateMapping[$stringResult] = true;
            } else {
                $duplicateArray[$zipCodeGeo['postal_code']] = $key;
            }
        }

        foreach ($duplicateArray as $zipCode => $index) {
            try {
                $results = $geocoding->getByAddress($zipCode);
            } catch (\Exception $exception) {
                try {
                    $results = $geocoding->getByAddress("$zipCode,USA");
                } catch (\Exception $exception) {
                    continue;
                }
            }


            if (!count($results)) {
                continue;
            }

            $result = $results->first();

            /** @var Geometry $geometry */
            $geometry = $result->getGeometry();

            /** @var Location $location */
            $location = $geometry->getLocation();

            $lat = $location->getLat();
            $lng = $location->getLng();


            $zipCodeRadiusArray[$index]['lat'] = $lat;
            $zipCodeRadiusArray[$index]['lng'] = $lng;
        }

        $zipCodeDataJson = json_encode($zipCodeRadiusArray);

        $result = BusinessEntityHeatMap::where('user_id', $this->userId)->where('keyword_id', $this->keyword->id)->where('business_entity_id', $this->businessEntityId)->count();

        if (!$result) {
            DB::table('business_entity_heat_map')->insert([
                'user_id' => $this->userId,
                'keyword_id' => $this->keyword->id,
                'business_entity_id' => $this->businessEntityId,
                'location' => $this->location,
                'latitude' => $lat,
                'longitude' => $lng,
                'place_id' => $placeId,
                'zip_code' => $zipCodeSingle,
                'zip_radius' => $zipCodeDataJson,
                'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
            ]);
        }

        /** @var Model $heatMapData */
        $heatMapData = BusinessEntityHeatMap::where('user_id', $this->userId)->where('keyword_id', $this->keyword->id)->where('business_entity_id', $this->businessEntityId)->orderBy('created_at', 'DESC')->first();

        DispatchZipCodeJobs::dispatch($heatMapData->id)->onQueue('radius');
    }
}
