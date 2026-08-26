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
     * Собирает воспроизводимую curl-команду из Guzzle-параметров запроса.
     *
     * @param string $method
     * @param string $url
     * @param array $options Guzzle request options (headers, query, json, form_params, body, multipart, auth)
     * @param bool $insecure добавить -k (как Client verify => false)
     */
    public static function toCurl(string $method, string $url, array $options = [], bool $insecure = true): string
    {
        $parts = ['curl'];

        if ($insecure) {
            $parts[] = '-k';
        }

        $method = strtoupper($method);
        if ($method !== 'GET') {
            $parts[] = '-X ' . escapeshellarg($method);
        }

        if (!empty($options['query']) && is_array($options['query']) && strpos($url, '?') === false) {
            $url .= '?' . http_build_query($options['query']);
        }

        $parts[] = escapeshellarg($url);

        if (!empty($options['headers']) && is_array($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                foreach ((array)$value as $headerValue) {
                    $parts[] = '-H ' . escapeshellarg("$name: $headerValue");
                }
            }
        }

        if (!empty($options['auth']) && is_array($options['auth'])) {
            $user = (string)($options['auth'][0] ?? '');
            $pass = (string)($options['auth'][1] ?? '');
            $parts[] = '-u ' . escapeshellarg("$user:$pass");
        }

        if (array_key_exists('json', $options)) {
            $json = is_string($options['json'])
                ? $options['json']
                : json_encode($options['json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (empty($options['headers']['Content-Type']) && empty($options['headers']['content-type'])) {
                $parts[] = '-H ' . escapeshellarg('Content-Type: application/json');
            }
            $parts[] = '--data ' . escapeshellarg((string)$json);
        } elseif (!empty($options['form_params']) && is_array($options['form_params'])) {
            $parts[] = '--data ' . escapeshellarg(http_build_query($options['form_params']));
        } elseif (array_key_exists('body', $options) && $options['body'] !== null) {
            $parts[] = '--data ' . escapeshellarg((string)$options['body']);
        } elseif (!empty($options['multipart']) && is_array($options['multipart'])) {
            foreach ($options['multipart'] as $field) {
                $name = $field['name'] ?? '';
                if ($name === '') {
                    continue;
                }
                if (isset($field['filename'])) {
                    $parts[] = '-F ' . escapeshellarg($name . '=@' . $field['filename']);
                } elseif (isset($field['contents']) && (is_scalar($field['contents']) || $field['contents'] === null)) {
                    $parts[] = '-F ' . escapeshellarg($name . '=' . (string)$field['contents']);
                }
            }
        }

        return implode(' ', $parts);
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