<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 23:35
 */

namespace mhapach\SwaggerModelGenerator\Libs;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

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

    /** @var \Closure - для расширения логов - должна возвращать строку */
    public $logClosure;
    
    /** @var Client */
    private $httpClient;
    /** @var string */
    private $method = 'get';    
    /** @var string - путь до файла в папке storage/app */
    private $logFileDefault = "logs/rest.log";
    /** @var string  */
    private $logFile = "";
    /** @var bool  */
    private $logEnabled = false;
    /** @var bool - tracing http requests */
    private $traceEnabled = false;
    /** @var array */
    private $traceLog = [];
    
    /** @var string */
    private $lastRequestedUrl; 

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
        
        $data['on_stats'] = function (TransferStats $stats) {
            $this->lastRequestedUrl = $stats->getEffectiveUri();
        };

        $result = null;
        try {
            if (!$this->httpClient)
                $this->initGuzzleClient();
            
            $result = $this->httpClient->request($method, $url, $data);
            $response = (string)$result->getBody();
        }
        catch (RequestException $e) {
            $this->errorMessage = urldecode($e->getMessage());
            $this->errorCode = $e->getCode();
            if ($e->getResponse()->getStatusCode() == 400)
                $this->errorMessage = urldecode($e->getResponse()->getBody()->getContents());
        }
        catch (Exception $e) {
            $this->errorCode = $e->getCode();
            $this->errorMessage = urldecode($e->getMessage());
        }

        if (!empty($errorMessage) || !empty($errorCode)) {
            Log::error("Error Code: $errorCode. Error message: $errorMessage");
            throw new Exception($errorMessage, $errorCode);
        }
                
        if ($this->logEnabled)
            $this->log();

        if (!empty($this->errorMessage))
            throw new \Exception($this->errorMessage, $this->errorCode);

        return $response;
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
            "Request method: " . $this->method . "\n" .
            "Data: " . json_encode($this->requestParams) . "\n" .
            "LastRequestedUrl: ". $this->lastRequestedUrl."\n".
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
    public function enableTrace()
    {
        $this->traceEnabled = true;
        $this->initGuzzleClient();
    }    
    
    public function disableTrace()
    {
        $this->traceEnabled = false;
        $this->initGuzzleClient();
    }

    /**
     * @return array : null - return last request trace
     */
    public function lastRequestTrace()
    {
        return is_array($this->traceLog) && !empty($this->traceLog) ? last($this->traceLog) : null;       
    }

    /**
     * @return array : null - return all requests trace
     */
    public function allRequestTrace()
    {
        return is_array($this->traceLog) && !empty($this->traceLog) ? $this->traceLog : null;       
    }


    private function initGuzzleClient()
    {
        $clientParams = [
            'verify' => false
        ];

        if ($this->traceEnabled) {
            $history = Middleware::history($this->traceLog);
            $stack = HandlerStack::create();
            // Add the history middleware to the handler stack.
            $stack->push($history);
            $clientParams['handler'] = $stack;
        }

        $this->httpClient = new Client($clientParams);
    }    
}
