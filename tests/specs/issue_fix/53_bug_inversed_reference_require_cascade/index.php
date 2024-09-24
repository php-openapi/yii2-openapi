<?php

return [
    'openApiPath' => '@specs/issue_fix/53_bug_inversed_reference_require_cascade/index.yml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];

