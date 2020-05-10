<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Entities;

/**
 * Class HintEntity
 * @package mhapach\SwaggerModelGenerator\Libs\Models\Entities
 */
class HintEntity extends BaseEntity
{
    /** @var string - its a psrType of Models\Property */
    public $type;
    /** @var string - its a psrType of Models\Property */
    public $psrType;
    /** @var string */
    public $description;
    /** @var string - */
    public $title;
    /** @var string */
    public $format;

}