<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 30.03.2020
 * Time: 17:29
 */

namespace mhapach\SwaggerModelGenerator\src\Libs\Models\Swagger;

use mhapach\SwaggerModelGenerator\src\Libs\Models\BaseModel;
use Illuminate\Support\Collection;

class Root extends BaseModel
{
    /** @var string */
    public $swagger;
    /** @var string[] | Collection */
    public $info;
    /** @var string */
    public $host;
    /** @var string */
    public $basePath;
    /** @var array | Collection */
    public $tags;
    /** @var Method[] | Collection */
    public $paths;
    /** @var Definition[] | Collection */
    public $definitions;

//    protected $classMapping = [
//        'paths' => Method::class,
//        'definitions' => Definition::class,
//    ];

    public function __construct($attributes, array $classMapping = [])
    {
        parent::__construct($attributes, $classMapping);
        $this->initDefinitions();
        $this->initPaths();
    }

    private function initDefinitions()
    {
        $res = null;
        /** @var array $definition */
        foreach ($this->definitions as $definitionName => $definition) {

            $class = new Definition($definition);
            $class->name = $definitionName;
            if (!empty($definition['properties']))
                $class->properties = $this->_properties($definition['properties']);
            $res[] = $class;
        }
        $this->definitions = $res ? collect($res) : null;
    }

    public function initPaths()
    {
        /** @var array $definition */
        $res = null;
        /** @var array $prop */
        foreach ($this->paths as $path => $yamlMethods) {
            foreach ($yamlMethods as $method => $props) {
                $props['method'] = $method;
                $props['path'] = $path;
                $res[] = new Method($props);
            }
        }

        return $this->paths = $res ? collect($res) : null;
    }


    /**
     * @return Collection | Property[] | null
     */
    private function _properties(array $definitionProperties) {
        /** @var array $definition */
        $properties = null;
        /** @var array $prop */
        foreach ($definitionProperties as $propName => $prop) {
            $prop['name'] = $propName;
            $properties[] = new Property($prop);
        }

        if (!empty($properties))
            collect($properties);

        return $properties;
    }

}