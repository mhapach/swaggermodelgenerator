<?php


namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3;

use mhapach\SwaggerModelGenerator\Libs\Helpers\ParseHelper;
use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;

class Property extends BaseModel
{
    /** @var string - */
    public $type;
    /** @var string - required */
    public $name;
    /** @var string */
    public $format;
    /** @var string */
    public $description;
    /** @var string[] */
    public $enum;
    /** @var array - assoc array value = {"type|$ref" => string} */
    public $items;
    /** @var string */
    public $default;
    /** @var string */
    public $minimum;
    /** @var string */
    public $maximum;

    /** Dynamic properties */
    /** @var string - */
    public $psrType;
    /** @var string - */
    public $ref;
    /** @var string */
    public $refType;

    public function __construct($attributes)
    {
//        if (isset($attributes->name) || $this->name) {
        $this->_initAttrByRef($attributes);
        parent::__construct($attributes);
        $this->init();
//        }
    }

    public function init()
    {
        if ($this->type == 'array' && isset($this->items->type)) {
            $this->psrType = $this->items->type . "[]";
        } elseif ($this->type == 'array' && isset($this->items->{'$ref'})) {
            $this->refType = ParseHelper::getSafeClassName(last(explode('/', $this->items->{'$ref'})));
            $this->psrType = $this->refType . "[]";
            $this->ref = $this->items->{'$ref'};
        } elseif (in_array($this->format, ['date', 'date-time'])) {
            $this->psrType = "Carbon";
        } elseif (!empty($this->ref)) {
            $this->refType = ParseHelper::getSafeClassName(last(explode('/', $this->ref)));
            $this->psrType = $this->refType;
        } else
            $this->psrType = $this->type;

    }

    /**
     * @param array $attributes
     */
    protected function _initAttrByRef(&$attributes)
    {
        $refName = null;
        if (isset($attributes->{'$ref'}))
            $refName = $attributes->{'$ref'};
        elseif (isset($attributes->type) && $attributes->type == 'array' && isset($attributes->items->{'$ref'}))
            $refName = $attributes->items->{'$ref'};

        if ($refName) {
            $refName = last(explode('/', $refName));
            $definition = $this->_getDefinitionByRef($refName);

//            dump('in property', $definition);

            if ($definition) {
                if (isset($definition->allOf))
                    $definition = $definition->allOf[1];

                if ($definition->type != 'object') {
                    $definition->name = $attributes->name ?? null;
                    $attributes = $definition;
                    if (!empty($attributes->enum))
                        $attributes->description = " enum values: " . implode(" | ", $attributes->enum);
                } else
                    $attributes->type = $attributes->type ?? 'object';
            }
        }
    }

    /**
     * @param string $name
     * @return array | null
     */
    protected function _getDefinitionByRef(string $name)
    {
        if (empty(Root::$parsedYaml->components->schemas->$name))
            return null;

        $schema = clone Root::$parsedYaml->components->schemas->$name;
        return $schema;
    }


}