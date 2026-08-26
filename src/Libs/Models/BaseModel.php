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
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class BaseModel
{
    /** @var bool - Строгое соответсвие полям модели */
    protected $appends = [];

    protected $strict = true;

    /** @var array - поля даты */
    protected $dates = [];

    /** @var array - mapping классов вида [поле из Soap => полный путь к классу] */
    protected $classMapping = [];

    public function toArray()
    {
        return (array) $this;
    }

    public function toJson()
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * BaseModel constructor.
     *
     * @param  stdClass|array  $attributes
     *
     * @throws ReflectionException
     */
    public function __construct($attributes, array $classMapping = [])
    {
        /**
         * Если у нас пустой класс мэппинг в текущем классе то передаем от класса предка,
         * те бывает так что у нас весь мэппинг определен в базовом классе
         * хотя лучше так не делать
         */
        if (! empty($classMapping) /*&& empty($this->classMapping)*/) {
            //            $this->classMapping = $classMapping;
            $this->classMapping = array_merge($this->classMapping, $classMapping);
        }

        if (! empty($attributes) && is_array($attributes)) {
            $this->autoFill($attributes);
        } elseif (! empty($attributes) && ($attributes instanceof stdClass)) {
            $this->autoFill(get_object_vars($attributes));
        }

        if (is_array($this->appends)) {
            foreach ($this->appends as $appendField) {
                $this->$appendField = $this->$appendField;
            }
        }
    }

    /**
     * Автозаполнение полей создающегося объека по аналогии с Ларой через конструктор
     *
     * @throws ReflectionException
     */
    protected function autoFill(?array $attributes = []): void
    {
        if (empty($attributes)) {
            return;
        }

        $reflect = new ReflectionClass($this);

        foreach ($attributes as $name => $value) {
            $name = trim($name);
            $name = preg_replace('/\$/', '', $name);
            if ($this->strict && ! property_exists($this, $name)) {
                continue;
            }

            $prop = $reflect->getProperty($name);
            $modelClassName = $this->propClassName($prop);

            if ($modelClassName && class_exists($modelClassName) && ! self::isPropArray($prop)) {
                $this->$name = new $modelClassName($value, $this->classMapping);
            } elseif ($modelClassName && class_exists($modelClassName) && self::isPropArray($prop)) {
                $values = $value;
                $valuesAsObjects = [];
                foreach ($values as $item) {
                    $valuesAsObjects[] = new $modelClassName($item, $this->classMapping);
                }
                $this->$name = collect($valuesAsObjects);
            } else {
                if (in_array($name, $this->dates) && ! ($value instanceof Carbon) && $value) {
                    $value = new Carbon($value);
                }

                $this->$name = $value;
            }
        }
    }

    private function propClassName(ReflectionProperty $prop): ?string
    {
        $name = $prop->getName();
        /** @var string $modelClassName - Название модели наследнице класса BaseModel */
        $modelClassName = $this->classMapping[$name] ?? ucfirst($name);

        /**
         * пРОВЕРКА сделана для того, чтобы выяснить не является ли текущее имя property встроенным классом
         * например public $number - будет преобразовано в сласс Number а реально допустим оно string
         */
        if (class_exists($modelClassName)) {
            if (self::isPropScalar($prop)) {
                return null;
            }

            return $modelClassName;
        }

        return null;
    }

    /**
     * Чтобы срабатывали мутаторы Вида get{$snakeStyleName}Attribute
     *
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
        elseif (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return null;
    }

    public function isPropScalar(ReflectionProperty $prop): bool
    {
        if (self::isPropInteger($prop) || self::isPropFloat($prop) || self::isPropString($prop)) {
            return true;
        }

        return false;
    }

    private static function isPropInteger(ReflectionProperty $property): bool
    {
        if ($property->getType()?->getName() == 'int') {
            return true;
        }

        $description = $property->getDocComment();

        return str_contains(mb_strtolower($description), 'int');
    }

    private static function isPropString(ReflectionProperty $property): bool
    {
        if ($property->getType()?->getName() == 'string') {
            return true;
        }

        $description = $property->getDocComment();

        return str_contains(mb_strtolower($description), 'string');
    }

    private static function isPropFloat(ReflectionProperty $property): bool
    {
        if ($property->getType()?->getName() == 'float') {
            return true;
        }

        $description = $property->getDocComment();

        return str_contains(mb_strtolower($description), 'float');
    }

    private static function isPropDate(ReflectionProperty $prop): bool
    {
        if ($prop->getType()?->getName() == 'Date' || $prop->getType()->getName() == 'Carbon/Carbon') {
            return true;
        }

        $description = $prop->getDocComment();

        return str_contains(mb_strtolower($description), 'carbon');
    }

    public static function isPropArray(ReflectionProperty $prop): bool
    {
        if ($prop->getType()?->getName() == 'array') {
            return true;
        }

        $description = $prop->getDocComment();

        return str_contains(mb_strtolower($description), '[]') || str_contains(mb_strtolower($description), 'array');
    }

    public static function isPropMappedObject(ReflectionProperty $prop, ?array $classMapping = []): bool
    {
        $name = $prop->name;

        return isset($classMapping[$name]);
    }
}
