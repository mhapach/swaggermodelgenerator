<?php
/**
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 */
?>
@if (!empty($entity->getDates()))    
    protected $dates = ["{!!implode('", "', $entity->getDates())!!}"];    
@endif
@if (!empty($entity->getClassMapping()))
    
    protected $classMapping = [
    @foreach($entity->getClassMapping() as $name => $mapping)
    "{{$name}}" => {{$mapping}}::class,
    @endforeach];    
@endif