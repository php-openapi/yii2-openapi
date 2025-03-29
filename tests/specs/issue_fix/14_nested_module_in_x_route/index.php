<?php

return [
    'openApiPath' => '@specs/issue_fix/14_nested_module_in_x_route/index.yml',
    'generateUrls' => true,
    'generateModels' => true,
    // 'useJsonApi' => true, // TODO for FractalAction
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'generateModelFaker' => false,
    // 'urlPrefixes' => [
    //     'hi' => ['module' => 'greet', 'namespace' => 'app\greet'],
    //     'abc' => ['module' => 'abc', 'namespace' => 'app\abc'],
    // ]
];
