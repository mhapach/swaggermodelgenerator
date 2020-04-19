<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 19:06
 */

namespace mhapach\SwaggerModelGenerator\src\Libs\Converters\Swagger;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use mhapach\SwaggerModelGenerator\Libs\Converters\Swagger;
use mhapach\SwaggerModelGenerator\src\Libs\BaseService;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodParamEntity;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\ClassEntity;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\HintEntity;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodEntity;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\PropertyEntity;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Swagger\Method;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Swagger\MethodParam;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Swagger\Property;
use mhapach\SwaggerModelGenerator\src\Libs\Models\Swagger\Root;

class ServiceConverter
{
    /** @var Root */
    private $swagger;
    /** @var string */
    private $ns = '';
    /** @var string */
    private $modelsNs = '';
    /** @var string */
    private $extends = "";
    /** @var string */
    private $implements = "";
    /** @var string[] */
    private $modules;

    /** @var ClassEntity */
    private $serviceClassEntity;

    /**
     * Swagger constructor.
     * @param Root $swagger
     * @param string $ns
     * @param string $extends
     * @param string $implements
     */
    public function __construct(Root $swagger, string $ns = null, string $modelsNs = null, string $extends = "BaseService", string $implements = null)
    {
        $this->swagger = $swagger;
        $this->ns = $ns;
        $this->modelsNs = $modelsNs ?: $ns;
        $this->extends = $extends;
        $this->implements = $implements;
    }

    /**
     * @return ClassEntity
     */
    public function get(string $debugPath = null)
    {
        $this->serviceClassEntity = new ClassEntity([
            'name' => "Service",
            'ns' => $this->ns,
            'extends' => "BaseService",
            'implements' => $this->implements,
            'hint' => new HintEntity($this->swagger->info),
            'methods' => $this->createMethods($debugPath)
        ]);
        
        $this->setIncludedClasses();

        return $this->serviceClassEntity;
    }

    /**
     * @return void
     */
    private function setIncludedClasses()
    {
        $this->serviceClassEntity->includedClasses["BaseService"] = BaseService::class;
        $this->serviceClassEntity->includedClasses["Collection"] = Collection::class;

        if ($this->modelsNs == $this->ns)
            return;

        /** @var MethodEntity $methodEntity */
        foreach($this->serviceClassEntity->methods as $methodEntity) if ($methodEntity->return->refType){
            $this->serviceClassEntity->includedClasses[$methodEntity->return->refType] = $this->modelsNs."\\".$methodEntity->return->refType;
        }        
    }
    
    /**
     * @param string $debugPath
     * @return MethodEntity[] | Collection | null
     */
    private function createMethods(string $debugPath = null)
    {
        /** @var MethodEntity[] $methods */
        $methods = null;
        /** @var Method $method */
        foreach ($this->swagger->paths as $method) if (!$debugPath || $debugPath == $method->path) {
            $isServiceResponseJson = in_array("application/json", $method->produces) ?  true : false;

            $return = new PropertyEntity($method->return->toArray());
            if (!$isServiceResponseJson){
                $return->type = "string";
                $return->psrType = "string";
                $return->refType = "string";
                $return->hint->psrType = "string";
                $return->hint->type = "string";
            }
            
            $methodEntity = new MethodEntity([
                'serviceResponseType' => $isServiceResponseJson ? 'json' : 'string',
                'path' => $method->path, 
                'name' => $method->name,
                'method' => $method->method,
                'return' => $return,
                'ref' => $isServiceResponseJson ? $method->ref : null,
                'produces' => $method->produces,
                'hint' => new HintEntity([
                    'type' => $isServiceResponseJson ? $method->return->type : 'string',
                    'psrType' => $isServiceResponseJson ? $method->return->psrType : 'string',
                    'format' => $method->return->format,
                    'description' => $method->summary,
                ]),
                'params' => $this->createMethodParams($method)
            ]);
            
            $methods[] = $methodEntity;
        }
        return $methods ? collect($methods) : null ;
    }
    
    /**
     * @param string $debugPath
     * @return MethodParamEntity[]
     */
    private function createMethodParams(Method $method)
    {
        $params = null;
        /** @var MethodParam $param */
        foreach ($method->parameters as $param) {
            $params[] = new MethodParamEntity([
                'type' => $param->type,
                'psrType' => $param->psrType,
                'format' => $param->format,
                'name' => $param->name,
                'required' => $param->required,
                'allowEmptyValue' => $param->allowEmptyValue,                
                'in' => $param->in,                

                'hint' => new HintEntity([
                    'type' => $param->type,
                    'psrType' => $param->psrType,
                    'format' => $param->format,
                    'description' => $param->description,
                ]),
            ]);
        }
        return $params ? collect($params) : null ;
    }

}