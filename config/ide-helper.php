<?php

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Session\Store;

return [
    /**
     * Filename & Format.
     */
    'filename'        => 'vendor/_ide_helper',
    'format'          => 'php',

    /*
     * Fluent helpers.
     */
    'include_fluent'  => false,

    /*
     * Helper files to include.
     */
    'include_helpers' => false,
    'helper_files'    => [
        base_path() . '/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
    ],

    /*
     * Model locations to include.
     */
    'model_locations' => [ 'app/Models' ],

    /*
     * Extra classes.
     */
    'extra'           => [
        'Eloquent' => [ EloquentBuilder::class, QueryBuilder::class ],
        'Session'  => [ Store::class ],
    ],

    'magic'                       => [
        'Log' => [
            'debug'     => 'Monolog\Logger::addDebug',
            'info'      => 'Monolog\Logger::addInfo',
            'notice'    => 'Monolog\Logger::addNotice',
            'warning'   => 'Monolog\Logger::addWarning',
            'error'     => 'Monolog\Logger::addError',
            'critical'  => 'Monolog\Logger::addCritical',
            'alert'     => 'Monolog\Logger::addAlert',
            'emergency' => 'Monolog\Logger::addEmergency',
        ],
    ],

    /**
     * Interface implementations.
     */
    'interfaces'                  => [],

    /**
     * Support for custom DB types.
     */
    'custom_db_types'             => [
    ],
    'model_camel_case_properties' => false,

    /**
     * Property casts.
     */
    'type_overrides'              => [
        'integer' => 'int',
        'boolean' => 'bool',
    ],
];
