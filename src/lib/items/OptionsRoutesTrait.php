<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

trait OptionsRoutesTrait
{
    public function getOptionsRoute():string
    {
        if (!empty($this->prefixSettings)) {
            if (isset($this->prefixSettings['module'])) {
                $prefix = $this->prefixSettings['module'];
                return static::finalOptionsRoute($prefix, $this->controllerId);
            } elseif (isset($this->prefixSettings['namespace']) && str_contains($this->prefixSettings['namespace'], '\modules\\')) { # if `module` not present then check in namespace and then in path
                $prefix = static::computeModule('\\', $this->prefixSettings['namespace']);
                if ($prefix) {
                    return static::finalOptionsRoute($prefix, $this->controllerId);
                }
            } elseif (isset($this->prefixSettings['path']) && str_contains($this->prefixSettings['path'], '/modules/')) {
                $prefix = static::computeModule('/', $this->prefixSettings['path']);
                if ($prefix) {
                    return static::finalOptionsRoute($prefix, $this->controllerId);
                }
            }
        }
        return $this->controllerId.'/options';
    }

    /**
     * @param string $separator
     * @param string $entity path or namespace
     * @return void
     */
    public static function computeModule(string $separator, string $entity): ?string
    {
        $parts = explode($separator . 'modules' . $separator, $entity); # /app/modules/forum/controllers => /forum/controllers
        if (empty($parts[1])) {
            return null;
        }
        if (str_contains($parts[1], 'controller')) {
            $result = explode($separator . 'controller', $parts[1]); // compute everything in between "modules" and "controllers" e.g. api/v1
            $result = array_map(function ($val) {
                return str_replace('\\', '/', $val);
            }, $result);
        } else {
            $result = explode($separator, $parts[1]); # forum/controllers => forum
        }
        if (empty($result[0])) {
            return null;
        }
        return $result[0];
    }

    public static function finalOptionsRoute(string $prefix, string $controllerId): string
    {
        return trim($prefix, '/') . '/' . $controllerId . '/options';
    }

//    TODO remove
//    public function getRouteInfo(): array
//    {
//        /** @var ?array $modules */
//        $modules = $controllerId = $path = $namespace = null;
//
//        if ($this->xRoute) {
//            $routeParts = explode('/', $this->xRoute);
//            $controllerId = $routeParts[count($routeParts)-2]; # last second part is controller ID
////            $actionId = $routeParts[count($routeParts)-1];
//            unset($routeParts[count($routeParts)-1], $routeParts[count($routeParts)-2]);
//            $modules = $routeParts;
//        }
//
//        return [
//            'modules' => $modules,
//            'controller_id' => $controllerId,
////            'action_id' => $actionId,
//            'path' => $path,
//            'namespace' => $namespace,
//        ];
//    }

//    public function getRoute(): string
//    {
//
//    }
//
//    public function getPath(): string
//    {
//
//    }
//
//    public function getNamespace(): string
//    {
//
//    }
}
