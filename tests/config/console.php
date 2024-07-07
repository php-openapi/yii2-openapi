<?php

use cebe\yii2openapi\Bootstrap;
use SamIT\Yii2\MariaDb\Schema;
use yii\console\controllers\MigrateController;
use yii\db\Connection;

$config = [
    'id' => 'cebe/yii2-openapi',
    'timeZone' => 'UTC',
    'basePath' => dirname(__DIR__) . '/tmp/docker_app',
    'runtimePath' => dirname(__DIR__) . '/tmp',
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => MigrateController::class,
            'migrationPath' => [
                dirname(__DIR__) . '/migrations',
            ],
        ],
        // see usage instructions at https://www.yiiframework.com/doc/guide/2.0/en/db-migrations#separated-migrations
        'migrate-mysql' => [ // just for development of tests
            'class' => MigrateController::class,
            'migrationPath' => [
                dirname(__DIR__) . '/migrations',
                dirname(__DIR__) . '/tmp/docker_app/migrations',
                dirname(__DIR__) . '/tmp/docker_app/migrations_mysql_db',
            ],
        ],
        'migrate-maria' => [ // just for development of tests
            'class' => MigrateController::class,
            'db' => 'maria',
            'migrationPath' => [
                dirname(__DIR__) . '/tmp/docker_app/migrations_maria_db',
            ],
        ],
        'migrate-pgsql' => [ // just for development of tests
            'class' => MigrateController::class,
            'db' => 'pgsql',
            'migrationPath' => [
                dirname(__DIR__) . '/tmp/docker_app/migrations_pgsql_db',
            ],
        ],
    ],
    'components' => [
        'pgsql' => [
            'class' => Connection::class,
            'dsn' => 'pgsql:host=postgres;dbname=testdb',
            'username' => 'dbuser',
            'password' => 'dbpass',
            'charset' => 'utf8',
            'tablePrefix' => 'itt_',
        ],
        'mysql' => [
            'class' => Connection::class,
            'dsn' => 'mysql:host=mysql;dbname=testdb',
            'username' => 'dbuser',
            'password' => 'dbpass',
            'charset' => 'utf8',
            'tablePrefix' => 'itt_',
        ],
        'maria' => [
            'class' => Connection::class,
            'dsn' => 'mysql:host=maria;dbname=testdb',
            'username' => 'dbuser',
            'password' => 'dbpass',
            'charset' => 'utf8',
            'tablePrefix' => 'itt_',
            'schemaMap' => [
                'mysql' => Schema::class
            ]
        ],
        'db' => [
            'class' => Connection::class,
            'dsn' => 'mysql:host=mysql;dbname=testdb',
            'username' => 'dbuser',
            'password' => 'dbpass',
            'charset' => 'utf8',
            'tablePrefix' => 'itt_',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];

return $config;
