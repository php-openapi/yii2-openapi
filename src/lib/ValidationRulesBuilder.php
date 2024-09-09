<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace phpopenapi\yii2openapi\lib;

use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\ValidationRule;
use yii\helpers\VarDumper;
use yii\validators\DateValidator;
use function count;
use function implode;
use function in_array;
use function preg_match;
use function strtolower;

class ValidationRulesBuilder
{
    /**
     * @var \phpopenapi\yii2openapi\lib\items\DbModel
     */
    private $model;

    /**
     * @var array|ValidationRule[]
     */
    private $rules = [];

    private $typeScope = [
        'required' => [],
        'ref' => [],
        'trim' => [],
        'safe' => [],
    ];

    public function __construct(DbModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return array|\phpopenapi\yii2openapi\lib\items\ValidationRule[]
     */
    public function build():array
    {
        $this->prepareTypeScope();

        if (!empty($this->typeScope['trim'])) {
            $this->rules['trim'] = new ValidationRule($this->typeScope['trim'], 'trim');
        }

        if (!empty($this->typeScope['required'])) {
            $this->rules['required'] = new ValidationRule($this->typeScope['required'], 'required');
        }
        if (!empty($this->typeScope['ref'])) {
            $this->addExistRules($this->typeScope['ref']);
        }
        foreach ($this->model->indexes as $index) {
            if ($index->isUnique) {
                $this->addUniqueRule($index->columns);
            }
        }
        foreach ($this->model->attributes as $attribute) {
            // column/field/property with name `id` is considered as Primary Key by this library and it is automatically handled by DB/Yii; so remove it from validation `rules()`
            if (in_array($attribute->columnName, ['id', $this->model->pkName]) ||
                in_array($attribute->propertyName, ['id', $this->model->pkName])
            ) {
                continue;
            }
            $this->resolveAttributeRules($attribute);
        }

        if (!empty($this->typeScope['safe'])) {
            $this->rules['safe'] = new ValidationRule($this->typeScope['safe'], 'safe');
        }
        return $this->rules;
    }

    private function addUniqueRule(array $columns):void
    {
        $params = count($columns) > 1 ? ['targetAttribute' => $columns] : [];
        $this->rules[implode('_', $columns) . '_unique'] = new ValidationRule($columns, 'unique', $params);
    }

    private function resolveAttributeRules(Attribute $attribute):void
    {
        if ($attribute->isReadOnly()) {
            return;
        }
        if ($attribute->phpType === 'bool' || $attribute->phpType === 'boolean') {
            $this->rules[$attribute->columnName . '_boolean'] = new ValidationRule([$attribute->columnName], 'boolean');
            $this->defaultRule($attribute);
            return;
        }

        if (in_array($attribute->dbType, ['time', 'date', 'datetime'], true)) {
            $key = $attribute->columnName . '_' . $attribute->dbType;
            $params = [];
            if ($attribute->dbType === 'date') {
                $params['format'] = 'php:Y-m-d';
            }
            if ($attribute->dbType === 'datetime') {
                $params['format'] = 'php:Y-m-d H:i:s';
            }
            if ($attribute->dbType === 'time') {
                $params['format'] = 'php:H:i:s';
            }

            $this->rules[$key] = new ValidationRule([$attribute->columnName], $attribute->dbType, $params);
            $this->defaultRule($attribute);
            return;
        }

        if (in_array($attribute->phpType, ['int', 'integer', 'double', 'float']) && !$attribute->isReference()) {
            $this->addNumericRule($attribute);
            $this->defaultRule($attribute);
            return;
        }
        if ($attribute->phpType === 'string' && !$attribute->isReference()) {
            $this->addStringRule($attribute);
        }
        if (!empty($attribute->enumValues)) {
            $key = $attribute->columnName . '_in';
            $this->rules[$key] =
                new ValidationRule([$attribute->columnName], 'in', ['range' => $attribute->enumValues]);
            $this->defaultRule($attribute);
            return;
        }
        $this->defaultRule($attribute);
        $this->addRulesByAttributeName($attribute);
    }

    private function addRulesByAttributeName(Attribute $attribute):void
    {
        //@TODO: probably also patterns for file, image
        $patterns = [
            '~e?mail~i' => 'email',
            '~(url|site|website|href|link)~i' => 'url',
        ];
        foreach ($patterns as $pattern => $validator) {
            if (empty($attribute->reference) # ignore column name based rules in case of reference/relation # https://github.com/cebe/yii2-openapi/issues/159
                && preg_match($pattern, strtolower($attribute->columnName))) {
                $key = $attribute->columnName . '_' . $validator;
                $this->rules[$key] = new ValidationRule([$attribute->columnName], $validator);
                return;
            }
        }
    }

    /**
     * @param array|Attribute[] $relations
     */
    private function addExistRules(array $relations):void
    {
        foreach ($relations as $attribute) {
            if ($attribute->phpType === 'int' || $attribute->phpType === 'integer') {
                $this->addNumericRule($attribute);
            } elseif ($attribute->phpType === 'string') {
                $this->addStringRule($attribute);
            }
            $this->rules[$attribute->columnName . '_exist'] = new ValidationRule(
                [$attribute->columnName],
                'exist',
                ['targetRelation' => $attribute->camelName()]
            );
        }
    }

    private function addStringRule(Attribute $attribute):void
    {
        $params = [];
        if ($attribute->maxLength === $attribute->minLength && $attribute->minLength !== null) {
            $params['length'] = $attribute->minLength;
        } else {
            if ($attribute->minLength !== null) {
                $params['min'] = $attribute->minLength;
            }
            if ($attribute->maxLength !== null) {
                $params['max'] = $attribute->maxLength;
            }
        }
        $key = $attribute->columnName . '_string';
        $this->rules[$key] = new ValidationRule([$attribute->columnName], 'string', $params);
    }

    private function defaultRule(Attribute $attribute):void
    {
        if ($attribute->defaultValue === null) {
            return;
        }
        if ($attribute->defaultValue instanceof \yii\db\Expression) {
            return;
        }

        $params = [];
        $params['value'] = $attribute->defaultValue;
        $key = $attribute->columnName . '_default';
        $this->rules[$key] = new ValidationRule([$attribute->columnName], 'default', $params);
    }

    private function addNumericRule(Attribute $attribute):void
    {
        $params = [];
        if ($attribute->limits['min'] !== null) {
            $params['min'] = $attribute->limits['min'];
        }
        if ($attribute->limits['max'] !== null) {
            $params['max'] = $attribute->limits['max'];
        }
        $validator = ($attribute->phpType === 'int' || $attribute->phpType === 'integer') ? 'integer' : 'double';
        $key = $attribute->columnName . '_' . $validator;
        $this->rules[$key] = new ValidationRule([$attribute->columnName], $validator, $params);
    }

    private function prepareTypeScope():void
    {
        foreach ($this->model->attributes as $attribute) {
            /** @var $attribute \phpopenapi\yii2openapi\lib\items\Attribute */
            if ($attribute->isReadOnly()) {
                continue;
            }
            // column/field/property with name `id` is considered as Primary Key by this library and it is automatically handled by DB/Yii; so remove it from validation `rules()`
            if (in_array($attribute->columnName, ['id', $this->model->pkName]) ||
                in_array($attribute->propertyName, ['id', $this->model->pkName])
            ) {
                continue;
            }
            if (/*$attribute->defaultValue === null &&*/ $attribute->isRequired()) {
                $this->typeScope['required'][$attribute->columnName] = $attribute->columnName;
            }

            if ($attribute->phpType === 'string' &&
                empty($attribute->enumValues) # don't apply trim on enum columns # https://github.com/cebe/yii2-openapi/issues/158
            ) {
                $this->typeScope['trim'][$attribute->columnName] = $attribute->columnName;
            }

            if ($attribute->isReference()) {
                $this->typeScope['ref'][] = $attribute;
                continue;
            }

            if (in_array($attribute->phpType, ['int', 'integer', 'string', 'bool', 'boolean', 'float', 'double'])) {
                continue;
            }

            $this->typeScope['safe'][$attribute->columnName] = $attribute->columnName;
        }
    }
}
