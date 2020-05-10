<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Entities;

class MethodParamEntity extends PropertyEntity implements IRenderable
{
    /** @var bool */
    public $required;
    /** @var string */
    public $xExample;
    /** @var string - cgi-param wrapper inside which will json data */
    public $in;
    /** @var bool */
    public $allowEmptyValue = false;
}