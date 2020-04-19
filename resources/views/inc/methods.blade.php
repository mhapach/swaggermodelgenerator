<?php
/**
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 */
?>
@if (!empty($entity->methods))
@foreach($entity->methods as $methodEntity)
    /**@if ($methodEntity->hint->title)
      * {{$methodEntity->hint->title}}.
@endif
@if ($methodEntity->hint->description) 
      * {{$methodEntity->hint->description}}.
@endif
      * @var array $data - [
@foreach($methodEntity->params->pluck('in')->unique() as $inGroup)
      *     {{$inGroup}} => [
@foreach($methodEntity->params->where('in','=',$inGroup)->sortByDesc('required') as $methodParamEntity)
      *         {{$methodParamEntity->hint->type}} ${{$methodParamEntity->name}} @if ($methodParamEntity->required)- required @endif @if ($methodParamEntity->hint->description) {{$methodParamEntity->hint->description}} @endif,
@endforeach
      *     ]
@endforeach
      * ]
      * @throws \Exception    
@if ($methodEntity->return)
      * @return {{$methodEntity->return->hint->psrType}} @if(strpos($methodEntity->return->hint->psrType, '[]')!== false)| Collection @endif;
@endif
      */
    public function {{$methodEntity->name}}(array $data = [])
    {   
        @include('mhapach::inc.method_body')
    }
 
@endforeach
@endif