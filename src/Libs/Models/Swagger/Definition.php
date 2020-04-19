<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 30.03.2020
 * Time: 17:29
 */

namespace mhapach\SwaggerModelGenerator\src\Libs\Models\Swagger;

use mhapach\SwaggerModelGenerator\src\Libs\Models\BaseModel;

class Definition extends BaseModel
{
    /** @var string */
    public $type;
    /** @var string */
    public $name;
    /** @var string */
    public $title;
    /** @var string */
    public $description;
    /** @var Property */
    public $properties;
}