<?php

return [
    'openApiPath' => '@specs/issue_fix/62_unnecessary_sql_statement_for_comment_on_column_in_pgsql/index.yml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false, // `generateModels` must be `true` in order to use `generateModelFaker` as `true`
];

