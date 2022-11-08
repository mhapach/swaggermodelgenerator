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
use Psr\Log\LoggerInterface;
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
    /** @var array - default header lines */
    public $defaultHeaders = [];

    public static $logger = null;

    public function __construct(string $serviceAddress, array $defaultHeaders = [], ?LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->serviceAddress = $serviceAddress;
        $this->defaultHeaders = $defaultHeaders;
    }

    @include('mhapach::inc.methods')

}
