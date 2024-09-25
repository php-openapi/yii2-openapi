<?php

return [
    'openApiPath' => '@specs/issue_fix/51_bug_allof_with_description/index.yml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => true,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];

