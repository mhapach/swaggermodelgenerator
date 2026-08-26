<?php


namespace mhapach\SwaggerModelGenerator\Libs\Converters;


use mhapach\SwaggerModelGenerator\Libs\Models\Entities\ClassEntity;

abstract class BaseConverter
{

    public function genModels(string $path) {}

    public function genService(string $path) {}

    public function renderEntity(ClassEntity $entity) {
        return (view('mhapach::class', ['entity' => $entity])->render());
    }
    public function renderModel(ClassEntity $entity) {
        return (view('mhapach::model', ['entity' => $entity])->render());
    }
    public function renderService(ClassEntity $entity) {
        return (view('mhapach::service', ['entity' => $entity])->render());
    }
}