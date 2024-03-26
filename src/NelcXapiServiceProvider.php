<?php

namespace Nelc\LaravelNelcXapiIntegration;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class NelcXapiServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config';
    const ROUTE_PATH = __DIR__ . '/../routes';
    const VIEW_PATH = __DIR__ . '/views';
    const ASSET_PATH = __DIR__ . '/../assets';

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load configuration files

        $this->publishes([
            self::CONFIG_PATH => config_path()
        ], 'config');

        // Load assets files
        $this->publishes([
            self::ASSET_PATH => public_path('lrs-nelc-xapi')
        ], 'assets');

        // Load route files
        $this->loadRoutesFrom(self::ROUTE_PATH . '/web.php');

        // Load views
        $this->loadViewsFrom(self::VIEW_PATH, 'lrs-nelc-xapi');

        Blade::directive('NelcXapiScript', function ($expression) {
            $output = "<script src=\"{{asset('lrs-nelc-xapi/js/lrs-nelc-xapi.js')}}\"></script>";
            $output .= "<script src=\"{{asset('lrs-nelc-xapi/bootstrap/js/bootstrap.min.js')}}\"></script>";
            return $output;
        });

        Blade::directive('NelcXapiStyle', function ($expression) {
            $output = "<link href=\"{{asset('lrs-nelc-xapi/css/lrs-nelc-xapi.css')}}\" rel=\"stylesheet\" />";
            $output .= "<link href=\"{{asset('lrs-nelc-xapi/bootstrap/css/bootstrap.min.css')}}\" rel=\"stylesheet\" />";
            return $output;
        });
    }

        /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register any services, bindings, or other things here
        $this->mergeConfigFrom(
            self::CONFIG_PATH . '/lrs-nelc-xapi.php',
            'lrs-nelc-xapi'
        );
    }
}
