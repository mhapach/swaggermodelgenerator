<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 19:05
 */

namespace mhapach\SwaggerModelGenerator\Libs\Converters\OpenApi3;

use Carbon\Carbon;
use mhapach\SwaggerModelGenerator\Libs\BaseService;
use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\HintEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\PropertyEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Component;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Property;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Root;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Types\Compound;


class EntityConverter
{
    /** @var Root */
    private $sourceRoot;
    private $ns = '';
    private $extends = "BaseModel";
    private $implements = "";

    /** @var Collection | ClassEntity[] */
    private $convertedEntities;

    /**
     * Swagger constructor.
     * @param Root $sourceRoot
     * @param string $ns
     * @param string $extends
     * @param string $implements
     */
    public function __construct(Root $sourceRoot, string $ns = null, string $extends = "BaseModel", string $implements = null)
    {
        $this->sourceRoot = $sourceRoot;
        $this->ns = $ns;
        $this->extends = $extends;
        $this->implements = $implements;
    }

    /**
     * @param Property[] | Collection $definition
     * @return Collection | null
     */
    private function getConvertedProperties($definitionProperties)
    {
        if (!$definitionProperties)
            return null;

        $res = null;
        /** @var Property $sourceProperty */
        foreach ($definitionProperties as $sourceProperty) {
            $property = new PropertyEntity($sourceProperty->toArray());
            $property->hint = new HintEntity($sourceProperty->toArray());
            $res[] = $property;
        }
        return $res ? collect($res) : null;
    }

    /**
     * @return ClassEntity[]
     */
    public function get(string $definitionName = null)
    {
        $res = null;
        /** @var Component $definition */
        foreach ($this->sourceRoot->components as $definition) if ($definition->type == 'object') {
            $entityClass = new ClassEntity([
                'name' => $definition->name,
                'ns' => $this->ns,
                'extends' => $definition->extends ?: $this->extends,
                'implements' => $this->implements,
                'hint' => new HintEntity($definition->toArray()),
                'properties' => $this->getConvertedProperties($definition->properties),
                'includedClasses' => [BaseModel::class, Carbon::class]
            ]);
            $res[] = $entityClass;
        }
        $this->convertedEntities = collect($res);

        /** @var ClassEntity $entityClass */
        foreach ($this->convertedEntities as $entityClass) if ($entityClass->extends != $this->extends) {
            $entityClass->classMapping = $this->getParentClassMappings($entityClass);
            $entityClass->dates = $this->getParentClassDates($entityClass);
        }
        return $res;
    }

    /**
     * @param ClassEntity $class
     * @return array
     */
    private function getParentClassMappings(ClassEntity $class)
    {
        if (!$class->extends)
            return [];

        $mapping = [];
        /** @var ClassEntity $class */
        while ($class) {
            $mapping = array_merge($mapping, $class->classMapping ?? []);
            $class = $this->convertedEntities->where('name', $class->extends)->first();
        }
        return $mapping;
    }
    /**
     * @param ClassEntity $class
     * @return array
     */
    private function getParentClassDates(ClassEntity $class)
    {
        if (!$class->extends)
            return [];

        $dates = [];
        /** @var ClassEntity $class */
        while ($class) {
            $dates = array_merge($dates, $class->dates ?? []);
            $class = $this->convertedEntities->where('name', $class->extends)->first();
        }
        return $dates;
    }
}
