<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\generators;

use cebe\openapi\exceptions\IOException;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Reference;
use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\items\RestAction;
use cebe\yii2openapi\lib\items\RouteData;
use cebe\yii2openapi\lib\openapi\ResponseSchema;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;

class RestActionGenerator
{
    /**
     * @var Config
     */
    protected $config;

    protected $knownModelClasses = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array|RestAction[]
     * @throws IOException
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     * @throws InvalidConfigException
     */
    public function generate(): array
    {
        $actions = [];
        foreach ($this->config->getOpenApi()->paths as $path => $pathItem) {
            if ($path[0] !== '/') {
                throw new InvalidConfigException('Path must begin with /');
            }
            if ($pathItem === null) {
                continue;
            }
            if ($pathItem instanceof Reference) {
                $pathItem = $pathItem->resolve();
            }
            $actions[] = $this->resolvePath($path, $pathItem);
        }
        return array_merge(...$actions);
    }

    /**
     * @param string $path
     * @param PathItem $pathItem
     * @return array|RestAction[]
     * @throws UnresolvableReferenceException
     * @throws InvalidConfigException
     */
    protected function resolvePath(string $path, PathItem $pathItem): array
    {
        $actions = [];

        $routeData = Yii::createObject(RouteData::class, [$pathItem, $path, $this->config->urlPrefixes]);
        foreach ($pathItem->getOperations() as $method => $operation) {
            $actions[] = $this->prepareAction($method, $operation, $routeData);
        }
        return $actions;
    }

    /**
     * @param string $method
     * @param Operation $operation
     * @param RouteData $routeData
     * @return RestAction|object
     * @throws UnresolvableReferenceException
     * @throws InvalidConfigException
     */
    protected function prepareAction(string $method, Operation $operation, RouteData $routeData): BaseObject
    {
        $actionType = $this->resolveActionType($routeData, $method);
        $modelClass = ResponseSchema::guessModelClass($operation, $actionType);
        $responseWrapper = ResponseSchema::findResponseWrapper($operation, $modelClass);
        // fallback to known model class on same URL
        if ($modelClass === null && isset($this->knownModelClasses[$routeData->path])) {
            $modelClass = $this->knownModelClasses[$routeData->path];
        } else {
            $this->knownModelClasses[$routeData->path] = $modelClass;
        }

        if ($routeData->isRelationship()) {
            $controllerId = $routeData->controller;
            $modelClass = Inflector::id2camel(Inflector::singularize($controllerId));
            $controllerId = isset($this->config->controllerModelMap[$modelClass])
                ? Inflector::camel2id($this->config->controllerModelMap[$modelClass])
                : $controllerId;
        } elseif ($modelClass !== null) {
            $controllerId = isset($this->config->controllerModelMap[$modelClass])
                ? Inflector::camel2id($this->config->controllerModelMap[$modelClass])
                : Inflector::camel2id($modelClass);
        } else {
            $controllerId = $routeData->controller;
        }
        return Yii::createObject(RestAction::class, [
            [
                'id' => trim("$actionType{$routeData->action}", '-'),
                'controllerId' => $controllerId,
                'urlPath' => $routeData->path,
                'requestMethod' => strtoupper($method),
                'urlPattern' => $routeData->pattern,
                'idParam' => $routeData->idParam ?? $routeData->parentParam ?? null,
                'params' => $routeData->params,
                'modelName' => $modelClass,
                'modelFqn' => $modelClass !== null
                    ? $this->config->modelNamespace . '\\' . Inflector::id2camel($modelClass, '_')
                    : null,
                'responseWrapper' => $responseWrapper,
                'prefix' => $routeData->getPrefix(),
                'prefixSettings' => $routeData->getPrefixSettings()
            ],
        ]);
    }

    protected function resolveActionType(RouteData $routeData, string $method): string
    {
        $actionTypes = [
            'get' => $routeData->resolveGetActionType(),
            'post' => 'create',
            'patch' => 'update',
            'put' => 'update',
            'delete' => 'delete',
        ];
        return $actionTypes[$method] ?? "http-$method";
    }
}
