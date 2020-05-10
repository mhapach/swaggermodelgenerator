<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 23:35
 */

namespace mhapach\SwaggerModelGenerator\Libs;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use mhapach\SwaggerModelGenerator\Libs\Helpers\HttpHelper;

class BaseService
{

    /** @var string */
    public $login;

    /** @var string */
    public $password;

    /** @var string */
    public $url;

    /** @var array - последний запрос */
    public $requestParams;

    /** @var Carbon - время последнего запроса */
    public $requestDate;

    /** @var string - последний ответ */
    public $response;

    /** @var string */
    private $method = 'get';

    /** @var string */
    public $errorMessage;
    public $errorCode;

    /**
     * @param $url
     * @param string $method
     * @param array $data
     * @return string | null
     * @throws \Exception
     */
    public function request($url, array $data = [], $method = 'get')
    {
        $this->url = $url;
        $this->method = strtolower($method);
        $this->requestParams = $data;
        $this->requestDate = new Carbon();

        $this->response = null;

        $result = null;
        try {
            $this->response = HttpHelper::request($url, $data, $method);
/*            if ($this->method == 'get')
                $this->response = HttpHelper::request($url, ['query' => HttpHelper::stringifyRequestParams($data)], $method);
            else{
                $this->response = HttpHelper::request($url, ['query' => HttpHelper::encodeRequestParams($data)], $method);
            }*/
        } catch (\Exception $e) {
            $this->errorMessage = urldecode($e->getMessage());
            $this->errorCode = $e->getCode();
        }

        $this->log();

        if (!empty($this->errorMessage))
            throw new \Exception($this->errorMessage, $this->errorCode);

        return $this->response;
    }

    /**
     * Логируем запросы
     * @param string $fileName
     * @throws \Exception
     */
    private function log($fileName = "rest/http_request.log")
    {
        $content = "--- BEGIN ---\n" .
            "Адрес: " . $this->url . "\n" .
            "Данные: " . json_encode($this->requestParams) . "\n" .
            "Время запроса: " . $this->requestDate->toDateTimeString() . "\n" .
            "Время ответа: " . (new Carbon())->toDateTimeString() . "\n" .
            "Ответ: " . $this->response . "\n" .
            "Ошибки: " . $this->errorCode." - ". $this->errorMessage . "\n" .
            "--- /END ---\n";

            //Storage::disk()->append($fileName, $content); //This shit makes out of memory error
        file_put_contents(Storage::disk('local')->path($fileName), $content, FILE_APPEND);            
    }
}
