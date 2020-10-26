<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 30.03.2020
 * Time: 17:29
 */

namespace mhapach\SwaggerModelGenerator\Libs;


use Illuminate\Support\Facades\Cache;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\Swagger\Root as SwaggerRoot;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Root as OpenApi3Root;
use Exception;
use Symfony\Component\Yaml\Yaml;
use mhapach\SwaggerModelGenerator\Libs\Helpers\HttpHelper;

class SourceFactory
{
    /** @var string ссылка или yaml Контент */
    private $content;

    /** @var string */
    private $href;

    /**
     * SourceFactory constructor.
     * @param string $yamlOrHref
     */
    public function __construct(string $yamlOrHref)
    {
        if (strpos($yamlOrHref, 'http://') !== false || strpos($yamlOrHref, 'https://') !== false)
            $this->href = $yamlOrHref;
        else
            $this->content = $yamlOrHref;
    }

    /**
     * @return SwaggerRoot | OpenApi3Root
     * @throws Exception
     * @todo сделать обработку запроса к OpenApi 3
     */
    public function instance()
    {
        $this->_requestContent();

        if (!$this->content)
            throw new Exception("Response from yaml schema is empty");

        $parsedYaml = Yaml::parse($this->content, Yaml::PARSE_OBJECT_FOR_MAP);
        
        if (!empty($parsedYaml->swagger)) {
            return new SwaggerRoot($parsedYaml);
        } else if (!empty($parsedYaml->openapi)) {
            return new OpenApi3Root($parsedYaml);
        } else {
            throw new Exception("unsupported format of json: neither swagger nor openApi");
        }

    }

    /**
     * Получаем yaml
     * @throws Exception
     */
    private function _requestContent()
    {
        try {
            $this->content = HttpHelper::request($this->href);
            //$this->content = file_get_contents($this->href);
        } catch (Exception $e) {
            throw new Exception("Cant request {$this->href}\n" . $e->getMessage());
        }
        return $this->content;
    }
}