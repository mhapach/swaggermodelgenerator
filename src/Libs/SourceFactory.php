<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 30.03.2020
 * Time: 17:29
 */

namespace mhapach\SwaggerModelGenerator\src\Libs;


use Illuminate\Support\Facades\Cache;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Swagger\Root as SwaggerRoot;
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
     * @return SwaggerRoot
     * @todo сделать обработку запроса к OpenApi 3
     * @throws Exception
     */
    public function instance()
    {
        $this->_requestContent();

        if (!$this->content)
            throw new Exception("Response from yaml schema is empty");

        $parsedYaml = Yaml::parse($this->content);

        return new SwaggerRoot($parsedYaml);
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
        }
        catch(Exception $e){
            throw new Exception("Cant request {$this->href}\n".$e->getMessage());
        }
        return $this->content;
    }
}