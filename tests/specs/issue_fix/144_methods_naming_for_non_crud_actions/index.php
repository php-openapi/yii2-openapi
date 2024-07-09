<?php

return [
    'openApiPath' => '@specs/issue_fix/144_methods_naming_for_non_crud_actions/index.yaml',
    'generateUrls' => true,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
