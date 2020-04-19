<?php
/**
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 */
?>
{{-- Properties generation --}}
@if ($entity->properties)
@foreach($entity->properties as $propertyEntity)
    /** @var {{$propertyEntity->hint->psrType}} - {{$propertyEntity->hint->title}} {{$propertyEntity->hint->description}} */
    public ${{$propertyEntity->name}};
@endforeach
@endif
