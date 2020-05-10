<?php


namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3;

use Illuminate\Support\Collection;
use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;

/**
 * Class Component
 * @package mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3
 * @property string $name
 */
class Component extends BaseModel
{
    /** @var string */
    public $type;

    /** @var string */
    public $name;
 
    /** @var string[] */
    public $enum; 
    
    /** @var string[] */
    public $required;
    
    /** @var Property[] | Collection */
    public $properties;
    
    public $classMapping = [
        'properties' => Property::class
    ];
}