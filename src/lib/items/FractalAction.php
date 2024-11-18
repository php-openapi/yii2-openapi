<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use Yii;
use yii\base\BaseObject;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function explode;
use function in_array;
use function strpos;

/**
 * @property-read string      $actionMethodName
 * @property-read string      $transformerName
 * @property-read string      $templateId
 * @property-read null|array  $template
 * @property-read string      $parameterList
 * @property-read null|string $implementation
 * @property-read string      $baseModelName
 * @property-read string      $findModelMethodName
 * @property-read string      $findModelForMethodName
 * @property-read string      $optionsRoute
 * @property-read string      $route
 * @property-read array       $paramNames
 * @property-read array       $parentIdAttribute
 */
final class FractalAction extends BaseObject
{
    /**@var string* */
    public $id;

    /**@var string */
    public $urlPath;

    /**@var string */
    public $urlPattern;

    /**@var string */
    public $requestMethod;

    /**@var string */
    public $controllerId;

    /**@var string|null */
    public $modelName;

    /**@var string|null */
    public $modelFqn;

    /**@var string|null */
    public $idParam;

    /**@var array */
    public $params = [];
    /**@var string**/
    public $type;

    /**@var string|null */
    public $transformerFqn;

    /**@var string|null */
    public $parentIdParam;

    /**@var bool */
    public $singularResourceKey = false;
    /**
     * For relationships only - name of related model
     * @var string
     **/
    public $relatedModel;

    /**@var ?string */
    public $prefix;

    /**@var array */
    public $prefixSettings = [];

    public $expectedRelations = [];

    private $templateFactory;

    private function templateFactory():FractalActionTemplates
    {
        if (!$this->templateFactory) {
            $this->templateFactory = Yii::createObject(FractalActionTemplates::class, [$this]);
        }
        return $this->templateFactory;
    }

    public function getRoute():string
    {
        if ($this->prefix && !empty($this->prefixSettings)) {
            $prefix = $this->prefixSettings['module'] ?? $this->prefix;
            return trim($prefix, '/').'/'.$this->controllerId.'/'.$this->id;
        }
        return $this->controllerId.'/'.$this->id;
    }

    public function getOptionsRoute():string
    {
        //@TODO: re-check
        if ($this->prefix && !empty($this->prefixSettings)) {
            $prefix = $this->prefixSettings['module'] ?? $this->prefix;
            return trim($prefix, '/').'/'.$this->controllerId.'/options';
        }
        return $this->controllerId.'/options';
    }

    public function getBaseModelName():string
    {
        return $this->modelFqn ? StringHelper::basename($this->modelFqn) : '';
    }

    public function getTransformerName():string
    {
        return $this->transformerFqn ? StringHelper::basename($this->transformerFqn) : '';
    }

    public function getParamNames():array
    {
        return array_keys($this->params);
    }

    public function getParameterList():string
    {
        return implode(', ', array_map(static function ($p) {
            return "\$$p";
        }, $this->getParamNames()));
    }

    public function getTemplateId(): string
    {
        $id = $this->id;
        if (strpos($id, '-for-') !== false) {
            $id = explode('-for-', $id, 2)[0];
        }
        if (strpos($id, '-related-') !== false) {
            $id = explode('-related-', $id, 2)[0];
        }
        return Inflector::variablize($id.'-'.$this->type);
    }

    public function getActionMethodName():string
    {
        return 'action' . Inflector::id2camel($this->id);
    }

    public function getFindModelMethodName():string
    {
        return 'find' . $this->baseModelName . 'Model';
    }

    public function hasStandardId():bool
    {
        return $this->idParam === null || $this->idParam === 'id';
    }

    public function getParentIdAttribute(): ?string
    {
        return Inflector::camel2id($this->parentIdParam, '_');
    }

    public function getResourceKey():string
    {
        $wrapper = function ($val) {
            return $this->singularResourceKey ? Inflector::singularize($val): Inflector::pluralize($val);
        };
        if ($this->type === RouteData::TYPE_RELATIONSHIP) {
            return $wrapper(Inflector::camel2id($this->relatedModel));
        }
        if ($this->modelName) {
            return $wrapper(Inflector::camel2id($this->modelName));
        }
        return $wrapper(Inflector::camel2id($this->controllerId));
    }

    public function getRelationName(): string
    {
        if (strpos($this->id, '-related-') !== false) {
            $name = explode('-related-', $this->id)[1];
            return Inflector::variablize($name);
        }
        return '';
    }

    public function getFindModelForMethodName():string
    {
        return 'find' . $this->baseModelName . 'Model';
    }


    public function hasTemplate():bool
    {
        return $this->templateFactory()->hasTemplate();
    }

    public function getTemplate():?string
    {
        return $this->templateFactory()->getTemplate();
    }

    public function shouldBeAbstract(): bool
    {
        $maybeImplemented = [RouteData::TYPE_PROFILE, RouteData::TYPE_DEFAULT, RouteData::TYPE_RESOURCE_OPERATION];
        if ((!$this->modelName || !$this->hasTemplate()) && !in_array($this->type, $maybeImplemented, true)) {
            return true;
        }
        if ($this->hasStandardId() && !in_array($this->type, $maybeImplemented, true)) {
            return false; //Default template action used
        }
        if (!$this->templateFactory()->hasImplementation()) {
            return true;
        }
        return false;
    }
    public function getImplementation():?string
    {
        return $this->templateFactory()->getImplementation();
    }

    public function shouldUseTemplate():bool
    {
        return isset($this->modelFqn) && $this->hasTemplate() && $this->hasStandardId();
    }

    public function shouldUseCustomFindModel():bool
    {
        return $this->templateFactory()->hasImplementation()
            && !$this->shouldUseTemplate()
            && in_array($this->type, [RouteData::TYPE_RESOURCE, RouteData::TYPE_COLLECTION], true);
    }

    public function shouldUseCustomFindForModel():bool
    {
        return $this->templateFactory()->hasImplementation()
            && !$this->shouldUseTemplate()
            && in_array($this->type, [RouteData::TYPE_RESOURCE_FOR, RouteData::TYPE_COLLECTION_FOR], true);
    }

    public function getIdParamType(): string
    {
        if (!isset($this->params[$this->idParam]['type'])) {
            return 'string';
        }
        return $this->params[$this->idParam]['type'] === 'integer' ? 'int' : 'string';
    }
}
