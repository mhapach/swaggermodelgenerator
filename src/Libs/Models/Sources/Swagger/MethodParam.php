<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 13:03
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\Swagger;

use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;

class MethodParam extends Property
{
    /** @var string */
    public $in;
    /** @var bool */
    public $required;
    /** @var bool */
    public $allowEmptyValue;
    /** @var string */
    public $xExample;
}