<?php
/**
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodParamEntity $methodParamEntity
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

/**
@if($entity->hint->title) * {{$entity->hint->title}} @endif.
@if($entity->hint->description) * {{$entity->hint->description}} @endif.
 * Class {{$entity->name}}
 * @package {{$entity->ns}}
 */
class {{$entity->name}} {{$entity->extends ? 'extends '.$entity->extends : ''}} {{$entity->implements ? 'implements '.$entity->implements : ''}}
{
    /** @var string - rest service address */
    public $serviceAddress;

    public function __construct($serviceAddress)
    {
        $this->serviceAddress = $serviceAddress;
    }

    @include('mhapach::inc.methods')

}