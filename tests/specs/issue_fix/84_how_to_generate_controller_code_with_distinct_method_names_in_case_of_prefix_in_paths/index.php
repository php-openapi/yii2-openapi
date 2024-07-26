<?php

return [
    'openApiPath' => '@specs/issue_fix/84_how_to_generate_controller_code_with_distinct_method_names_in_case_of_prefix_in_paths/index.yaml',
    'generateUrls' => true,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
