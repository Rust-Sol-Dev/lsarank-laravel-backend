<?php

namespace App\Helpers;

class BladeHelper
{
    /**
     * Hide URL params
     *
     * @param array $params
     * @return string
     */
    public static function hideUrlParams(array $params)
    {
        $arrayString = serialize($params);

        $base64 = base64_encode($arrayString);

        $final = str_rot13($base64);

        return $final;
    }

    /**
     * Decode URL params
     *
     * @param string $params
     * @return mixed
     */
    public static function decodeUrlParams(string $params)
    {
        $base64 = str_rot13($params);

        $arrayString = base64_decode($base64);

        $array = unserialize($arrayString);

        return $array;
    }
}
