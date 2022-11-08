<?php

namespace mhapach\SwaggerModelGenerator\Libs\Helpers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class DataHelper
{
    public static function isJson($str) : bool {
        $json = json_decode($str);
        return ($json && $str != $json);
    }
}
