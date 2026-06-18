<?php

return [
    'openApiPath' => '@specs/issue_fix/14_nested_module_in_x_route/index.yml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'generateModelFaker' => false,
];
