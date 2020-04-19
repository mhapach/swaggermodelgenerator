<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\src\Libs\Models\Entities;

use mhapach\SwaggerModelGenerator\src\Libs\Models\BaseModel;

/**
 * Class Entity
 * @package mhapach\SwaggerModelGenerator\src\Libs\Models
 */
abstract class BaseEntity extends BaseModel
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}