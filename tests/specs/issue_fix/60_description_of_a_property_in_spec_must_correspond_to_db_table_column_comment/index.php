<?php

return [
    'openApiPath' => '@specs/issue_fix/60_description_of_a_property_in_spec_must_correspond_to_db_table_column_comment/index.yml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
