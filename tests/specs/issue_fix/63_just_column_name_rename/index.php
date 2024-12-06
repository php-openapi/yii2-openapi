<?php

return [
    'openApiPath' => '@specs/issue_fix/63_just_column_name_rename/index.yml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
