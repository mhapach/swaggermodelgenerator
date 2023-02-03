<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 23:35
 */

namespace mhapach\SwaggerModelGenerator\Libs;

use Carbon\Carbon;
use Carbon\Traits\Creator;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use mhapach\SwaggerModelGenerator\Libs\Helpers\DataHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

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

    public LoggerInterface $logger;

    private string $app = "";

    private bool $logEnabled = false;

    private bool $traceEnabled = false;

    private array $traceLog = [];

    /** @var string */
    private $lastRequestedUrl;

    /** @var ResponseInterface */
    public $lastRequestResult;

    /** @var string */
    public string $errorMessage = "";
    public string $errorCode = "";

    public function __construct(?LoggerInterface $logger = null)
    {
        if (!$logger)
            $this->logger = Log::build(config('logging.channels.' . config('logging.default')));
        else
            $this->logger = $logger;
    }

    /**
     * @param $url
     * @param string $method
     * @param array $data
     * @return string | null
     * @throws Exception|GuzzleException
     */
    public function request($url, array $data = [], string $method = 'get'): string|null
    {
        $this->url = $url;
        $this->method = strtolower($method);
        $this->requestParams = $data;
        $this->requestDate = new Carbon();

        $this->response = null;

        $data['on_stats'] = function (TransferStats $stats) {
            $this->lastRequestedUrl = $stats->getEffectiveUri();
        };

        try {
            if (!$this->httpClient)
                $this->initGuzzleClient();
            $this->lastRequestResult = $this->httpClient->request($method, $url, $data);
            $this->response = (string)$this->lastRequestResult->getBody();
        } catch (RequestException $e) {
            $this->errorMessage = urldecode($e->getMessage());
            $this->errorCode = $e->getCode();
            if ($e->hasResponse() && $e->getResponse()?->getStatusCode() == 400)
                $this->errorMessage = urldecode($e->getResponse()->getBody()->getContents());
        } catch (Exception $e) {
            $this->errorCode = $e->getCode();
            $this->errorMessage = urldecode($e->getMessage());
        }

        if ($this->logEnabled)
            $this->log();

        if (!empty($this->errorMessage) || !empty($errorCode))
            throw new Exception($this->errorMessage, $this->errorCode);

        return $this->response;
    }

    /**
     * Логируем запросы
     */
    private function log(): void
    {
        if (!$this->logger || !$this->logEnabled)
            return;

        $context = [
            "response status" => $this->lastRequestResult?->getStatusCode() ?: $this->errorCode,
            "time start" => $this->requestDate->toDateTimeString(),
            "time end" => (new Carbon())->toDateTimeString(),
            "request method" => $this->method,
//            "Request address" => $this->url,
            "request url" => $this->lastRequestedUrl,
            "errors" => $this->errorMessage,
            "data" => $this->requestParams,
            "response body" => DataHelper::isJson($this->response) ?
                json_decode($this->response, JSON_UNESCAPED_UNICODE) : $this->response,
        ];

        if (!app()->runningInConsole())
            $context = array_merge($context, [
                "route name" => (request()->route()->getName()),
                "route action" => request()->route()->getActionName(),
                "referer (REQUEST_URI)" => getenv('REQUEST_URI'),
                "user agent" => getenv('HTTP_USER_AGENT'),
                "ip address" => (request()->ip() ?? 'UNKNOWN'),
                "url params" => request()->all(),
            ]);

        if ($this->app)
            $context['app'] = $this->app;

//        ksort($context);
        if (!$this->errorCode)
            $this->logger->info('ok', $context);
        else
            $this->logger->error($this->errorMessage, $context);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setApp(string $name)
    {
        $this->app = $name;
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
