<?php
/**
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 */
?>
@if ($methodEntity->params)
@foreach($methodEntity->params->where('required', '=', true) as $methodParamEntity)
@if($methodParamEntity->in != 'body')
        if (!isset($data["{{$methodParamEntity->in}}"]["{{$methodParamEntity->name}}"]))
            throw new \Exception("Required parameter {{$methodParamEntity->name}} is absent");
@else
        if (!isset($data["{{$methodParamEntity->in}}"]))
            throw new \Exception("Required parameter {{$methodParamEntity->name}} is absent");
@endif    
@endforeach
@endif
        if (!isset($data['headers']) && !empty($this->defaultHeaders))
            $data['headers'] = $this->defaultHeaders;

        /** @var string */
        $requestUrl = $this->serviceAddress . "{!! preg_replace('/\{(.*?)\}/', '{$data[\'path\'][\'$1\']}', $methodEntity->path) !!}";

        /** @var array */
        $response = $this->request($requestUrl, $data, "{{$methodEntity->method ?: 'get'}}");
        @php $returnTypeOrClassName = $methodEntity->refType @endphp

        $res = null;        
@if (!$methodEntity->return)
        $res = $response;
@else    
@if ($methodEntity->serviceResponseType == 'json' && ($methodEntity->type == 'array' || $methodEntity->ref))
        $response = json_decode($response);
@endif
@if ($methodEntity->type == 'array')
        if ($response)
            foreach ($response as $item)
                @if ($methodEntity->ref) $res[] = new {{$returnTypeOrClassName}}($item, $addClassMapping) @else $res[] = $item @endif;
    
        if ($res)
            $res = collect($res);
@elseif ($methodEntity->ref)
        $res = new {{$returnTypeOrClassName}}($response, $addClassMapping);
@else
        $res = $response;
@endif
@endif {{-- /@if ($methodEntity->return) --}}
        return $res;
