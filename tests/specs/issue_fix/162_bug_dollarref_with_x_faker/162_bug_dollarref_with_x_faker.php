<?php

return [
    'openApiPath' => '@specs/issue_fix/162_bug_dollarref_with_x_faker/162_bug_dollarref_with_x_faker.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
