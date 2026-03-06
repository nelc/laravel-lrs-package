<?php
return [
    'enabled' => env('LRS_ENABLED', false),
    'endpoint'      => env('LRS_ENDPOINT'),
    'middleware'      => ['web'],
    'key'    => env('LRS_USERNAME'),
    'secret'    => env('LRS_PASSWORD'),
    'platform_in_arabic'    => env('LRS_PLATFORM_ARABIC', ''),
    'platform_in_english'    => env('LRS_PLATFORM_ENGLISH', ''),
    'activity_base_url' => env('LRS_ACTIVITY_BASE_URL', env('APP_URL')),
    'timeout' => (int) env('LRS_TIMEOUT', 15),
    'verify_ssl' => filter_var(env('LRS_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),
    'base_route'    => 'nelcxapi/test',
];
