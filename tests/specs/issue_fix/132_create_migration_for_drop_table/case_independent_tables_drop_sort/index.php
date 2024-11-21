<?php

return [
    'openApiPath' => '@specs/issue_fix/132_create_migration_for_drop_table/case_independent_tables_drop_sort/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false,
];
