<?php


namespace mhapach\SwaggerModelGenerator\Libs\Converters;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use mhapach\SwaggerModelGenerator\Libs\Converters\OpenApi3\ServiceConverter;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Root;
use mhapach\SwaggerModelGenerator\Libs\Converters\OpenApi3\EntityConverter;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity;

class OpenApi3 extends BaseConverter
{
    /** @var string - is used in class generator template */
    public $modelsNs;
    /** @var string */
    public $serviceNs;

    /** @var Root */
    public $sourceRoot;
    /** @var string  */
    public $debugDefinitionName;
    /** @var string  */
    public  $debugPath;

    public function __construct(Root $sourceRoot, string $modelsNs, string $serviceNs = null)
    {
        $this->sourceRoot = $sourceRoot;
        $this->modelsNs = $modelsNs;
        $this->serviceNs = $serviceNs ?: $modelsNs;
    }

    /**
     * @throws \Exception
     */
    public function genModels(string $path)
    {
        /** @var EntityConverter $swaggerEntityConverter */
        $swaggerEntityConverter = new EntityConverter($this->sourceRoot, $this->modelsNs);
        /** @var Collection | ClassEntity[] $entities */
        $entities = $swaggerEntityConverter->get($this->debugDefinitionName);
        if ($entities) {

            if (!File::exists($path))
                File::makeDirectory($path,0775,true,false);

            /** @var ClassEntity $entity */
            foreach ($entities as $entity)
                File::put($path . "/{$entity->name}.php", $this->renderModel($entity));
        }
    }

    /**
     * @param string $path
     * @param string|null $className - set class name
     * @param string|null $customModelsNs
     */
    public function genService(string $path, string $className = "Service", string $customModelsNs = null) {
        $customModelsNs = $customModelsNs ?: $this->modelsNs;
        /** @var EntityConverter $swagg1erEntityConverter */
        $swaggerServiceConverter = new ServiceConverter($this->sourceRoot, $this->serviceNs, $className, $customModelsNs);
        /** @var ClassEntity $entities */
        $service = $swaggerServiceConverter->get($this->debugPath);
        if ($service) {
            if (!File::exists($path))
                File::makeDirectory($path,0775,true,false);
            File::put( $path."/{$service->name}.php", $this->renderService($service));
        }
    }
}
