<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use Ahc\Jwt\JWT;
use Spatie\Image\Manipulations;

class PuppeteerService
{
    /**
     * Screenshot map for report
     *
     * @param string $heatMapId
     * @return string
     */
    public function screenShotMap(string $heatMapId)
    {
        $mapRoute = route('map-report', ['heatMap' => $heatMapId]);
        $jwt = new JWT(env('APP_KEY'));
        $token = $jwt->encode([
            'uid' => $heatMapId,
            'aud'    => env('APP_URL'),
            'scopes' => ['report'],
            'iss'    => env('APP_URL'),
        ]);

        $mapRoute = $mapRoute . "?token=$token";
        $base64Data = Browsershot::url($mapRoute)
            ->setNodeBinary('/home/andrija/node/bin/node')
            ->setNpmBinary('/home/andrija/node/bin/npm')
            ->timeout(200)
            ->setScreenshotType('png')
            ->setOption('args', ['--start-maximized', '--no-sandbox'])
            ->windowSize(1260, 1130)
            ->fit(Manipulations::FIT_CONTAIN, 600, 600)
            ->deviceScaleFactor(2)
            ->landscape()
            ->waitUntilNetworkIdle()
            ->select("#map > div")
            ->base64Screenshot();
//            ->save($fileFullPath);

        $imgBinary = "data:image/png;base64,$base64Data";

        return $imgBinary;
    }
}
