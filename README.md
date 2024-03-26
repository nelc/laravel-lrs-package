# Laravel Nelc Xapi Integration

Laravel package for integrating with Saudi NELC xAPI.

## Installation

## Step 1: Navigate to Your Project Directory

Before you begin, navigate to the directory where your Laravel project is located. Use the following command to change into your project directory:

```bash
cd path/to/your/laravel-project
```

## Step 2: Installation

You can install this library using Composer. Run the following command:

```bash
composer require nelc/laravel-nelc-xapi-integration
```

## Step 3: Register the ServiceProvider

Register the `NelcXapiServiceProvider` in your Laravel project. Open the `config/app.php` file and add the following line to the `providers` array:

```php
// config/app.php

'providers' => ServiceProvider::defaultProviders()->merge([
    /*
        * Package Service Providers...
    */
    // Other providers...
    Nelc\LaravelNelcXapiIntegration\NelcXapiServiceProvider::class,
])->toArray(),
```

## Step 4: Dump Autoload Files
After registering the ServiceProvider, run the following command to re-generate Composer's autoloader files:

```bash
composer dump-autoload
```

## Step 5: Publish Configuration Files
To publish the configuration files provided by this package, run the following Artisan command:

```bash
php artisan vendor:publish --provider="Nelc\LaravelNelcXapiIntegration\NelcXapiServiceProvider"
```
This will deploy `asset` files inside the public folder with the name `lrs-nelc-xapi` and the file `lrs-nelc-xapi.php` inside the `config` folder.

## Step 6: Configure Basic Settings
To start using this package, follow these steps to configure the basic settings and connect with NELC XAPI:


Add your `LRS_ENDPOINT` and `LRS_USERNAME` and `LRS_PASSWORD` to projects `.env` file
```bash
# .env file content
LRS_ENDPOINT=provided_lrs_endpoint
LRS_USERNAME=your_lrs_username
LRS_PASSWORD=your_lrs_password
```

If needed you can open the `lrs-nelc-xapi.php` file inside the `config` folder. Adjust the basic settings according to your requirements. Make sure to set up any `authentication (key, secret)` details through `.env` file to avoid these sensitive being commited to git repo and being exposed.

```php
// config/lrs-nelc-xapi.php
return [
    'endpoint'      => env('LRS_ENDPOINT'),
    'middleware'      => ['web'], // Middleware to display the demo page, ['web', 'auth', ...]
    'key'    => env('LRS_USERNAME'),
    'secret'    => env('LRS_PASSWORD'),
    'platform_in_arabic'    => '', // Platform name in Arabic
    'platform_in_english'    => '', // Platform name in English
    'base_route'    => 'nelcxapi/test', // Demo Page Link
];
```


Now, you can go to the demo page and start testing statement submissions using the following link:
`https://your-site.com/nelcxapi/test` or through the `route('lrs-nelc-xapi.base_route')`.

## Usage
Once the package is installed and the ServiceProvider is registered, you can use it in your Laravel project. Here's a simple examples:

## Registered Statement
Indicates the actor is officially enrolled or inducted in an activity.
```php
use Nelc\LaravelNelcXapiIntegration\XapiIntegration;
// ...

$xapi = new XapiIntegration();
$response = $xapi->Registered(
    '123456789', // Student National ID
    'betalamoud@gmail.com', // Student Email
    '123', // Course Id OR url Or slug
    'New Course', // Course Title
    'New Course description', // Course description
    'MR Hassan', // instructor Name
    'mrhassan@mail.com',  // instructor Email
);

// dd( $response['status'] ); return 200
// dd( $response['message'] ); return ok
// dd( $response['body'] ); return UUID
```

## Initialized Statement
Indicates the activity provider has determined that the actor successfully started an activity.
```php
use Nelc\LaravelNelcXapiIntegration\XapiIntegration;
// ...

$xapi = new XapiIntegration();
$response = $xapi->Initialized(
    '123456789', // Student National ID
    'betalamoud@gmail.com', // Student Email
    '123', // Course Id OR url Or slug
    'New Course', // Course Title
    'New Course description', // Course description
    'MR Hassan', // instructor Name
    'mrhassan@mail.com',  // instructor Email
);

// dd( $response['status'] ); return 200
// dd( $response['message'] ); return ok
// dd( $response['body'] ); return UUID
```

## Watched Statement
Indicates that the actor has watched the object. This verb is typically applicable only when the object represents dynamic, visible content such as a movie, a television show or a public performance. This verb is a more specific form of the verbs experience, play and consume.
```php
use Nelc\LaravelNelcXapiIntegration\XapiIntegration;
// ...

$xapi = new XapiIntegration();
$response = $xapi->Watched(
    '123456789', // Student National ID
    'betalamoud@gmail.com', // Student Email
    '/url/to/lesson', // Lesson Or object URL
    'Lesson title', // Object title
    'Lesson description',  // Object description
    true, // The status indicating whether it has been fully watched (boolean).
    'PT15M', // The duration of the watching session in `ISO 8601` format.
    '123', // Course Id OR url Or slug
    'New Course', // Course Title
    'New Course description', // Course description
    'MR Hassan', // instructor Name
    'mrhassan@mail.com',  // instructor Email

);

// dd( $response['status'] ); return 200
// dd( $response['message'] ); return ok
// dd( $response['body'] ); return UUID
```

## Completed Statement
Indicates the actor finished or concluded the activity normally.

### Completed (Lesson or class)
```php
use Nelc\LaravelNelcXapiIntegration\XapiIntegration;
// ...

$xapi = new XapiIntegration();
$response = $xapi->CompletedLesson(
    '123456789', // Student National ID
    'betalamoud@gmail.com', // Student Email
    '/url/to/lesson', // Lesson URL
    'Lesson title',
    'Lesson description',
    '123', // Course Id OR url Or slug
    'New Course', // Course Title
    'New Course description', // Course description
    'MR Hassan', // instructor Name
    'mrhassan@mail.com',  // instructor Email
);

// dd( $response['status'] ); return 200
// dd( $response['message'] ); return ok
// dd( $response['body'] ); return UUID
```
