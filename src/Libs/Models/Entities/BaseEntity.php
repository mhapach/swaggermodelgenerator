<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Entities;

use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;

/**
 * Class Entity
 * @package mhapach\SwaggerModelGenerator\Libs\Models
 */
abstract class BaseEntity extends BaseModel
{
    public function __construct($attributes = null)
    {
        parent::__construct($attributes);
    }
}