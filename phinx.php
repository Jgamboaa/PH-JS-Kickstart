<?php
require_once __DIR__ . '/config/env_reader.php';

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'migrations',
            'default_environment' => 'default',
            'default' => [
                'adapter' => env('DB_ADAPTER', 'mysql'),
                'host' => env('DB_HOST', 'localhost'),
                'name' => env('DB_NAME', 'db'),
                'user' => env('DB_USER', 'root'),
                'pass' => env('DB_PASS', ''),
                'port' => env('DB_PORT', '3306'),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
            ]
        ],
        'version_order' => 'creation'
    ];
