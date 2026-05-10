<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace'  => '\Nelc\LaravelNelcXapiIntegration\Controllers',
    'middleware' => config('lrs-nelc-xapi.middleware')
], function () {
    Route::get(config('lrs-nelc-xapi.base_route'), 'LrsNelcXapiController@getIndex')
        ->name('lrs-nelc-xapi.base_route');

    Route::post(config('lrs-nelc-xapi.base_route'), 'LrsNelcXapiController@postIndex')
        ->name('lrs-nelc-xapi.validate_base_route');
});