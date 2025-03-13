<?php

return [
    'openApiPath' => '@specs/issue_fix/78_properties_that_are_marked_as_readonly_are_not_read_only/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
