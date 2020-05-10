<?php

namespace mhapach\SwaggerModelGenerator\Libs\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ParseHelper
{
    /**
     * @param string $className
     * @return string
     */
    public static function getSafeClassName(string $className) {
        if (in_array(strtolower($className), ['class', 'type', 'empty']))
            $className = 'C'.$className;
        return $className;
   }
   
   public static function isJson(string $string) {
       $data = json_decode($string);
       return (json_last_error() == JSON_ERROR_NONE) ;
   }

}