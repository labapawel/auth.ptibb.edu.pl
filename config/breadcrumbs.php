<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb Files
    |--------------------------------------------------------------------------
    |
    | The path to the routes/breadcrumbs.php file, or an array of paths.
    | This file should contain the breadcrumb definitions for your application.
    | Set to false to disable automatic loading of breadcrumb files.
    |
    */

    'files' => base_path('routes/breadcrumbs.php'),

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs Manager Class
    |--------------------------------------------------------------------------
    |
    | The class to use as the breadcrumbs manager. You can override this if
    | you need to extend the default functionality.
    |
    */

    'manager-class' => \DaveJamesMiller\Breadcrumbs\BreadcrumbsManager::class,

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs Generator Class
    |--------------------------------------------------------------------------
    |
    | The class to use as the breadcrumbs generator. You can override this if
    | you need to extend the default functionality.
    |
    */

    'generator-class' => \DaveJamesMiller\Breadcrumbs\BreadcrumbsGenerator::class,

];