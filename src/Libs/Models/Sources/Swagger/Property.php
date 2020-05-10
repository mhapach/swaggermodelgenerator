<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 30.03.2020
 * Time: 17:29
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\Swagger;

use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;

/**
 * Class Property
 * @package mhapach\SwaggerModelGenerator\Libs\Models
 */
class Property extends BaseModel
{
    /** @var string - */
    public $type;
    /** @var string - */
    public $psrType;
    /** @var string - */
    public $name;
    /** @var string */
    public $format;
    /** @var string */
    public $description;
    /** @var string[] */
    public $enum;
    /** @var \stdClass - assoc array value = {"type|$ref" => string}*/
    public $items;
    /** @var string */
    public $ref;
    /** @var string */
    public $refType;

    public function __construct($attributes)
    {
        parent::__construct($attributes);

        $this->init();
    }

    public function init() 
    {
        if ($this->type == 'array' && isset($this->items->type)) {
            $this->psrType = $this->items->type."[]";
        }
        elseif ($this->type == 'array' && isset($this->items->{'$ref'})) {
            $this->refType = last(explode('/', $this->items->{'$ref'}));
            $this->psrType = $this->refType."[]";
            $this->ref = $this->items->{'$ref'};
        }
        elseif (in_array( $this->format, ['date', 'date-time'])) {
            $this->psrType = "Carbon";
        }
        elseif (!empty($this->ref)){
            $this->refType = last(explode('/', $this->ref));
            $this->psrType = $this->refType;
        }
        else
            $this->psrType = $this->type;
    }
}