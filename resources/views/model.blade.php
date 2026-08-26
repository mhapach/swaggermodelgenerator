<?php
/**
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 */
print '<?php';
?>


@if($entity->ns)
namespace {{$entity->ns}};
@endif

@if($entity->includedClasses)
@foreach($entity->includedClasses as $includedClass)
use {{$includedClass}};    
@endforeach
@endif
use Illuminate\Support\Collection;

/**
@if($entity->hint->title) * {{$entity->hint->title}} @endif.
@if($entity->hint->description) * {{$entity->hint->description}} @endif.
 * Class {{$entity->name}}
 * @package {{$entity->ns}}
 */
class {{$entity->name}} {{$entity->extends ? 'extends '.$entity->extends : ''}} {{$entity->implements ? 'implements '.$entity->implements : ''}}
{
    @include('mhapach::inc.properties')

    @include('mhapach::inc.dates_and_mappings')

    @include('mhapach::inc.methods')

}