<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET hi' => 'greet/default',
    'GET bye' => 'bye/list',
    'POST bye' => 'bye/create',
    'GET abc/task/<id:\d+>' => 'abc/task/view',
    'hi' => 'greet/options',
    'bye' => 'bye/options',
    'abc/task/<id:\d+>' => 'abc/task/options',
];
