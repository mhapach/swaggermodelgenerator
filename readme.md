# SwaggerModelGenerator

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This library generates models and service with methods based on Swagger scheme. 
Current release supports only OpenAPI 2.0 (Aka OAS)

## Installation

Via Composer

``` bash
$ composer require mhapach/swaggermodelgenerator
```

## Usage
### Models and service generation
      
    $serviceScheme = "http://your-service.com/scheme";
    $modelsNamespace = 'App\Services\Models';
    $serviceNamespace = 'App\Services';
    $modelsPath = "/your-project/app/Services/Models";
    $servicePath = "/your-project/app/Services";

    /** @var Swagger $converterInstance */
    $converterInstance = (new SwaggerModelGenerator($serviceScheme, true))->getConverterInstance($modelsNamespace, $serviceNamespace);
    $converterInstance->genModels($modelsPath);        
    $converterInstance->genService($servicePath);
    
### Generated service and models usage
    $serviceAddress = "http://your-service.com/some-name";
    $service = new Service($serviceAddress);
    $bc = $service->benefitCategoriesUsingGET([
        'path' => ['id' => 1122],
        'query' => ['some-param' => 1],
    ]);   
    
## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [author name][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mhapach/swaggermodelgenerator.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mhapach/swaggermodelgenerator.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/mhapach/swaggermodelgenerator/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/mhapach/swaggermodelgenerator
[link-downloads]: https://packagist.org/packages/mhapach/swaggermodelgenerator
[link-travis]: https://travis-ci.org/mhapach/swaggermodelgenerator
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/mhapach
[link-contributors]: ../../contributors
