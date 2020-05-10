<?php
/**
 * Created by PhpStorm.
 * User: M.Khapachev
 * Date: 09.04.2020
 * Time: 12:10
 */

namespace mhapach\SwaggerModelGenerator\Libs\Models\Sources\Swagger;


use mhapach\SwaggerModelGenerator\Libs\Models\BaseModel;
use Illuminate\Support\Collection;
use mhapach\SwaggerModelGenerator\Libs\Models\Entities\HintEntity;

class Method extends BaseModel
{
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
    /** @var bool */
    public $deprecated;
    /** @var array */
    public $responses;

    /** @var MethodReturn[] | Collection */
    public $return;


    protected $classMapping = [
        'parameters' => MethodParam::class
    ];

    public function __construct($attributes, array $classMapping = [])
    {
        parent::__construct($attributes, $classMapping);
        $this->initName();
        $this->initReturn();
    }
//
//    private function initReturn(){
//        if (!empty($this->responses['200'])) {
//            $successResponse = $this->responses['200'];
//            $this->return = new Property ($successResponse['schema']);
//            $this->return->hint = new HintEntity([
//                'type' => $this->return->type,
//                'description' => $this->return->description,
//                'psrType' => $this->return->psrType,
//                'format' => $this->return->format
//            ]);
//        }
//    }
    private function initReturn(){
        if (!$this->responses)
            return null;

        foreach ($this->responses as $responseCode => $value) {
            /** @var \stdClass $schema */
            $schema = $value->schema ?? $value;
            if (isset($schema->{'$ref'}))
                $schema->type = 'object';

            $schema->responseCode = $responseCode;
            $this->return[] = new MethodReturn($schema);
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
                }
                else {
                    $name = trim($part, '{}');
                    $this->name .= "By" . ucfirst($name);
                }
            }
        }
    }

    /**
    "responses": {
        "200": {
            "description": "Данные золотой записи",
            "schema": {
                "$ref": "#/definitions/PeopleGoldenResponseDTO"
            }
        },
        "401": {
            "description": "Unauthorized"
        },
        "403": {
            "description": "Forbidden"
        },
        "404": {
            "description": "Not Found"
        }
    },
    "deprecated": false
 */
}