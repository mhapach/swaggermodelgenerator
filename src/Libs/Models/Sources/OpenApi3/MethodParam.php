<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 13:03
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3;

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
    
    public function __construct($attributes)
    {
        parent::__construct($attributes);
        if (isset($attributes->schema) && isset($attributes->schema->type))
            $this->type = $attributes->schema->type;
    }
}