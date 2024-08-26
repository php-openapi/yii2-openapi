<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use cebe\yii2openapi\lib\ValidationRulesBuilder;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\ColumnSchema;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use function array_filter;
use function array_map;
use function str_replace;
use const PHP_EOL;

/**
 * @property-read string $tableAlias
 * @property-read array $uniqueColumnsList
 * @property-read array[]|array $attributesByType
 * @property-read array|AttributeRelation[] $hasOneRelations
 */
class DbModel extends BaseObject
{
    /**
     * @var string primary key attribute name
     */
    public $pkName;

    // model name
    public string $name;

    // table name. (without brackets and db prefix)
    public string $tableName;

    // description from the schema.
    public string $description;

    /**
     * @var array|Attribute[] model attributes.
     */
    public array $attributes = [];

    /**
     * @var array|AttributeRelation[] database relations.
     */
    public array $relations = [];

    /**
     * @var array|NonDbRelation[] non-db relations
     */
    public array $nonDbRelations = [];

    /**
     * @var array|ManyToManyRelation[] many-to-many relations.
     */
    public array $many2many = [];

    /**
     * @var array|AttributeRelation[] inverse relations
     */
    public array $inverseRelations = [];

    public array $junctionCols = [];

    /**
     * @var DbIndex[]|array
     */
    public array $indexes = [];

    public bool $isNotDb = false;

    public function getTableAlias(): string
    {
        return '{{%' . $this->tableName . '}}';
    }

    public function getClassName(): string
    {
        return Inflector::id2camel($this->name, '_');
    }

    /**
     * @throws InvalidConfigException
     */
    public function getValidationRules(): string
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
     * @return AttributeRelation[]|array
     */
    public function getHasOneRelations(): array
    {
        return array_filter(
            $this->relations,
            static function (AttributeRelation $relation) {
                return $relation->isHasOne();
            }
        );
    }

    public function getPkAttribute(): Attribute
    {
        return $this->attributes[$this->pkName];
    }

    /**
     * @return ColumnSchema[]
     */
    public function attributesToColumnSchema(): array
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
     * @return array|Attribute[]
     */
    public function getEnumAttributes(): array
    {
        return array_filter(
            $this->attributes,
            static function (Attribute $attribute) {
                return !$attribute->isVirtual && !empty($attribute->enumValues);
            }
        );
    }

    /**
     * @return array|Attribute[]
     */
    public function virtualAttributes(): array
    {
        return array_filter($this->attributes, static function (Attribute $attribute) {
            return $attribute->isVirtual;
        });
    }

    /**
     * @return array|Attribute[]
     */
    public function dbAttributes(): array
    {
        return array_filter($this->attributes, static function (Attribute $attribute) {
            return !$attribute->isVirtual;
        });
    }
}
