<?php

return [
    'openApiPath' => '@specs/issue_fix/79_response_status_codes_are_not_the_codes_defined_in_spec/index.yml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'generateModelFaker' => false,
];
