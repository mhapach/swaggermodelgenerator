<?php

namespace mhapach\SwaggerModelGenerator\Libs\Helpers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class HttpHelper
{
    /**
     * @param $url
     * @param array $data = [
     *      'query' => [param => 112] - cgi params 
     *      'path' => [param => 112] - url param replacement
     *      'form_params' => [param => 112] - post params
     *      'body' => string {param : 112} - to send requestBody for expl json as string 
     *      'headers' => [
     *          'Accept' => '*\/*',
     *          'Content-Type' => 'application/x-www-form-urlencoded',
     *          'Cache-Control' => 'no-cache',
     *          "Authorization" => "Bearer {$accessMarker['access_token']}"          
     *      ]
     * ]
     * @param string $method
     * @return string|null
     * @throws Exception
     */
    public static function request($url, array $data = [], $method = 'get') {
        $method = strtolower($method);

        $response = null;

        $httpClient = new Client(['verify' => false ]);
        $result = null;
        try {
            $result = $httpClient->request($method, $url, $data);
            $response = (string)$result->getBody();
        }
        catch (RequestException $e) {
            $errorMessage = urldecode($e->getMessage());
            $errorCode = $e->getCode();
            if ($e->getResponse()->getStatusCode() == 400) 
                $errorMessage = urldecode($e->getResponse()->getBody()->getContents());            
        }
        catch (Exception $e) {
            $errorCode = $e->getCode();
            $errorMessage = urldecode($e->getMessage());
        }

        if (!empty($errorMessage) || !empty($errorCode)) {
            Log::error("Error Code: $errorCode. Error message: $errorMessage");
            throw new Exception($errorMessage, $errorCode);
        }

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