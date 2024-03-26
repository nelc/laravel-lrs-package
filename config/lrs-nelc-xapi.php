<?php
return [
    'endpoint'      => env('LRS_ENDPOINT'),
    'middleware'      => ['web'],
    'key'    => env('LRS_USERNAME'),
    'secret'    => env('LRS_PASSWORD'),
    'platform_in_arabic'    => '',
    'platform_in_english'    => '',
    'base_route'    => 'nelcxapi/test',
];
