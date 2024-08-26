<?php

return [
    'openApiPath' => '@specs/issue_fix/30_add_validation_rules_by_attribute_name_or_pattern/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];

