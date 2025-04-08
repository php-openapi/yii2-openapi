<?php

return [
    'openApiPath' => '@specs/issue_fix/35_resolve_todo_re_check_options_route_in_fractal_action/index.yaml',
    'generateUrls' => true,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'useJsonApi' => true,
    'urlPrefixes' => [
        'animals' => '',
        '/info' => ['module' => 'petinfo', 'namespace' => '\app\modules\petinfo\controllers'],
        '/forum' => ['namespace' => '\app\modules\forum\controllers'], # namespace contains "\modules\"
        '/forum2' => ['path' => '@app/modules/forum2/controllers', 'namespace' => '\app\forum2\controllers'], # path contains "/modules/"
        '/api/v1' => ['path' => '@app/modules/some/controllers', 'namespace' => '\app\api\v1\controllers'],
        '/api/v2' => ['path' => '@app/modules/api/v2/controllers', 'namespace' => '\app\some\controllers'],
    ]
];
