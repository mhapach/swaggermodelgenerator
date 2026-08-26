<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 03.04.2020
 * Time: 15:23
 * Class maker
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Entities;

use Illuminate\Support\Collection;

/**
 * Class MethodEntity
 * @package mhapach\SwaggerModelGenerator\Libs\Models\Entities
 * @property string $psrType
 * @property string $refType
 * @property string $type
 * @property string $ref
 * @property string $format
 */
class MethodEntity extends BaseEntity implements IRenderable
{
    /** @var string */
    public $name;
//    /** @var string */
//    public $psrType;
//    /** @var string */
//    public $refType;
//    /** @var string */
//    public $type;
//    /** @var string */
//    public $ref;
//    /** @var string */
//    public $format;
    /** @var string */
    public $path;
    /** @var string */
    public $method;
    /** @var HintEntity */
    public $hint;
    /** @var string[] - returned content type - for openApi 2.0*/
    public $produces;
    
    /** @var string */
    public $serviceResponseType;
    
    /** @var MethodParamEntity*/
    public $requestBody;
    /** @var MethodParamEntity[] | Collection*/
    public $params;

    /** @var MethodReturnEntity[] | Collection*/
    public $return;

    /** @return string|null */
    public function getPsrTypeAttribute()
    {
        /** @var MethodReturnEntity $return200 */
        $return200 = $this->getSuccessReturn();
        return $return200 ? $return200->psrType : null;

//        if (!$this->return)
//            return null;
//        
//        $res = [];
//        /** @var MethodReturnEntity $return */
//        foreach($this->return as $return) {
//            $res[]=$return->psrType;
//        }
//        return implode(' | ', $res); 
    }
    
    /** @return string|null */
    public function getRefTypeAttribute()
    {
        /** @var MethodReturnEntity $return200 */
        $return200 = $this->getSuccessReturn();
        return $return200 ? $return200->refType : null;
    }
        
    /** @return string|null */
    public function getTypeAttribute()
    {
        /** @var MethodReturnEntity $return200 */
        $return200 = $this->getSuccessReturn();
        return $return200 ? $return200->type : null;
    }        

    /** @return string|null */
    public function getRefAttribute()
    {
        /** @var MethodReturnEntity $return200 */
        $return200 = $this->getSuccessReturn();
        return $return200 ? $return200->ref : null;            
    }
    
    /** @return string|null */
    public function getFormatAttribute()
    {
        /** @var MethodReturnEntity $return200 */
        $return200 = $this->getSuccessReturn();
        return $return200 ? $return200->format : null;
    }

    /**
     * @return MethodReturnEntity | null
     */
    private function getSuccessReturn(){
        if (!$this->return)
            return null;

        $res = null;
        /** @var MethodReturnEntity $return200 */
        return $this->return->where('responseCode', 200)->first();
    }

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