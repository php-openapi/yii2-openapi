<?php

return [
    'openApiPath' => '@specs/issue_fix/74_invalid_schema_reference_error/index.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => true,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
    'ignoreSpecErrors' => true,
];
