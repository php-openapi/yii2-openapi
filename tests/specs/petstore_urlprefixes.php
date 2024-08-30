<?php

return [
    'openApiPath' => '@specs/petstore_urlprefixes.yaml',
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
        '/info' => ['module' =>'petinfo','namespace' => '\app\modules\petinfo\controllers'],
        '/fgh' => ['namespace' => '\app\modules\fgh\controllers'],
        '/fgh2' => ['path' => '@app/modules/fgh2/controllers', 'namespace' => '\app\fgh2\controllers'],
        '/api/v1' => ['path' => '@app/modules/api/v1/controllers', 'namespace' => '\app\api\v1\controllers']
    ]
];
