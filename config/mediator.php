<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Handler Discovery Paths
    |--------------------------------------------------------------------------
    |
    | This array defines the directories where the MediatorService will scan
    | for command/request handlers. These paths are relative to your Laravel
    | application's base directory (typically the 'app' directory).
    |
    | Example: app_path('Handlers/Commands') would scan 'app/Handlers/Commands'.
    |
    */
    'handler_paths' => [
        app_path('Handlers'), // A common directory for all handlers
        // app_path('Http/Controllers'), // Uncomment if controllers can be handlers
        // app_path('Services'),       // Uncomment if services can be handlers
        // app_path('Services'),       // Uncomment if services can be handlers
    ],

    /*
    |--------------------------------------------------------------------------
    | Handler Namespace Mappings
    |--------------------------------------------------------------------------
    |
    | This array provides explicit mappings from root namespaces to their
    | corresponding file paths. This is useful for accurately deriving
    | the FQCN (Fully Qualified Class Name) from a file path when the
    | default 'App\' mapping is not sufficient.
    |
    | The key should be the root namespace (e.g., 'App\'), and the value
    | should be the absolute path to its corresponding directory.
    |
    | Example: 'App\Handlers\' => app_path('Handlers')
    |
    */
    'handler_namespaces' => [
        'App\\' => app_path(), // Default Laravel app namespace mapping
        // 'App\\Handlers\\' => app_path('Handlers'), // Example for a specific handler namespace
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude Paths (Optional)
    |--------------------------------------------------------------------------
    |
    | Define patterns for paths or file names to exclude from scanning.
    | This uses Symfony's Finder exclude() method syntax.
    |
    */
    'exclude_paths' => [
        // 'Tests', // Exclude test directories
        // 'stubs', // Exclude stub files
    ],
];
