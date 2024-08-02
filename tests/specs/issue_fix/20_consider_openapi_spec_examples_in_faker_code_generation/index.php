<?php

return [
    'openApiPath' => '@specs/issue_fix/20_consider_openapi_spec_examples_in_faker_code_generation/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
//    'excludeModels' => [
//        'Error',
//    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
