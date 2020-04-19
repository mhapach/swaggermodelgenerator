<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\src\Libs\Models\Entities;

use Illuminate\Support\Collection;

class MethodEntity extends BaseEntity implements IRenderable
{
    /** @var string */
//    public $type;
    /** @var string */
//    public $psrType;
    /** @var string */
//    public $format;
    /** @var string */
    public $ref;
    /** @var string */
    public $name;
    /** @var string */
    public $path;
    /** @var string */
    public $method;
    /** @var HintEntity */
    public $hint;
    /** @var string[] - returned content type */
    public $produces;
    
    /** @var string */
    public $serviceResponseType;
    
    /** @var MethodParamEntity[] | Collection*/
    public $params;

    /** @var PropertyEntity | Collection*/
    public $return;
    
    /**
     * Хотели использовать если бы методы формировались со списком параметров- переменнхыа не ассоциативным массивом
     * Но в этом случае есть опасность оишбки передачи параметров если изменится последовательность параметров
     * Пока не используется
     * @return string
     */
    public function renderParamsAsString()
    {
        $res = [];
        if ($this->params) {
            /** @var MethodParamEntity $methodParamEntity */
            foreach ($this->params as $methodParamEntity) {
                $str = "{$methodParamEntity->type} \${$methodParamEntity->name}";
                if (!$methodParamEntity->required)
                    $str .= " = null";
                $res[] = $str;
            }
        }
        return implode(", ", $res);
    }

    /**
     * Формируем описание для параметра $data в методе потому что в шаблоне неудобно
     * @return string
     */
    public function renderParamsDescription()
    {
        $str = "";
        if ($this->params) {
            $str = "[";
            /** @var MethodParamEntity $methodParamEntity */
            foreach ($this->params->sortByDesc('required') as $methodParamEntity) {
                $str .= "    @var {$methodParamEntity->hint->psrType} \${$methodParamEntity->name}";
                if (!$methodParamEntity->required)
                    $str .= " - required";
                $str .= "\n";
            }
            $str .= "]";
        }
        return $str;
    }

}