<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 19:06
 */

namespace mhapach\SwaggerModelGenerator\Libs\Converters\OpenApi3;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use mhapach\SwaggerModelGenerator\Libs\Converters\Swagger;
use mhapach\SwaggerModelGenerator\Libs\BaseService;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodParamEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\HintEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodReturnEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\PropertyEntity;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Method;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\MethodParam;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\MethodReturn;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Property;
use mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3\Root;

class ServiceConverter
{
    /** @var Root */
    private $sourceRoot;
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
     * @param Root $sourceRoot
     * @param string $ns
     * @param string $extends
     * @param string $implements
     */
    public function __construct(Root $sourceRoot, string $ns = null, string $modelsNs = null, string $extends = "BaseService", string $implements = null)
    {
        $this->sourceRoot = $sourceRoot;
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
            'hint' => new HintEntity($this->sourceRoot->info),
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

        if (!$this->serviceClassEntity->methods)
            return;
        
        /** @var MethodEntity $methodEntity */
        foreach($this->serviceClassEntity->methods as $method) if ($method->return) {
            /** @var MethodReturnEntity $methodReturnEntity */
            foreach ($method->return as $methodReturnEntity ) {
                if ($methodReturnEntity->refType)
                    $this->serviceClassEntity->includedClasses[$methodReturnEntity->refType] = $this->modelsNs . "\\" . $methodReturnEntity->refType;
            }
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
        foreach ($this->sourceRoot->paths as $method) if (!$debugPath || $debugPath == $method->path) {
            $isServiceResponseJson = $method->responses->{'200'}->content->{'application/json'} ?  true : false;

            $methodEntity = new MethodEntity([
                'serviceResponseType' => $isServiceResponseJson ? 'json' : 'string',
                'path' => $method->path,
                'name' => $method->name,
                'method' => $method->method,
                'ref' => $isServiceResponseJson ? $method->ref : null,
                'produces' => $method->produces,
                'hint' => new HintEntity([
                    'description' => $method->summary
                ]),
                'params' => $this->createMethodParams($method),
                'return' => $this->createMethodReturn($method)
            ]);
            $methods[] = $methodEntity;
        }
        return $methods ? collect($methods) : null ;
    }

    /**
     * @param Method $method
     * @return PropertyEntity[] | Collection | null
     */
    private function createMethodReturn(Method $method) {
        $res = null;
        /** @var MethodReturn $methodReturn */
        foreach($method->return as $methodReturn) {
            $res[]= new MethodReturnEntity($methodReturn->toArray());
        }
        if ($res)
            $res = collect($res);
        return $res;
    }
    
    /**
     * @param string $debugPath
     * @return MethodParamEntity[]
     */
    private function createMethodParams(Method $method)
    {
        if (!$method->parameters && !$method->requestBody)
            return null;
        
        if ($method->requestBody)
            $method->parameters[] = $method->requestBody;
        
        /** @var MethodParam $param */
        foreach ($method->parameters as $param) {
            $entityParams[] = new MethodParamEntity([
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
        return $entityParams ? collect($entityParams) : null ;
    }

}