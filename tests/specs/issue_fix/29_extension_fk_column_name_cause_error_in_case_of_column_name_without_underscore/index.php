<?php

return [
    'openApiPath' => '@specs/issue_fix/29_extension_fk_column_name_cause_error_in_case_of_column_name_without_underscore/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true,
];
