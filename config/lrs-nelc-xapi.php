<?php
return [
    'endpoint'      => env('LRS_ENDPOINT'),
    'middleware'      => ['web'],
    'key'    => env('LRS_USERNAME'),
    'secret'    => env('LRS_PASSWORD'),
    // Platform display names (used in xAPI statement extensions)
    'platform_in_arabic'    => env('LRS_PLATFORM_AR', ''),
    'platform_in_english'    => env('LRS_PLATFORM_EN', ''),
    // Short platform identifier/value used in the statement `platform` field
    'platform' => env('LRS_PLATFORM', ''),
    'version'   => env('LRS_VERSION', '1.0.3'),
    'base_route'    => 'nelcxapi/test',
];
