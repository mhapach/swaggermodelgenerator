<?php
/**
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 */
?>
{{-- Properties generation --}}
@if ($entity->properties)
@foreach($entity->properties as $propertyEntity)
    /** @var {{$propertyEntity->hint->psrType}} @if(strpos($propertyEntity->hint->psrType, '[]')!==false)| Collection @endif - {{$propertyEntity->hint->title}} {{$propertyEntity->hint->description}} */
    public ${{$propertyEntity->name}};
@endforeach
@endif
