<?php


namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3;

use Illuminate\Support\Collection;
use mhapach\SwaggerModelGenerator\Libs\Helpers\ParseHelper;
use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;

class Root extends BaseModel
{
    /** @var object */
    public static $parsedYaml;
    /** @var array */
    public $info;
    /** @var array */
    public $tags;
    /** @var array */
    public $servers;
    /** @var string[] */
    public $externalDocs;
    /** @var Method[] | Collection - methods */
    public $paths;
    /** @var Component[] | Collection - components */
    public $components;

    public function __construct(object $parsedYaml)
    {
        parent::__construct($parsedYaml);
        self::$parsedYaml = $parsedYaml;
        $this->initComponents();
        $this->initPaths();
    }

    public function initComponents()
    {
        $components = null;
        foreach ($this->components->schemas as $name => $value)/* if ($name == 'PackageRequest') */{
            $value->name = ParseHelper::getSafeClassName($name);
            $extendsClassName = null;
            if (!empty($value->allOf)) {
                $extendsClassName = last(explode("/", $value->allOf[0]->{'$ref'}));

                if (isset($value->allOf[1])) {

                    if(!empty($value->allOf[1]->properties))
                        $value->properties = $value->allOf[1]->properties;

                    if(!empty($value->allOf[1]->type) && $value->allOf[1]->type == 'object') //если ссылка объект то меньше чем объект текущий элемент быть не может
                        $value->type = 'object';
                }

            }

            /** @var Component $schema */
            $schema = new Component($value);
//            dump('in root', $value->properties);
            if (!empty($value->properties))
                $schema->properties = $this->_properties($value->properties);
            $schema->extends = $extendsClassName;

            $components[] = $schema;
        }

        if ($components)
            $this->components = collect($components);
    }

    /**
     * @param object | array $schemaProperties
     * @return array|Collection|null
     */
    private function _properties($schemaProperties)
    {
        /** @var array $definition */
        $properties = null;
        /** @var array $prop */
        foreach ($schemaProperties as $propName => $prop) /*if ($propName == 'instruments')*/ {
            $prop->name = $propName;
            $property = new Property($prop);
            $properties[] = $property;
        }

        if (!empty($properties))
            $properties = collect($properties);

        return $properties;
    }

    /**
     * Methods
     * @return Collection|null
     */
    public function initPaths()
    {
        /** @var array $definition */
        $res = null;

        /** @var array $prop */
        foreach ($this->paths as $path => $yamlMethods) /*if ($path == '/sandbox/currencies/balance')*/ {
            foreach ($yamlMethods as $method => $props) {
                $props->method = $method;
                $props->path = $path;

                $res[] = new Method($props);
            }
        }

        return $this->paths = $res ? collect($res) : null;
    }

}