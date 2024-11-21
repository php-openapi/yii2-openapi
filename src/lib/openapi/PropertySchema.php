<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\openapi;

use BadMethodCallException;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
use cebe\yii2openapi\generator\ApiGenerator;
use cebe\yii2openapi\lib\CustomSpecAttr;
use cebe\yii2openapi\lib\exceptions\InvalidDefinitionException;
use cebe\yii2openapi\lib\traits\ForeignKeyConstraints;
use SamIT\Yii2\MariaDb\Schema as MariaDbSchema;
use Throwable;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ColumnSchema;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\db\Schema as YiiDbSchema;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use function is_int;
use function strpos;

class PropertySchema
{
    use ForeignKeyConstraints;

    public const REFERENCE_PATH = '/components/schemas/';
    public const REFERENCE_PATH_LEN = 20;

    /**
     * @var string
     * Contains foreign key column name
     * @example 'redelivery_of'
     * See usage docs in README for more info
     */
    public $fkColName;

    /**
     * @var null|bool|string
     * If `false`, no faker will be generated in faker model
     * See more about usage in README.md file present in root directory of this library
     */
    public $xFaker;

    /**
     * @var \cebe\openapi\SpecObjectInterface
     */
    private $property;

    /** @var string* */
    private $name;

    /** @var bool $isReference * */
    private $isReference = false;

    /** @var bool $isItemsReference * */
    private $isItemsReference = false;

    /** @var bool $isNonDbReference * */
    private $isNonDbReference = false;

    /** @var string $refPointer */
    private $refPointer;

    /** @var \cebe\yii2openapi\lib\openapi\ComponentSchema $refSchema */
    private $refSchema;

    /**
     * @var bool
     */
    private $isPk;

    /**
     * @var \cebe\yii2openapi\lib\openapi\ComponentSchema
     */
    private $schema;

    /**
     * @param \cebe\openapi\SpecObjectInterface             $property
     * @param string                                        $name
     * @param \cebe\yii2openapi\lib\openapi\ComponentSchema $schema
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct(SpecObjectInterface $property, string $name, ComponentSchema $schema)
    {
        $this->name = $name;
        $this->property = $property;
        $this->schema = $schema;
        $this->isPk = $name === $schema->getPkName();

        $onUpdate = $onDelete = $xFaker = $reference = $fkColName = null;

        foreach ($property->allOf ?? [] as $element) {
            // x-fk-on-delete | x-fk-on-update
            if (!empty($element->{CustomSpecAttr::FK_ON_UPDATE})) {
                $onUpdate = $element->{CustomSpecAttr::FK_ON_UPDATE};
            }
            if (!empty($element->{CustomSpecAttr::FK_ON_DELETE})) {
                $onDelete = $element->{CustomSpecAttr::FK_ON_DELETE};
            }

            if (isset($element->{CustomSpecAttr::FAKER})) {
                $xFaker = $element->{CustomSpecAttr::FAKER};
            }

            if ($element instanceof Reference) {
                $reference = $element;
            }

            // x-fk-column-name
            if (!empty($element->{CustomSpecAttr::FK_COLUMN_NAME})) {
                $fkColName = $element->{CustomSpecAttr::FK_COLUMN_NAME};
            }
        }

        if (
            ($onUpdate !== null || $onDelete !== null) &&
            ($reference instanceof Reference)
        ) {
            $this->onUpdateFkConstraint = $onUpdate;
            $this->onDeleteFkConstraint = $onDelete;
            $this->property = $reference;
            $property = $this->property;
        } elseif (
            ($fkColName !== null) &&
            ($reference instanceof Reference)
        ) {
            $this->fkColName = $fkColName;
            $this->property = $reference;
            $property = $this->property;
        } elseif ($xFaker !== null && $reference instanceof Reference) {
            $this->xFaker = $xFaker;
            $this->property = $reference;
            $property = $this->property;
        }

        if ($property instanceof Reference) {
            $this->initReference();
        } elseif (
            isset($property->type, $property->items) && $property->type === 'array'
            && $property->items instanceof Reference
        ) {
            $this->initItemsReference();
        }
    }

    /**
     * @return bool
     */
    public function isNonDbReference():bool
    {
        return $this->isNonDbReference;
    }

    /**
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     * @throws \yii\base\InvalidConfigException
     */
    private function initReference():void
    {
        $this->isReference = true;
        $this->refPointer = $this->property->getJsonReference()->getJsonPointer()->getPointer();
        $refSchemaName = $this->getRefSchemaName();
        if ($this->isRefPointerToSelf()) {
            $this->refSchema = $this->schema;
        } elseif ($this->isRefPointerToSchema()) {
            $this->property->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $this->refSchema = Yii::createObject(ComponentSchema::class, [$this->property->resolve(), $refSchemaName]);
        }
        if ($this->refSchema && $this->refSchema->isNonDb()) {
            $this->isNonDbReference = true;
        }
    }

    /**
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     * @throws \yii\base\InvalidConfigException
     */
    private function initItemsReference():void
    {
        $this->isItemsReference = true;
        $items = $this->property->items ?? null;
        if (!$items) {
            return;
        }
        $this->refPointer = $items->getJsonReference()->getJsonPointer()->getPointer();
        if ($this->isRefPointerToSelf()) {
            $this->refSchema = $this->schema;
        } elseif ($this->isRefPointerToSchema()) {
            $items->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $this->refSchema = Yii::createObject(ComponentSchema::class, [$items->resolve(), $this->getRefSchemaName()]);
        }
        if ($this->refSchema && $this->refSchema->isNonDb()) {
            $this->isNonDbReference = true;
        }
    }

    public function setName(string $name):void
    {
        $this->name = $name;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function isPrimaryKey():bool
    {
        return $this->isPk;
    }

    public function getProperty():SpecObjectInterface
    {
        return $this->property;
    }

    public function getRefPointer(): string
    {
        return $this->refPointer ?? '';
    }

    public function getRefSchema():ComponentSchema
    {
        if (!$this->isReference && !$this->isItemsReference) {
            throw new BadMethodCallException('Schema is not reference');
        }
        return $this->refSchema;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function getTargetProperty():?PropertySchema
    {
        return $this->getRefSchema()->getProperty($this->getRefSchema()->getPkName());
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function getSelfTargetProperty():?PropertySchema
    {
        if (!$this->isRefPointerToSelf()) {
            return null;
        }
        $propName = str_replace(
            self::REFERENCE_PATH . $this->getRefClassName() . '/properties/',
            '',
            $this->refPointer
        );
        return $this->getRefSchema()->getProperty($propName);
    }

    public function isRefPointerToSchema():bool
    {
        return $this->refPointer && strpos($this->refPointer, self::REFERENCE_PATH) === 0;
    }

    public function isRefPointerToSelf():bool
    {
        return $this->isRefPointerToSchema()
            && strpos($this->refPointer, '/' . $this->schema->getName() . '/') !== false
            && strpos($this->refPointer, '/properties/') !== false;
    }

    public function getRefSchemaName():string
    {
        if (!$this->isReference && !$this->isItemsReference) {
            throw new BadMethodCallException('Property should be a reference or contains items with reference');
        }
        $pattern = strpos($this->refPointer, '/properties/') !== false ?
            '~^'.self::REFERENCE_PATH.'(?<schemaName>.+)/properties/(?<propName>.+)$~'
            : '~^'.self::REFERENCE_PATH.'(?<schemaName>.+)$~';
        if (!\preg_match($pattern, $this->refPointer, $matches)) {
            throw new InvalidDefinitionException('Invalid schema reference');
        }
        return $matches['schemaName'];
    }

    public function getRefClassName():string
    {
        return Inflector::id2camel($this->getRefSchemaName(), '_');
    }

    public function getAttr(string $attrName, $default = null)
    {
        return $this->property->$attrName ?? $default;
    }

    public function hasAttr(string $attrName):bool
    {
        return isset($this->property->$attrName);
    }

    public function isReference():bool
    {
        return $this->isReference;
    }

    public function hasItems():bool
    {
        return !$this->isReference && isset($this->property->items, $this->property->type)
            && $this->property->type === 'array';
    }

    public function hasRefItems():bool
    {
        return $this->isItemsReference;
    }

    public function hasEnum():bool
    {
        if ($this->isReference) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        return isset($this->property->enum) && is_array($this->property->enum);
    }

    public function isVirtual():bool
    {
        return isset($this->property->{CustomSpecAttr::DB_TYPE})
            && $this->property->{CustomSpecAttr::DB_TYPE} === false;
    }

    public function guessMinMax():array
    {
        $min = $this->getAttr('minimum');
        $max = $this->getAttr('maximum');
        $exclusiveMin = $this->getAttr('exclusiveMinimum', false);
        $exclusiveMax = $this->getAttr('exclusiveMaximum', false);
        /**
         * @see OpenApi v.3.0 and v3.1 difference for exclusiveMinimum and exclusiveMaximum
         * https://apisyouwonthate.com/blog/openapi-v31-and-json-schema
         * (both variants supported)
         */
        if (is_int($exclusiveMin)) {
            $min = $exclusiveMin;
        }
        if (is_int($exclusiveMax)) {
            $max = $exclusiveMax;
        }
        if ($min !== null && $exclusiveMin === true) {
            $min++;
        }
        if ($max !== null && $exclusiveMax === true) {
            $max--;
        }

        return [$min, $max];
    }

    public function getMaxLength():?int
    {
        $ml = $this->getAttr('maxLength');

        // if x-db-type is set and maxLength is not set then check for maxlength in x-db-type
        // e.g. varchar(17) => 17
        if ($ml === null) {
            if (!empty($this->property->{CustomSpecAttr::DB_TYPE})) {
                $regex = '/(\w+)(\()([0-9]+)(\))/';
                preg_match($regex, $this->property->{CustomSpecAttr::DB_TYPE}, $matches);
                $ml = isset($matches[3]) ? $matches[3] : $ml;
            }
        }
        return $ml;
    }

    public function getMinLength():?int
    {
        return $this->getAttr('minLength');
    }

    public function isReadonly():bool
    {
        return $this->getAttr('readOnly', false);
    }

    public function guessPhpType():string
    {
        $customDbType = isset($this->property->{CustomSpecAttr::DB_TYPE})
            ? strtolower($this->property->{CustomSpecAttr::DB_TYPE}) : null;
        if ($customDbType !== null
            && (in_array($customDbType, ['json', 'jsonb'], true) || StringHelper::endsWith($customDbType, '[]'))
        ) {
            return 'array';
        }

        if ($customDbType) {
            list(, , $phpType) = static::findMoreDetailOf($customDbType);
            return $phpType;
        }

        switch ($this->getAttr('type')) {
            case 'integer':
                return 'int';
            case 'boolean':
                return 'bool';
            case 'number': // can be double and float
                return $this->getAttr('format') === 'double' ? 'double' : 'float';
            default:
                return $this->getAttr('type', 'string');
        }
    }

    public function guessDbType($forReference = false):string
    {
        if ($forReference) {
            $format = $this->getAttr('format');
            if ($this->getAttr('type') === 'integer') {
                return $format === 'int64' ? YiiDbSchema::TYPE_BIGINT : YiiDbSchema::TYPE_INTEGER;
            }
            return $this->getAttr('type');
        }
        if ($this->hasRefItems()) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        if ($this->hasAttr(CustomSpecAttr::DB_TYPE) && $this->getAttr(CustomSpecAttr::DB_TYPE) !== false) {
            $customDbType = strtolower($this->getAttr(CustomSpecAttr::DB_TYPE));
            // if ($customDbType === 'varchar') {
            //     return YiiDbSchema::TYPE_STRING;
            // }
            if ($customDbType !== null) {
                return $customDbType;
            }
        }
        $format = $this->getAttr('format');
        $type = $this->getAttr('type');
        if ($this->isPk && $type === 'integer') {
            return $format === 'int64' ? YiiDbSchema::TYPE_BIGPK : YiiDbSchema::TYPE_PK;
        }

        switch ($type) {
            case 'boolean':
                return $type;
            case 'number': // can be double and float
                return $format ?? 'float';
            case 'integer':
                if ($format === 'int64') {
                    return YiiDbSchema::TYPE_BIGINT;
                }
                return YiiDbSchema::TYPE_INTEGER;
            case 'string':
                if (in_array($format, ['date', 'time', 'binary'])) {
                    return $format;
                }
                if ($this->hasAttr('maxLength') && (int)$this->getAttr('maxLength') < 2049) {
                    //What if we want to restrict length of text column?
                    return YiiDbSchema::TYPE_STRING;
                }
                if ($format === 'date-time' || $format === 'datetime') {
                    return YiiDbSchema::TYPE_DATETIME;
                }
                if (in_array($format, ['email', 'url', 'phone', 'password'])) {
                    return YiiDbSchema::TYPE_STRING;
                }
                if (!empty($this->property->enum)) {
                    return YiiDbSchema::TYPE_STRING;
                }
                return YiiDbSchema::TYPE_TEXT;
            case 'object':
            {
                return YiiDbSchema::TYPE_JSON;
            }
//            case 'array':
//                Need schema example for this case if it is possible
//                return $this->typeForArray();
            default:
                return YiiDbSchema::TYPE_TEXT;
        }
    }

    /**
     * @return array|int|mixed|null
     */
    public function guessDefault()
    {
        if (!$this->hasAttr('default')) {
            return null;
        }
        $phpType = $this->guessPhpType();
        $dbType = $this->guessDbType();
        $default = $this->getAttr('default');

        if ($phpType === 'array' && in_array($default, ['{}', '[]'])) {
            return [];
        }
        if (is_string($default) && $phpType === 'array' && StringHelper::startsWith($dbType, 'json')) {
            try {
                return Json::decode($default);
            } catch (Throwable $e) {
                return [];
            }
        }

        if ($phpType === 'integer' && $default !== null) {
            return (int)$default;
        }

        return $default;
    }

    public static function findMoreDetailOf(string $xDbType): array
    {
        // We can have various values in `x-db-type`. Few examples are:
        // double precision(10,2)
        // double
        // text
        // text[]
        // decimal(12,2)
        // decimal
        // pg_lsn
        // pg_snapshot
        // integer primary key
        // time with time zone
        // time(3) with time zone
        // smallint unsigned zerofill
        // mediumint(10) unsigned zerofill comment "comment"

        // We only consider first word of DB type that has more than one word e.g. :
        // SQL Standard
        // 'double precision',

        // // PgSQL
        // 'bit varying',
        // 'character varying',
        // 'time with time zone',
        // 'time(3) with time zone',
        // 'time without time zone',
        // 'timestamp with time zone',
        // 'timestamp(6) with time zone',
        // 'timestamp without time zone',

        // Because abstract data type (e.g. yii\db\pgsql\Schema::$typeMap) is same for:
        // `double` and `double precision`
        // `time` and `time with time zone`
        // `time` and `time without time zone`
        // `timestamp` and `timestamp without time zone` etc


        preg_match('/\w+/', $xDbType, $matches);
        if (!isset($matches[0])) {
            throw new \yii\base\InvalidConfigException('Abnormal x-db-type: "'.$xDbType.'" detected');
        }
        $firstWordOfRealJustDbType = strtolower($matches[0]);

        if (ApiGenerator::isMysql()) {
            $mysqlSchema = new MySqlSchema;

            if (!array_key_exists($firstWordOfRealJustDbType, $mysqlSchema->typeMap)) {
                throw new InvalidDefinitionException('"x-db-type: '.$firstWordOfRealJustDbType.'" is incorrect. "'.$firstWordOfRealJustDbType.'" is not a real data type in MySQL or not implemented in Yii MySQL. See allowed data types list in `\yii\db\mysql\Schema::$typeMap`');
            }

            $yiiAbstractDataType = $mysqlSchema->typeMap[$firstWordOfRealJustDbType];
        } elseif (ApiGenerator::isMariaDb()) {
            $mariadbSchema = new MariaDbSchema;

            if (!array_key_exists($firstWordOfRealJustDbType, $mariadbSchema->typeMap)) {
                throw new InvalidDefinitionException('"x-db-type: '.$firstWordOfRealJustDbType.'" is incorrect. "'.$firstWordOfRealJustDbType.'" is not a real data type in MariaDb or not implemented in Yii MariaDB. See allowed data types list in `\SamIT\Yii2\MariaDb\Schema::$typeMap`');
            }
            $yiiAbstractDataType = $mariadbSchema->typeMap[$firstWordOfRealJustDbType];
        } elseif (ApiGenerator::isPostgres()) {
            $pgsqlSchema = new PgSqlSchema;
            if (!array_key_exists($firstWordOfRealJustDbType, $pgsqlSchema->typeMap)) {
                preg_match('/\w+\ \w+/', $xDbType, $doublePrecisionDataType);
                if (!isset($doublePrecisionDataType[0])) {
                    throw new InvalidDefinitionException('"x-db-type: '.$firstWordOfRealJustDbType.'" is incorrect. "'.$firstWordOfRealJustDbType.'" is not a real data type in PostgreSQL or not implemented in Yii PostgreSQL. See allowed data types list in `\yii\db\pgsql\Schema::$typeMap`');
                }
                $doublePrecisionDataType[0] = strtolower($doublePrecisionDataType[0]);
                if (!array_key_exists($doublePrecisionDataType[0], $pgsqlSchema->typeMap)) {
                    throw new InvalidDefinitionException('"x-db-type: '.$doublePrecisionDataType[0].'" is incorrect. "'.$doublePrecisionDataType[0].'" is not a real data type in PostgreSQL or not implemented in Yii PostgreSQL. See allowed data types list in `\yii\db\pgsql\Schema::$typeMap`');
                }
                $yiiAbstractDataType = $pgsqlSchema->typeMap[$doublePrecisionDataType[0]];
            } else {
                $yiiAbstractDataType = $pgsqlSchema->typeMap[$firstWordOfRealJustDbType];
            }
        } else {
            throw new NotSupportedException('"x-db-type" for database '.get_class(Yii::$app->db->schema).' is not implemented. It is only implemented for PostgreSQL, MySQL and MariaDB.');
        }

        $phpType = static::getColumnPhpType(new ColumnSchema(['type' => $yiiAbstractDataType]));
        if (StringHelper::endsWith($xDbType, '[]')) {
            $phpType = 'array';
        }

        return [
            $firstWordOfRealJustDbType,
            $yiiAbstractDataType,
            $phpType,
        ];
    }

    /**
     * This method is copied + enhanced from protected method `getColumnPhpType()` of \yii\db\Schema class
     * Extracts the PHP type from abstract DB type.
     * @param \yii\db\ColumnSchema $column the column schema information
     * @return string PHP type name
     */
    public static function getColumnPhpType(ColumnSchema $column): string
    {
        static $typeMap = [
            // abstract type => php type
            YiiDbSchema::TYPE_TINYINT => 'integer',
            YiiDbSchema::TYPE_SMALLINT => 'integer',
            YiiDbSchema::TYPE_INTEGER => 'integer',
            YiiDbSchema::TYPE_BIGINT => 'integer',
            YiiDbSchema::TYPE_BOOLEAN => 'boolean',
            YiiDbSchema::TYPE_FLOAT => 'double',
            YiiDbSchema::TYPE_DOUBLE => 'double',
            YiiDbSchema::TYPE_DECIMAL => 'double', # (enhanced)
            YiiDbSchema::TYPE_BINARY => 'resource',
            YiiDbSchema::TYPE_JSON => 'array',
        ];
        if (isset($typeMap[$column->type])) {
            if ($column->type === 'bigint') {
                return PHP_INT_SIZE === 8 && !$column->unsigned ? 'integer' : 'string';
            } elseif ($column->type === 'integer') {
                return PHP_INT_SIZE === 4 && $column->unsigned ? 'string' : 'integer';
            }

            return $typeMap[$column->type];
        }

        return 'string';
    }
}
