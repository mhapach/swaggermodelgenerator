<?php

namespace mhapach\SwaggerModelGenerator\Facades;

use Illuminate\Support\Facades\Facade;

class  SwaggerModelGenerator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'swaggermodelgenerator';
    }
}
