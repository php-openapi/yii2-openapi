<?php

return [
    'openApiPath' => '@specs/issue_fix/102_fractalaction_not_generated_for_root_path/index.yml',
    'generateUrls' => true,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'useJsonApi' => true,
    'generateControllers' => true,
    'generateMigrations' => false,
    'generateModelFaker' => false,
];
