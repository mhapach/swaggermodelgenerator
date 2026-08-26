<?php
/**
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 */
?>
@if (!empty($entity->dates))    
    protected $dates = ["{!!implode('", "', $entity->dates)!!}"];    
@endif
@if (!empty($entity->classMapping))
    
    protected $classMapping = [
    @foreach($entity->classMapping as $name => $mapping)
    "{{$name}}" => {{$mapping}}::class,
    @endforeach];    
@endif