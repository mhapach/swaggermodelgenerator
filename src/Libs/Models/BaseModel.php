<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 11.09.2018
 * Time: 12:54
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;

class BaseModel
{
    /** @var bool - Строгое соответсвие полям модели */
    protected $strict = true;
    /** @var array  - поля даты */
    protected $dates = [];

    /** @var array - mapping классов вида [поле из Soap => полный путь к классу] */
    protected $classMapping = [];

    public function toArray()
    {
        return (array)$this;
    }

    public function toJson()
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * BaseModel constructor.
     * @param \stdClass|array $attributes
     * @param array $classMapping
     */
    public function __construct($attributes, array $classMapping = [])
    {
        /**
         * Если у нас пустой класс мэппинг в текущем классе то передаем от класса предка,
         * те бывает так что у нас весь мэппинг определен в базовом классе
         * хотя лучше так не делать
         */
        if (!is_array($this->classMapping))
            $this->classMapping = [];

        if (!empty($classMapping))
            $this->classMapping = array_merge($this->classMapping, $classMapping);

        if (!empty($attributes) && is_array($attributes))
            $this->autoFill($attributes);
        else if (!empty($attributes) && $attributes instanceof \stdClass)
            $this->autoFill(get_object_vars($attributes));
    }

    /**
     * Автозаполнение полей создающегося объека по аналогии с Ларой через конструктор
     * @param array $attributes
     */
    protected function autoFill($attributes = array())
    {
        if (empty($attributes))
            return;

        foreach ($attributes as $name => $value) {
            $name = trim($name);
            $name = preg_replace('/\$/',"", $name);
            if ($this->strict && !property_exists($this, $name))
                continue;

            /** @var string $modelClassName  - Название модели наследнице класса BaseModel */
            $modelClassName = isset($this->classMapping[$name]) ? $this->classMapping[$name] : ucfirst($name);

            if ($modelClassName && class_exists($modelClassName) && !is_array($value))
                $this->$name = new $modelClassName($value, $this->classMapping);
            else if ($modelClassName && class_exists($modelClassName) && is_array($value)) {
                $values = $value;
                $valuesAsObjects = [];
                foreach ($values as $item) {
                    $valuesAsObjects[] = new $modelClassName($item, $this->classMapping);
                }
                $this->$name = collect($valuesAsObjects);
            } else {
                if (in_array($name, $this->dates) && !($value instanceof Carbon) && $value)
                    $value = new Carbon($value);

                $this->$name = $value;
            }
        }
        return;
    }
    /**
     * Чтобы срабатывали мутаторы Вида get{$snakeStyleName}Attribute
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        // name coming us with camel style thus we make em snake
        $snakeStyleName = Str::camel($name);
        $methodName = "get{$snakeStyleName}Attribute";

        //Для relation
        if (method_exists($this, $name)) {
            return $this->$name();
        }
        //Для мутаторов
        else if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }
        return null;
    }

}
