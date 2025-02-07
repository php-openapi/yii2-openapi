<?php

return [
    'openApiPath' => '@specs/issue_fix/64_add_a_test_for_a_column_change_data_type_comment_position_all_3_are_changed/index.yml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];
