<?php
/**
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\ClassEntity $entity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\PropertyEntity $propertyEntity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodEntity $methodEntity
 * @var \mhapach\SwaggerModelGenerator\src\Libs\Models\Entities\MethodParamEntity $methodParamEntity
 */
?>
@foreach($methodEntity->params->where('required', '=', true) as $methodParamEntity)
        if (!isset($data["{{$methodParamEntity->in}}"]["{{$methodParamEntity->name}}"]))
            throw new \Exception("Required parameter {{$methodParamEntity->name}} is absent");
@endforeach

        /** @var string */
        $requestUrl = $this->serviceAddress . "{!! preg_replace('/\{(.*?)\}/', '{$data[\'path\'][\'$1\']}', $methodEntity->path) !!}";

        /** @var array */
        $response = $this->request($requestUrl, $data, "{{$methodEntity->method ?: 'get'}}");
        @php $returnTypeOrClassName= str_replace('[]', '', $methodEntity->return->psrType) @endphp

        $res = null;

@if ($methodEntity->serviceResponseType == 'json')
        $response = json_decode($response);
@endif
@if ($methodEntity->return->type == 'array')
        foreach ($response as $item)
            @if ($methodEntity->return->ref) $res[] = new {{$returnTypeOrClassName}}($item) @else $res[] = $item @endif;
    
        if ($res)
            $res = collect($res);

@elseif ($methodEntity->return->ref)
        $res = new {{$returnTypeOrClassName}}($response);
@else
        $res = $response;
@endif
        return $res;
