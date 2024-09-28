<?php

return [
    'openApiPath' => '@specs/issue_fix/58_create_migration_for_column_position_change_if_a_field_position_is_changed_in_spec/index.yml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
