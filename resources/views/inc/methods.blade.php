<?php
/**
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodParamEntity $requestBodyParam
 */
?>
@if (!empty($entity->methods))
@foreach($entity->methods as $methodEntity)
@php $requestBodyParam = $methodEntity->params ? $methodEntity->params->where('in', '=', 'body')->first() : null; @endphp
    /**@if ($methodEntity->hint->title)
     * {{$methodEntity->hint->title}}
@endif 
@if ($methodEntity->hint->description)
     * {{$methodEntity->hint->description}}
@endif
@if ($methodEntity->params)
     * @param array $data - [    
@foreach($methodEntity->params->where('in', '!=', 'body')->pluck('in')->unique() as $inGroup)
     *     {{$inGroup}} => [
@foreach($methodEntity->params->where('in','=',$inGroup)->sortByDesc('required') as $methodParamEntity)
     *         '{{$methodParamEntity->name}}' => {{$methodParamEntity->hint->type}} @if ($methodParamEntity->required)- required @endif {!! $methodParamEntity->hint->description !!},
@endforeach
     *     ]
@endforeach
@if ($requestBodyParam)
     *     'body' => (string)'
{!! preg_replace('/(^.*?$)/m','     *      $1', $requestBodyParam->hint->description) !!}'    
@endif
     * ]
@endif
     * @param array $addClassMapping - ['field' => YourClass::class]
     * @return {{$methodEntity->psrType}} @if(strpos($methodEntity->psrType, '[]')!== false)| Collection @endif .
     * @throws \Exception
     */
    public function {{$methodEntity->name}}(array $data = [], array $addClassMapping = [])
    {   
        @include('mhapach::inc.method_body')
    }
 
@endforeach
@endif