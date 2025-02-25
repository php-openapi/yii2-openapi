<?php

return [
    'openApiPath' => '@specs/issue_fix/23_consider_openapi_extension_x_no_relation_also_in_other_pertinent_place/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
