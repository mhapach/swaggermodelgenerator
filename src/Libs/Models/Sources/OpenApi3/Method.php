<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 12:10
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\OpenApi3;

use Illuminate\Support\Str;
use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;
use Illuminate\Support\Collection;

class Method extends BaseModel
{
    /** @var string - dynamic property */
    public $name;
    /** @var string */
    public $path;
    /** @var string */
    public $method;
    /** @var string[] | Collection */
    public $tags;
    /** @var string - описание метода */
    public $summary;
    /** @var string */
    public $operationId;
    /** @var string[] | Collection */
    public $produces;
    /** @var MethodParam[] | Collection */
    public $parameters;
    /** @var MethodParamRequestBody */
    public $requestBody;
    /** @var bool */
    public $deprecated;
    /** @var array */
    public $responses;
    /** @var array */
    public $security;
    /** @var Property[] | Collection - keyed by content type */
    public $return;

    protected $classMapping = [
        'parameters' => MethodParam::class,
        'requestBody' => MethodParamRequestBody::class
    ];

    public function __construct($attributes)
    {
        parent::__construct($attributes);
        $this->initName();
        $this->initReturn();
    }

    private function initReturn()
    {
        if (!$this->responses)
            return null;

        foreach ($this->responses as $responseCode => $response) {
//            if (empty($response->content) && empty($response->links) && empty($response->headers))
//                continue;
            $content = isset($response->content) ? $response->content : [];

            $description = $response->description ?? null;
            foreach ($content as $contentType => $value) {
                $schema = isset($value->schema) ? $value->schema : null;
                if ($schema) {
                    $schema->contentType = $contentType;
                    $schema->responseCode = $responseCode;
                    $schema->description = $description;
                }
                $this->return[] = new MethodReturn($schema);
            }
        }

        if (!empty($this->return))
            $this->return = collect($this->return);
    }

    private function initName()
    {
        if ($this->operationId)
            $this->name = $this->operationId;
        else {
            $this->name = $this->method;
            $parts = explode('/', $this->path);
            /** @var string $part */
            foreach ($parts as $part) if ($part) {
                if (strpos($part, '{') === false) {
                    $this->name .= ucfirst($part);
                } else {
                    $name = trim($part, '{}');
                    $this->name .= "By" . ucfirst($name);
                }
            }
        }
        $this->name = Str::camel($this->name); // Иногда попадаются тирешки
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isJsonResponse(string $code)
    {
        /** @var bool $result */
        $result = false;
        if (
            !empty($this->responses->{'200'}) &&
            !empty($this->responses->{'200'}->content) &&
            !empty($this->responses->{'200'}->content->{'application/json'})
        ) {
            $result = true;
        }
        return $result;
    }
    /**
     * "responses": {
     * "200": {
     * "description": "Данные золотой записи",
     * "schema": {
     * "$ref": "#/definitions/PeopleGoldenResponseDTO"
     * }
     * },
     * "401": {
     * "description": "Unauthorized"
     * },
     * "403": {
     * "description": "Forbidden"
     * },
     * "404": {
     * "description": "Not Found"
     * }
     * },
     * "deprecated": false
     */
}
