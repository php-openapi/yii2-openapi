<?php

return [
    'openApiPath' => '@specs/issue_fix/14_module_config_in_url_prefixes/index.yml',
    'generateUrls' => true,
    'generateModels' => false,
    // 'useJsonApi' => true, // TODO for FractalAction
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'generateModelFaker' => false,
    'urlPrefixes' => [
        'hi' => ['module' => 'greet', 'namespace' => 'app\greet'],
    ]
];
