<?php

return [
    'openApiPath' => '@specs/issue_fix/52_bug_dependenton_allof_with_x_faker_false/index.yml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];

