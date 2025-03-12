<?php

return [
    'openApiPath' => '@specs/issue_fix/96_component_schema_should_be_optional/index.yml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => true,
];
