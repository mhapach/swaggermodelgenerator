<?php

namespace mhapach\SwaggerModelGenerator;

use Illuminate\Support\ServiceProvider;

class SwaggerModelGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'mhapach');
         $this->loadViewsFrom(__DIR__.'/../resources/views', 'mhapach');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
//        if ($this->app->runningInConsole()) {
//            $this->bootForConsole();
//        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/swaggermodelgenerator.php', 'swaggermodelgenerator');

        // Register the service the package provides.
        $this->app->singleton('swaggermodelgenerator', function ($app) {
            return new SwaggerModelGenerator;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['swaggermodelgenerator'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/swaggermodelgenerator.php' => config_path('swaggermodelgenerator.php'),
        ], 'swaggermodelgenerator.config');

        // Publishing the views.
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/mhapach'),
        ], 'swaggermodelgenerator.views');

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/mhapach'),
        ], 'swaggermodelgenerator.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/mhapach'),
        ], 'swaggermodelgenerator.views');*/

        // Registering package commands.
//         $this->commands([
//             SwaggerModelGenerator::class
//         ]);
    }
}
