<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Entities;

use Illuminate\Support\Collection;

/**
 * Class ClassEntity
 * @package mhapach\SwaggerModelGenerator\Libs\Models\Entities
 */
class ClassEntity extends BaseEntity implements IRenderable
{
    /** @var string */
    public $extends;
    /** @var string */
    public $implements;
    /** @var string */
    public $ns;
    /** @var string */
    public $name;
    /** @var HintEntity */
    public $hint;
    /** @var MethodEntity[] | Collection */
    public $methods;
    /** @var PropertyEntity[] | Collection */
    public $properties;
    /** @var string[] | Collection - list of modules and classes with namespaces */
    public $includedClasses; 

    /**
     * get classMapping
     * @return array
     */
    public function getClassMapping()
    {
        $res = [];

        /** @var PropertyEntity $prop */
        if ($this->properties)
            foreach ($this->properties as $prop) if ($prop->ref) {
                /** @var string $refClassName */
                $refClassName = last(explode('/', $prop->ref));
                $res[$prop->name] = $refClassName;
            }
        return $res;
    }

    /**
     * get dates array
     * @return array
     */
    public function getDates()
    {
        $res = [];
        if ($this->properties)
            foreach ($this->properties as $propertyEntity) if ($propertyEntity->format == 'date' || $propertyEntity->format == 'date-time')
                $res[] = $propertyEntity->name;
        return $res;
    }
}