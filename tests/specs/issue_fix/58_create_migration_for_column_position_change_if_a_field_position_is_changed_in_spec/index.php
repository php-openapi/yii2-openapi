<?php

return [
    'openApiPath' => '@specs/issue_fix/58_create_migration_for_column_position_change_if_a_field_position_is_changed_in_spec/index.yml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
