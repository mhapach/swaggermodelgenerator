<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\src\Libs\Models\Entities;

/**
 * @property string $psrType
 * Class PropertyEntity
 * @package mhapach\SwaggerModelGenerator\src\Libs\Models\Entities
 */
class PropertyEntity extends BaseEntity implements IRenderable
{
    /** @var string */
    public $type;
    /** @var string */
    public $psrType;
    /** @var string */
    public $refType;
    /** @var string */
    public $format;
    /** @var string */
    public $ref;
    /** @var string */
    public $name;
    /** @var HintEntity */
    public $hint;

}