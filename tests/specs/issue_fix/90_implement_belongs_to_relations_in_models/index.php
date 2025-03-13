<?php

return [
    'openApiPath' => '@specs/issue_fix/90_implement_belongs_to_relations_in_models/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
