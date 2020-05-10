<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 13:03
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3;

class MethodParamRequestBody extends MethodParam
{
    /** @var string */
    public $in = 'body';
    /** @var string */
    public $type = 'string';
    /** @var string  */
    public $name = 'body';
    
    public function __construct($attributes)
    {
        if (isset($attributes->content->{'application/json'}) && isset($attributes->content->{'application/json'}->schema)) {
            $oldAttributes = $attributes;
            $attributes = $oldAttributes->content->{'application/json'}->schema;
            $attributes->required = $oldAttributes->required;
            $attributes->description = isset($oldAttributes->description) ? $oldAttributes->description : '';

            if (isset($attributes->{'$ref'})) {
                $attributes->description = json_encode($this->parseRef($attributes->{'$ref'}), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        }
        parent::__construct($attributes);
    }

    protected function parseRef($ref) {
        $defName = last(explode('/', $ref));
        $definition = $this->_getDefinitionByRef($defName);
        /** @var string[] $required - fields in body */
        $required = isset($definition->required) ? $definition->required : [];
        
        $res = [];
        if (isset($definition->properties)) {
            /**
             * @var string $name
             * @var array $property
             */
            foreach($definition->properties as $name => $property) {
                $ref = '';
                if (isset($property->{'$ref'}))
                    $ref = $property->{'$ref'};
                elseif (isset($property->type) && $property->type == 'array' && isset($property->items->{'$ref'}))
                    $ref = $property->items->{'$ref'};
                
                if ($ref) {
                    $res[$name] = $this->parseRef($ref);
                    if (!is_array($res[$name]) && in_array($name, $required)) 
                        $res[$name] .= ' - required';
                }
                else 
                    $res[$name] = $this->getPropertyDescription($property) . (in_array($name, $required) ? ' - required' : '');                
            }
        }
        else
            return $this->getPropertyDescription($definition);
        
        return $res;
    }

    protected function getPropertyDescription($property){
        $str = "";
        if (isset($property->type))
            $str.="$property->type";
        if (!empty($property->enum))
            $str.=" - enum values: [".implode(" | ", $property->enum)."]";
        if (isset($property->description))
            $str.=". $property->description";
        if (isset($property->format))
            $str.=". $property->format";

        return $str;
    }
}