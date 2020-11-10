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
use Illuminate\Support\Facades\File;
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

    /** @var \Closure - для расширения логов должна возвращать строку */
    public $logClosure;
    /** @var string - путь до файла в папке storage/app */
    private $logFileDefault = "logs/rest.log";
    /** @var string  */
    private $logFile = "";
    /** @var bool  */
    private $logEnabled = false;

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

        if ($this->logEnabled)
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
    private function log()
    {
        $fileName = storage_path($this->logFileDefault);
        if ($this->logFile) {
            $fileName = $this->logFile;
            $dirName = dirname($fileName);
            if ($dirName && !file_exists($dirName))
                if (!File::makeDirectory($dirName))
                    throw new \Exception("Log file creation error. Check your access rights"); 
        }        
 
        $extraLog = "";
        if ($this->logClosure)
            $extraLog = call_user_func($this->logClosure);
        
        $content = "";
        if (!app()->runningInConsole())
            $content = "--- BEGIN ---\n" .
                "User Agent: " . getenv('HTTP_USER_AGENT') . "\n" .
                "IP address: " . (request()->ip() ?? 'UNKNOWN') . "\n" .
                "Route name: " . (request()->route()->getName()) . "\n" .
                "Route action: " . request()->route()->getActionName() . "\n" .
                "Refer (REQUEST_URI): " . getenv('REQUEST_URI') . "\n" .
                "CGI params: " . json_encode(request()->all(), JSON_UNESCAPED_UNICODE) . "\n";

        $content = $content .
            "Request address: " . $this->url . "\n" .
            "Data: " . json_encode($this->requestParams) . "\n" .
            "Response: " . $this->response . "\n" .
            "Errors: " . $this->errorMessage . "\n" .
            "Start time: " . $this->requestDate->toDateTimeString() . "\n" .
            "End time: " . (new Carbon())->toDateTimeString() . "\n" .
            $extraLog .
            "\n--- /END ---\n";

        //Storage::disk()->append($fileName, $content); //This shit makes out of memory error
        file_put_contents($fileName, $content, FILE_APPEND);
    }

    /**
     * @param \Closure $closure -  замыкание расширящее стандартный лог возвращает строку
     */
    public function setLogClosure(\Closure $closure)
    {
        $this->logClosure = $closure;
    }

    /**
     * @param string $logFile - путь к файлу
     */
    public function setLogFile(string $logFile)
    {
        $this->logFile = $logFile;
    }
    
    public function enableLog()
    {
        $this->logEnabled = true;
    }    
    
    public function disableLog()
    {
        $this->logEnabled = false;
    }
}
