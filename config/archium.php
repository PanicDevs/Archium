<?php

// config for PanicDev/Archium
return [
    /*
    |--------------------------------------------------------------------------
    | Archium Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether Archium is enabled in your application.
    | When disabled, no Archium components will be rendered.
    |
    */
    'enabled' => env('ARCHIUM_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Assets Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path where Archium's compiled assets will be stored.
    | This is used when publishing assets to the application.
    |
    */
    'assets_path' => 'vendor/archium',

    /*
    |--------------------------------------------------------------------------
    | Modules XML URL
    |--------------------------------------------------------------------------
    |
    | This URL points to the XML file that contains the list of available modules.
    | This file contains information about each module including dependencies,
    | versions, and repository URLs.
    |
    */
    'modules_xml_url' => env('ARCHIUM_MODULES_XML_URL', 'https://raw.githubusercontent.com/PanicDevs/ArchiumSettings/refs/heads/main/modules.archium'),

    /*
    |--------------------------------------------------------------------------
    | Modules Directory
    |--------------------------------------------------------------------------
    |
    | This is the directory where modules will be installed. By default, it uses
    | the modules directory in the base path of your Laravel application.
    |
    */
    'modules_directory' => env('ARCHIUM_MODULES_DIRECTORY', base_path('modules')),
];
