<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Entities;

class MethodReturnEntity extends PropertyEntity implements IRenderable
{
    /** @var string - text/plain or application/json */
    public $contentType;
    /** @var string */
    public $responseCode;
}