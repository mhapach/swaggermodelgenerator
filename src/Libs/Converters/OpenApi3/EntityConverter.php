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


class EntityConverter
{
    /** @var Root */
    private $sourceRoot;
    private $ns = '';
    private $extends = "BaseModel";
    private $implements = "";

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
        foreach ($this->sourceRoot->components as $definition) if ($definition->type == 'object' && (!$definitionName || $definitionName == $definition->name)) {
            $entityClass = new ClassEntity([
                'name' => $definition->name,
                'ns' => $this->ns,
                'extends' => $this->extends,
                'implements' => $this->implements,
                'hint' => new HintEntity($definition->toArray()),
                'properties' => $this->getConvertedProperties($definition->properties),
                'includedClasses' => [BaseModel::class, Carbon::class]
            ]);
            $res []= $entityClass;
        }
        return $res;
    }
}