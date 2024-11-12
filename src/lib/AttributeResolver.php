<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\exceptions\InvalidDefinitionException;
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\AttributeRelation;
use cebe\yii2openapi\lib\items\DbIndex;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\JunctionSchemas;
use cebe\yii2openapi\lib\items\ManyToManyRelation;
use cebe\yii2openapi\lib\items\NonDbRelation;
use cebe\yii2openapi\lib\openapi\ComponentSchema;
use cebe\yii2openapi\lib\openapi\PropertySchema;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function explode;
use function strpos;
use function strtolower;

class AttributeResolver
{
    /**
     * @var Attribute[]|array
     */
    private array $attributes = [];

    /**
     * @var AttributeRelation[]|array
     */
    public array $relations = [];

    /**
     * @var NonDbRelation[]|array
     */
    private array $nonDbRelations = [];
    /**
     * @var ManyToManyRelation[]|array
     */
    private array $many2many = [];

    private string $schemaName;

    private string $tableName;

    private ComponentSchema $componentSchema;

    private JunctionSchemas $junctions;

    private bool $isJunctionSchema;

    private bool $hasMany2Many;

    private ?Config $config;

    /**
     * @var AttributeRelation[]|array
     */
    public array $inverseRelations = [];

    public function __construct(string $schemaName, ComponentSchema $schema, JunctionSchemas $junctions, ?Config $config = null)
    {
        $this->schemaName = $schemaName;
        $this->componentSchema = $schema;
        $this->tableName = $schema->resolveTableName($schemaName);
        $this->junctions = $junctions;
        $this->isJunctionSchema = $junctions->isJunctionSchema($schemaName);
        $this->hasMany2Many = $junctions->hasMany2Many($schemaName);
        $this->config = $config;
    }

    /**
     * @return DbModel
     * @throws InvalidDefinitionException
     * @throws InvalidConfigException
     */
    public function resolve(): DbModel
    {
        foreach ($this->componentSchema->getProperties() as $property) {
            /** @var $property PropertySchema */

            $isRequired = $this->componentSchema->isRequiredProperty($property->getName());
            $nullableValue = $property->getProperty()->getSerializableData()->nullable ?? null;
            if ($nullableValue === false) { // see docs in README regarding NOT NULL, required and nullable
                $isRequired = true;
            }

            if ($this->isJunctionSchema) {
                $this->resolveJunctionTableProperty($property, $isRequired);
            } elseif ($this->hasMany2Many) {
                $this->resolveHasMany2ManyTableProperty($property, $isRequired);
            } else {
                $this->resolveProperty($property, $isRequired, $nullableValue);
            }
        }

        return Yii::createObject(DbModel::class, [
            [
                /** @see \cebe\openapi\spec\Schema */
                'openapiSchema' => $this->componentSchema->getSchema(),
                'pkName' => $this->componentSchema->getPkName(),
                'name' => $this->schemaName,
                'tableName' => $this->tableName,
                'description' => $this->componentSchema->getDescription(),
                'attributes' => $this->attributes,
                'relations' => $this->relations,
                'nonDbRelations' => $this->nonDbRelations,
                'many2many' => $this->many2many,
                'indexes' => $this->prepareIndexes($this->componentSchema->getIndexes()),
                //For valid primary keys for junction tables
                'junctionCols' => $this->isJunctionSchema ? $this->junctions->junctionCols($this->schemaName) : [],
                'isNotDb' => $this->componentSchema->isNonDb(),
            ],
        ]);
    }

    /**
     * @param PropertySchema $property
     * @param bool $isRequired
     * @throws InvalidDefinitionException
     * @throws InvalidConfigException
     */
    protected function resolveJunctionTableProperty(PropertySchema $property, bool $isRequired): void
    {
        if ($this->junctions->isJunctionProperty($this->schemaName, $property->getName())) {
            $junkAttribute = $this->junctions->byJunctionSchema($this->schemaName)[$property->getName()];
            $attribute = Yii::createObject(Attribute::class, [$property->getName()]);
            $attribute->setRequired($isRequired)
                ->setDescription($property->getAttr('description', ''))
                ->setReadOnly($property->isReadonly())
                ->setIsPrimary($property->isPrimaryKey())
                ->asReference($junkAttribute['relatedClassName'])
                ->setPhpType($junkAttribute['phpType'])
                ->setDbType($junkAttribute['dbType'])
                ->setForeignKeyColumnName($property->fkColName);
            $relation = Yii::createObject(AttributeRelation::class, [
                $property->getName(),
                $junkAttribute['relatedTableName'],
                $junkAttribute['relatedClassName'],
            ])->asHasOne([$junkAttribute['foreignPk'] => $attribute->columnName]);
            $this->relations[$property->getName()] = $relation;
            $this->attributes[$property->getName()] =
                $attribute->setFakerStub($this->guessFakerStub($attribute, $property));
        } else {
            $this->resolveProperty($property, $isRequired);
        }
    }

    /**
     * @param PropertySchema $property
     * @param bool $isRequired
     * @throws InvalidDefinitionException
     * @throws InvalidConfigException
     */
    protected function resolveHasMany2ManyTableProperty(PropertySchema $property, bool $isRequired): void
    {
        if ($this->junctions->isManyToManyProperty($this->schemaName, $property->getName())) {
            return;
        }
        if ($this->junctions->isJunctionRef($this->schemaName, $property->getName())) {
            $junkAttribute = $this->junctions->indexByJunctionRef()[$property->getName()][$this->schemaName];
            $junkRef = $property->getName();
            $junkProperty = $junkAttribute['property'];
            $viaModel = $this->junctions->trimPrefix($junkAttribute['junctionSchema']);

            $relation = Yii::createObject(ManyToManyRelation::class, [
                [
                    'name' => Inflector::pluralize($junkProperty),
                    'schemaName' => $this->schemaName,
                    'relatedSchemaName' => $junkAttribute['relatedClassName'],
                    'tableName' => $this->tableName,
                    'relatedTableName' => $junkAttribute['relatedTableName'],
                    'pkAttribute' => $this->attributes[$this->componentSchema->getPkName()],
                    'hasViaModel' => true,
                    'viaModelName' => $viaModel,
                    'viaRelationName' => Inflector::id2camel($junkRef, '_'),
                    'fkProperty' => $junkAttribute['pairProperty'],
                    'relatedFkProperty' => $junkAttribute['property'],
                ],
            ]);
            $this->many2many[Inflector::pluralize($junkProperty)] = $relation;

            $this->relations[Inflector::pluralize($junkRef)] =
                Yii::createObject(AttributeRelation::class, [$junkRef, $junkAttribute['junctionTable'], $viaModel])
                    ->asHasMany([$junkAttribute['pairProperty'] . '_id' => $this->componentSchema->getPkName()]);
            return;
        }

        $this->resolveProperty($property, $isRequired);
    }

    /**
     * @param PropertySchema $property
     * @param bool $isRequired
     * @param bool|null|string $nullableValue if string then its value will be only constant `ARG_ABSENT`. Default `null` is avoided because it can be in passed value in method call
     * @throws InvalidDefinitionException
     * @throws InvalidConfigException
     */
    protected function resolveProperty(
        PropertySchema $property,
        bool $isRequired,
        $nullableValue = 'ARG_ABSENT'
    ): void {
        if ($nullableValue === 'ARG_ABSENT') {
            $nullableValue = $property->getProperty()->getSerializableData()->nullable ?? null;
        }
        $attribute = Yii::createObject(Attribute::class, [$property->getName()]);

        if (!empty($property->getAttr(CustomSpecAttr::NO_RELATION))) {
            $this->attributes[$property->getName()] = $attribute->setFakerStub($this->guessFakerStub($attribute, $property));
        }

        $attribute->setRequired($isRequired)
                  ->setPhpType($property->guessPhpType())
                  ->setDescription($property->getAttr('description', ''))
                  ->setReadOnly($property->isReadonly())
                  ->setDefault($property->guessDefault())
                  ->setXDbType($property->getAttr(CustomSpecAttr::DB_TYPE))
                  ->setXDbDefaultExpression($property->getAttr(CustomSpecAttr::DB_DEFAULT_EXPRESSION))
                  ->setNullable($nullableValue)
                  ->setIsPrimary($property->isPrimaryKey())
                  ->setForeignKeyColumnName($property->fkColName)
                  ->setFakerStub($this->guessFakerStub($attribute, $property));
        if ($property->isReference()) {
            if ($property->isVirtual()) {
                throw new InvalidDefinitionException('References not supported for virtual attributes');
            }

            if ($property->isNonDbReference()) {
                $attribute->asNonDbReference($property->getRefClassName());
                $relation = Yii::createObject(
                    NonDbRelation::class,
                    [$property->getName(), $property->getRefClassName(), NonDbRelation::HAS_ONE]
                );

                $this->nonDbRelations[$property->getName()] = $relation;
                return;
            }

            $fkProperty = $property->getTargetProperty();
            if (!$fkProperty && !$property->getRefSchema()->isObjectSchema()) {
                $this->resolvePropertyRef($property, $attribute);
                return;
            }
            if (!$fkProperty) {
                return;
            }
            $relatedClassName = $property->getRefClassName();
            $relatedTableName = $property->getRefSchema()->resolveTableName($relatedClassName);
            [$min, $max] = $fkProperty->guessMinMax();
            $attribute->asReference($relatedClassName);
            $attribute->setPhpType($fkProperty->guessPhpType())
                      ->setDbType($fkProperty->guessDbType(true))
                      ->setSize($fkProperty->getMaxLength())
                      ->setDescription($property->getRefSchema()->getDescription())
                      ->setDefault($fkProperty->guessDefault())
                      ->setLimits($min, $max, $fkProperty->getMinLength())
                      ->setFakerStub($this->guessFakerStub($attribute, $property));

            $relation = Yii::createObject(
                AttributeRelation::class,
                [static::relationName($property->getName(), $property->fkColName), $relatedTableName, $relatedClassName]
            )
                ->asHasOne([$fkProperty->getName() => $attribute->columnName]);
            $relation->onUpdateFkConstraint = $property->onUpdateFkConstraint;
            $relation->onDeleteFkConstraint = $property->onDeleteFkConstraint;
            if ($property->isRefPointerToSelf()) {
                $relation->asSelfReference();
            }
            $this->relations[$property->getName()] = $relation;
            if (!$property->isRefPointerToSelf()) {
                $this->addInverseRelation($relatedClassName, $attribute, $property, $fkProperty);
            }
        }
        if (!$property->isReference() && !$property->hasRefItems()) {
            [$min, $max] = $property->guessMinMax();
            $attribute->setIsVirtual($property->isVirtual())
                ->setPhpType($property->guessPhpType())
                ->setDbType($property->guessDbType())
                ->setSize($property->getMaxLength())
                ->setLimits($min, $max, $property->getMinLength());
            if ($property->hasEnum()) {
                $attribute->setEnumValues($property->getAttr('enum'));
            }
        }

        if ($property->hasRefItems()) {
            if ($property->isVirtual()) {
                throw new InvalidDefinitionException('References not supported for virtual attributes');
            }

            if ($property->isNonDbReference()) {
                $attribute->asNonDbReference($property->getRefClassName());
                $relation = Yii::createObject(
                    NonDbRelation::class,
                    [$property->getName(), $property->getRefClassName(), NonDbRelation::HAS_MANY]
                );

                $this->nonDbRelations[$property->getName()] = $relation;
                return;
            }

            if ($property->isRefPointerToSelf()) {
                $relatedClassName = $property->getRefClassName();
                $attribute->setPhpType($relatedClassName . '[]');
                $relatedTableName = $this->tableName;
                $fkProperty = $property->getSelfTargetProperty();
                if ($fkProperty && !$fkProperty->isReference()
                    && !StringHelper::endsWith(
                        $fkProperty->getName(),
                        '_id'
                    )) {
                    $this->relations[$property->getName()] =
                        Yii::createObject(
                            AttributeRelation::class,
                            [static::relationName($property->getName(), $property->fkColName), $relatedTableName, $relatedClassName]
                        )
                            ->asHasMany([$fkProperty->getName() => $fkProperty->getName()])->asSelfReference();
                    return;
                }
                $foreignPk = Inflector::camel2id($fkProperty->getName(), '_') . '_id';
                $this->relations[$property->getName()] =
                    Yii::createObject(
                        AttributeRelation::class,
                        [static::relationName($property->getName(), $property->fkColName), $relatedTableName, $relatedClassName]
                    )
                        ->asHasMany([$foreignPk => $this->componentSchema->getPkName()]);
                return;
            }
            $relatedClassName = $property->getRefClassName();
            $relatedTableName = $property->getRefSchema()->resolveTableName($relatedClassName);
            if ($this->catchManyToMany(
                $property->getName(),
                $relatedClassName,
                $relatedTableName,
                $property->getRefSchema()
            )) {
                return;
            }
            $attribute->setPhpType($relatedClassName . '[]');
            $this->relations[$property->getName()] =
                Yii::createObject(
                    AttributeRelation::class,
                    [static::relationName($property->getName(), $property->fkColName), $relatedTableName, $relatedClassName]
                )
                    ->asHasMany([Inflector::camel2id($this->schemaName, '_') . '_id' => $this->componentSchema->getPkName()])
                    ->setInverse(Inflector::variablize($this->schemaName));
            return;
        }
        if ($this->componentSchema->isNonDb() && $attribute->isReference()) {
            $this->attributes[$property->getName()] = $attribute;
            return;
        }
        $this->attributes[$property->getName()] =
            $attribute->setFakerStub($this->guessFakerStub($attribute, $property));
    }

    /**
     * Check and register many-to-many relation
     * - property name for many-to-many relation should be equal lower-cased, pluralized schema name
     * - referenced schema should contain mirrored reference to current schema
     * @param string $propertyName
     * @param string $relatedSchemaName
     * @param string $relatedTableName
     * @param ComponentSchema $refSchema
     * @return bool
     * @throws InvalidConfigException|InvalidDefinitionException
     */
    protected function catchManyToMany(
        string $propertyName,
        string $relatedSchemaName,
        string $relatedTableName,
        ComponentSchema $refSchema
    ): bool {
        if (strtolower(Inflector::id2camel($propertyName, '_'))
            !== strtolower(Inflector::pluralize($relatedSchemaName))) {
            return false;
        }
        $expectedPropertyName = strtolower(Inflector::pluralize(Inflector::camel2id($this->schemaName, '_')));
        if (!$refSchema->hasProperty($expectedPropertyName)) {
            return false;
        }
        $refProperty = $refSchema->getProperty($expectedPropertyName);
        if (!$refProperty) {
            return false;
        }
        $refClassName = $refProperty->hasRefItems() ? $refProperty->getRefSchemaName() : null;
        if ($refClassName !== $this->schemaName) {
            return false;
        }
        $relation = Yii::createObject(ManyToManyRelation::class, [
            [
                'name' => $propertyName,
                'schemaName' => $this->schemaName,
                'relatedSchemaName' => $relatedSchemaName,
                'tableName' => $this->tableName,
                'relatedTableName' => $relatedTableName,
                'pkAttribute' => $this->attributes[$this->componentSchema->getPkName()],
            ],
        ]);
        $this->many2many[$propertyName] = $relation;
        return true;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function guessFakerStub(Attribute $attribute, PropertySchema $property): ?string
    {
        $resolver = Yii::createObject(['class' => FakerStubResolver::class], [$attribute, $property, $this->config]);
        return $resolver->resolve();
    }

    /**
     * @param array $indexes
     * @return array|DbIndex[]
     * @throws InvalidDefinitionException
     */
    protected function prepareIndexes(array $indexes): array
    {
        $dbIndexes = [];
        foreach ($indexes as $index) {
            $unique = false;
            if (strpos($index, ':') !== false) {
                // [$indexType, $props] = explode(':', $index);
                // if `$index` is `gin(to_tsvector('english', search::text)):search,prop2`
                $props = strrchr($index, ':'); # `$props` is now `:search,prop2`
                $props = substr($props, 1); # search,prop2
                $indexType = str_replace(':'.$props, '', $index); # `gin(to_tsvector('english', search::text))`
            } else {
                $props = $index;
                $indexType = null;
            }
            if (strtolower((string) $indexType) === 'unique') {
                $indexType = null;
                $unique = true;
            }
            $props = array_map('trim', explode(',', trim($props)));
            $columns = [];
            $xFkColumnNames = [];
            foreach ($this->attributes as $key => $value) {
                if (!empty($value->fkColName)) {
                    $xFkColumnNames[$value->fkColName] = $key;
                }
            }
            foreach ($props as $prop) {
                // for more info see test tests/specs/fk_col_name/fk_col_name.yaml
                // File: ForeignKeyColumnNameTest::testIndexForColumnWithCustomName
                // first check direct column names
                if (!isset($this->attributes[$prop])) {
                    // then check x-fk-column-name
                    if (!in_array($prop, array_keys($xFkColumnNames))) {
                        // then check relations/reference e.g. `user`/`user_id`
                        $refPropName = (substr($prop, -3) === '_id') ? rtrim($prop, '_id') : null;
                        if ($refPropName && !isset($this->attributes[$refPropName])) {
                            throw new InvalidDefinitionException('Invalid index definition - property ' . $prop
                                . ' not declared');
                        } else {
                            $prop = $refPropName;
                        }
                    } else {
                        $prop = $xFkColumnNames[$prop];
                    }
                }
                $columns[] = $this->attributes[$prop]->columnName;
            }
            $dbIndex = DbIndex::make($this->tableName, $columns, $indexType, $unique);
            $dbIndexes[$dbIndex->name] = $dbIndex;
        }
        return $dbIndexes;
    }

    /**
     * @param PropertySchema $property
     * @param Attribute $attribute
     * @return void
     * @throws InvalidConfigException|InvalidDefinitionException
     */
    protected function resolvePropertyRef(PropertySchema $property, Attribute $attribute): void
    {
        $fkProperty = new PropertySchema(
            $property->getRefSchema()->getSchema(),
            $property->getName(),
            $this->componentSchema
        );
        [$min, $max] = $fkProperty->guessMinMax();
        $attribute->setPhpType($fkProperty->guessPhpType())
            ->setDbType($fkProperty->guessDbType(true))
            ->setSize($fkProperty->getMaxLength())
            ->setDescription($fkProperty->getAttr('description'))
            ->setDefault($fkProperty->guessDefault())
            ->setLimits($min, $max, $fkProperty->getMinLength());
        $this->attributes[$property->getName()] =
            $attribute->setFakerStub($this->guessFakerStub($attribute, $fkProperty));
    }

    public static function relationName(string $propertyName, ?string $fkColumnName): string
    {
        $fkColumnName = (string) $fkColumnName;
        $relationName = $propertyName;
        if (!str_contains($fkColumnName, '_')) {
            $relationName = strtolower($fkColumnName) === strtolower($relationName) ? $relationName . 'Rel' : $relationName;
        }
        return $relationName;
    }

    /**
     * @throws InvalidConfigException
     */
    public function addInverseRelation(
        string $relatedClassName,
        Attribute $attribute,
        PropertySchema $property,
        PropertySchema $fkProperty
    ): void {
        $inverseRelation = Yii::createObject(
            AttributeRelation::class,
            [$this->schemaName, $this->tableName, $this->schemaName]
        )
            ->asHasOne([$attribute->columnName => $fkProperty->getName()]);
        $inverseRelation->setInverse($property->getName());
        $this->inverseRelations[$relatedClassName][] = $inverseRelation;
    }
}
