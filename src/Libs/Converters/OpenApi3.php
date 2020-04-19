<?php


namespace mhapach\SwaggerModelGenerator\Libs\Converters;

use mhapach\SwaggerModelGenerator\Libs\Models\OpenApi3\Root;

class OpenApi3 extends BaseConverter
{
    /** @var Root */
    public $sourceRoot;
    /** @var string  */
    public $debugDefinitionName;
    /** @var string  */
    public  $debugPath;

    public function __construct(Root $sourceRoot, string $modelsNs, string $serviceNs = null)
    {
        $this->sourceRoot = $sourceRoot;
        $this->modelsNs = $modelsNs;
        $this->serviceNs = $serviceNs ?: $modelsNs;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function genModels(string $path)
    {
    }

    /**
     * @param string $ns
     * @param string $path
     * @throws \Exception
     */
    public function genService(string $path) {

    }
}