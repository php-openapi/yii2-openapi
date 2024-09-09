<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;
use yii\db\IndexConstraint;
use function implode;
use function substr;

class DbIndex extends BaseObject
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $columns = [];

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var bool
     */
    public $isUnique = false;

    public function isEqual(DbIndex $dbIndex) : bool
    {
        return $this->type === $dbIndex->type
               && $this->isUnique === $dbIndex->isUnique
               && $this->columns === $dbIndex->columns;
    }

    public static function make(string $tableName, array $columns, $type = null, $isUnique = false):DbIndex
    {
        if ($type === 'btree') {
            $type = null; //Default type
        }
        $typeName = isset($type) ? '_'.explode('(', $type)[0]: '';
        $name = $isUnique !== false ? $tableName . '_'  . implode('_', $columns).'_key'
            : $tableName . '_' . implode('_', $columns) . $typeName . '_index';
        return new static([
            'name' => substr($name, 0, 63),
            'columns' => $columns,
            'type' => $type,
            'isUnique' => $isUnique,
        ]);
    }

    public static function fromConstraint(string $tableName, IndexConstraint $constraint):DbIndex
    {
        $name = $constraint->isUnique !== false ? $tableName . '_'  . implode('_', $constraint->columnNames).'_key'
            : $tableName . '_' . implode('_', $constraint->columnNames) . '_index';
        return new static([
            'name' => substr($name, 0, 63),
            'columns' => $constraint->columnNames,
            'type' => null,
            'isUnique' => $constraint->isUnique,
        ]);
    }
}
