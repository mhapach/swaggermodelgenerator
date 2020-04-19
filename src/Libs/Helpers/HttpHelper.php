<?php

namespace mhapach\SwaggerModelGenerator\Libs\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class HttpHelper
{
    /**
     * @param $url
     * @param array $data
     * @param string $method
     * @return string|null
     * @throws \Exception
     */
    public static function request($url, array $data = [], $method = 'get') {
        $method = strtolower($method);

        $response = null;

        $httpClient = new Client(['verify' => false ]);
        $result = null;
        try {
            $result = $httpClient->request($method, $url, $data);
            $response = (string)$result->getBody();
        } catch (GuzzleException $e) {
            $errorMessage = urldecode($e->getMessage());
            Log::error($errorMessage);
        }

        if (!empty($errorMessage))
            throw new \Exception($errorMessage);

        return $response;
    }

    /**
     * @param array|null $requestParams
     * @return array
     */
    public static function encodeRequestParams(array $requestParams = null)
    {
        foreach ($requestParams as &$value) {
            if ($value && is_array($value))
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            $value = urlencode($value);
        }
        return $requestParams;
    }

    /**
     * @param array|null $requestParams
     * @return string
     */
    public static function stringifyRequestParams(array $requestParams = null)
    {
        $res = [];
        self::encodeRequestParams($requestParams);
        foreach ($requestParams as $key => $value) {
            $res[] = "$key=$value";
        }
        return implode('&', $res);
    }    

}