<?php

namespace mhapach\SwaggerModelGenerator;

use Illuminate\Support\Facades\Cache;
use mhapach\SwaggerModelGenerator\Libs\Converters\Swagger as SwaggerConverter;
use mhapach\SwaggerModelGenerator\Libs\Converters\OpenApi3 as OpenApi3Converter;
use mhapach\SwaggerModelGenerator\Libs\SourceFactory;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\Swagger\Root as SwaggerRoot;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Root as OpenApi3Root;

/*class SwaggerModelGenerator {
    public function test(){
        print view('mhapach::index', ['test' => 123])->render();
        print "111111111111111111";
    }
}*/

/**
 * Class SwaggerModelGenerator
 * @package mhapach\SwaggerModelGenerator
 */
class SwaggerModelGenerator
{
    /** @var string */
    protected $schemeUrl;

    /** @var SwaggerRoot | OpenApi3Root */
    public $sourceRoot;

    /** @var bool */
    public static $debug;

    public function __construct(string $schemeUrl, bool $debug = false)
    {
        $this->schemeUrl = $schemeUrl;
        self::$debug = $debug;
    }

    /**
     * @throws \Exception
     */
    public function initSourceRoot() {
        $sourceFactory = new SourceFactory($this->schemeUrl);

        if (self::$debug)
            $this->sourceRoot = Cache::get($this->schemeUrl);

        if (!$this->sourceRoot) {
//            try {
                $this->sourceRoot = $sourceFactory->instance();
                Cache::put($this->schemeUrl, $this->sourceRoot);
//            } catch (\Exception $e) {
//                dd("------- Exception ------", "FILE: ".__FILE__, "LINE: ".__LINE__, "MESSAGE: ".$e->getMessage());
//                die($e->getMessage());
//            }
        }
    }

    /**
     * @param string $modelsNs - models namespace
     * @param string|null $serviceNs - service namespace
     * @return OpenApi3Converter|SwaggerConverter|null
     * @throws \Exception
     */
    public function getConverterInstance(string $modelsNs, string $serviceNs = null)
    {
        $serviceNs = $serviceNs ?: $modelsNs;

        $this->initSourceRoot();
        if ($this->sourceRoot instanceof SwaggerRoot)
            return new SwaggerConverter($this->sourceRoot, $modelsNs, $serviceNs);
        elseif ($this->sourceRoot instanceof OpenApi3Root) {
            return new OpenApi3Converter($this->sourceRoot, $modelsNs, $serviceNs);
        }
        else
            return null;
    }
}