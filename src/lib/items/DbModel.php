<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use cebe\yii2openapi\lib\helpers\FormatHelper;
use cebe\yii2openapi\lib\ValidationRulesBuilder;
use Yii;
use yii\base\BaseObject;
use yii\db\ColumnSchema;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use function array_filter;
use function array_map;
use function str_replace;
use const PHP_EOL;

/**
 * @property-read string                                                $tableAlias
 * @property-read array                                                 $uniqueColumnsList
 * @property-read array[]|array                                         $attributesByType
 * @property-read array|\cebe\yii2openapi\lib\items\AttributeRelation[] $hasOneRelations
 */
class DbModel extends BaseObject
{
    /**
     * @var \cebe\openapi\spec\Schema
    */
    public $openapiSchema;

    /**
     * @var string primary key attribute name
     */
    public $pkName;

    /**
     * @var string model name.
     */
    public $name;

    /**
     * @var string table name. (without brackets and db prefix)
     */
    public $tableName;

    /**
     * @var string description from the schema.
     */
    public $description;

    /**
     * @var array|\cebe\yii2openapi\lib\items\Attribute[] model attributes.
     */
    public $attributes = [];

    /**
     * @var array|\cebe\yii2openapi\lib\items\AttributeRelation[] database relations.
     */
    public $relations = [];

    /***
     * @var array|\cebe\yii2openapi\lib\items\NonDbRelation[] non-db relations
     */
    public $nonDbRelations = [];

    /**
     * @var array|\cebe\yii2openapi\lib\items\ManyToManyRelation[] many to many relations.
     */
    public $many2many = [];

    public $junctionCols = [];

    /**
     * @var \cebe\yii2openapi\lib\items\DbIndex[]|array
     */
    public $indexes = [];

    public $isNotDb = false;

    /**
     * @var array Automatically generated scenarios from the model 'x-scenarios'.
     */
    private array $scenarios;

    /**
     * @var string
     * Here, you can set your own default description for the scenario.
     * You can use the {name} attribute from the schema for the YAML model.
     */
    public string $scenarioDefaultDescription = " Scenario {name}";

    public function getTableAlias():string
    {
        return '{{%' . $this->tableName . '}}';
    }

    public function getClassName():string
    {
        return Inflector::id2camel($this->name, '_');
    }

    public function getValidationRules():string
    {
        $rules = Yii::createObject(ValidationRulesBuilder::class, [$this])->build();
        $rules = array_map('strval', $rules);
        $rules = VarDumper::export($rules);
        return str_replace([
            PHP_EOL,
            "\'",
            "'[[",
            "]',"
        ], [
            PHP_EOL . '        ',
            "'",
            '[[',
            '],'
        ], $rules);
    }

    /**
     * @return \cebe\yii2openapi\lib\items\AttributeRelation[]|array
     */
    public function getHasOneRelations():array
    {
        return array_filter(
            $this->relations,
            static function (AttributeRelation $relation) {
                return $relation->isHasOne();
            }
        );
    }

    public function getPkAttribute():Attribute
    {
        return $this->attributes[$this->pkName];
    }

    /**
     * @return ColumnSchema[]
     */
    public function attributesToColumnSchema():array
    {
        return $this->isNotDb
            ? []
            : array_reduce(
                $this->attributes,
                static function ($acc, Attribute $attribute) {
                    if (!$attribute->isVirtual) {
                        $acc[$attribute->columnName] = $attribute->toColumnSchema();
                    }
                    return $acc;
                },
                []
            );
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\Attribute[]
     */
    public function getEnumAttributes():array
    {
        return array_filter(
            $this->attributes,
            static function (Attribute $attribute) {
                return !$attribute->isVirtual && !empty($attribute->enumValues);
            }
        );
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\Attribute[]
     */
    public function virtualAttributes():array
    {
        return array_filter($this->attributes, static function (Attribute $attribute) {
            return $attribute->isVirtual;
        });
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\Attribute[]
     */
    public function dbAttributes():array
    {
        return array_filter($this->attributes, static function (Attribute $attribute) {
            return !$attribute->isVirtual;
        });
    }

    /**
     * @return array
     */
    public function getScenarios(): array
    {
        if (isset($this->scenarios)) {
            return $this->scenarios;
        }
        $this->scenarios = $this->getScenariosByOpenapiSchema();
        return $this->scenarios;
    }

    /**
     * @return array
     */
    private function getScenariosByOpenapiSchema(): array
    {
        $x_scenarios = $this->openapiSchema->{'x-scenarios'} ?? [];
        if (empty($x_scenarios) || !is_array($x_scenarios)) {
            return [];
        }

        $uniqueNames = [];
        $scenarios = array_filter($x_scenarios, function ($scenario) use (&$uniqueNames) {
            $name = $scenario['name'] ?? '';

            // Check if the name is empty, already used, or does not meet the criteria
            if (
                empty($name) ||
                in_array($name, $uniqueNames) ||
                !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)
            ) {
                return false; // Exclude this item
            }

            // Add the name to the uniqueNames array and keep the item
            $uniqueNames[] = $name;
            return true;
        });

        foreach ($scenarios as $key => $scenario) {
            $scenarios[$key]['const'] = 'SCENARIO_' . strtoupper(implode('_', preg_split('/(?=[A-Z])/', $scenario['name'])));
            $scenarios[$key]['description'] = FormatHelper::getFormattedDescription(
            !empty($scenario['description']) ?
                $scenario['description']
                : str_replace('{name}', $scenario['name'], $this->scenarioDefaultDescription
            ),
            5);
        }

        return $scenarios;
    }

    /**
     * @return string
     */
    public function getModelClassDescription(): string
    {
        if (empty($this->description)) {
            return ' This is the model class for table "'.$this->tableName.'".';
        }
        return FormatHelper::getFormattedDescription($this->description);
    }
}
