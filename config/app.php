<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */
    'MY_APP_API_KEY' => env('MY_APP_API_KEY', 's3cr3t-h4xx0r-k3y-007'),

    'GA4_Acoount_ID' => env('GA4_Acoount_ID', '344421576'),
    'env' => env('APP_ENV', 'production'),

    'AAPANEL_API_KEY' => env('AAPANEL_API_KEY'),
    'AAPANEL_PANEL_URL' => env('AAPANEL_PANEL_URL'),
    'AAPANEL_ADD_SITE_ENDPOINT' => env('AAPANEL_ADD_SITE_ENDPOINT'),
    'AAPANEL_SET_RUN_PATH_ENDPOINT' => env('AAPANEL_SET_RUN_PATH_ENDPOINT'),
    'AAPANEL_WEBSITE_RUN_PATH' => env('AAPANEL_WEBSITE_RUN_PATH'),
    'AAPANEL_WEBSITE_PATH' => env('AAPANEL_WEBSITE_PATH'),
    'AAPANEL_SAVE_FILE_ENDPOINT' => env('AAPANEL_SAVE_FILE_ENDPOINT'),
    'AAPANEL_NGINX_CONF_BASE_PATH' => env('AAPANEL_NGINX_CONF_BASE_PATH'),
    'AAPANEL_NGINX_URL_CONF_BASE_PATH' => env('AAPANEL_NGINX_URL_CONF_BASE_PATH'),
    'AAPANEL_GET_FILE_ENDPOINT' => env('AAPANEL_GET_FILE_ENDPOINT'),
    'contabo_s3_base_url' => env('AWS_URL'),


    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'google' => [
        'ads_path' => env('GOOGLE_ADS_PATH'),
        'service_file_name' => env('GOOGLE_SERVICE_FILE_NAME'),
        'ads_file_name' => env('GOOGLE_ADS_FILE_NAME'),
        'credentials_path' => env('GOOGLE_SERVICE_PATH'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
    'encryption' => [
        'static_iv' => env('STATIC_IV', 's3cr3t-h4xx0r-k3y-007'),
        'encryption_key' => env('STANDARD_ENCRYPT_KEY', 's3cr3t-h4xx0r-k3y-007'),
    ],
    'cloudflare' => [
        'base_uri'   => env('CLOUDFLARE_BASE_URI', 'https://api.cloudflare.com/client/v4/'),
        'auth_email' => env('CLOUDFLARE_AUTH_EMAIL'),
        'auth_key'   => env('CLOUDFLARE_API_KEY'),
    ],


    'providers' => ServiceProvider::defaultProviders()->merge([

        Intervention\Image\ImageServiceProviderLaravelRecent::class,
        Spatie\Permission\PermissionServiceProvider::class,

    ])->toArray(),


    'aliases' => Facade::defaultAliases()->merge([
        'Image' => Intervention\Image\Facades\Image::class,

    ])->toArray(),




];
