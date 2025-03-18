<?php

return [
    'openApiPath' => '@specs/issue_fix/88_in_case_of_updating_a_model_generator_creates_redundant_inverse_relations/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
