<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\migrations;

use cebe\yii2openapi\generator\ApiGenerator;
use cebe\yii2openapi\lib\ColumnToCode;
use cebe\yii2openapi\lib\items\DbIndex;
use yii\base\NotSupportedException;
use yii\db\ColumnSchema;
use yii\db\IndexConstraint;
use yii\db\Schema;
use yii\helpers\ArrayHelper;

final class MysqlMigrationBuilder extends BaseMigrationBuilder
{
    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildColumnChanges(ColumnSchema $current, ColumnSchema $desired, array $changed):void
    {
        $positionCurrent = $positionDesired = null;
        if (in_array('position', $changed, true)) {
            $positionCurrent = $this->findPosition($current, true);
            $positionDesired = $this->findPosition($desired);
            $key = array_search('position', $changed, true);
            if ($key !== false) {
                unset($changed[$key]);
            }
        }
        $newColumn = clone $current;
//        $positionCurrent = $this->findPosition($desired, true);
//        $positionDesired = $this->findPosition($desired);
//        if ($positionCurrent === $positionDesired) {
//            $positionCurrent = $positionDesired = null;
//        } # else {
//            $position = $positionDesired;
//            $newColumn->position = $position;
//        }
        foreach ($changed as $attr) {
            $newColumn->$attr = $desired->$attr;
        }
        if (static::isEnum($newColumn)) {
            $newColumn->dbType = 'enum'; // TODO this is concretely not correct
        }
        $this->migration->addUpCode($this->recordBuilder->alterColumn($this->model->getTableAlias(), $newColumn, $positionDesired))
            ->addDownCode($this->recordBuilder->alterColumn($this->model->getTableAlias(), $current, $positionCurrent));
    }

    protected function compareColumns(ColumnSchema $current, ColumnSchema $desired):array
    {
        $changedAttributes = [];
        $tableAlias = $this->model->getTableAlias();

        $this->modifyCurrent($current);
        $this->modifyDesired($desired);
        $this->modifyDesiredInContextOfCurrent($current, $desired);

        // Why this is needed? Often manually created ColumnSchem instance have dbType 'varchar' with size 255 and ColumnSchema fetched from db have 'varchar(255)'. So varchar !== varchar(255). such normal mistake was leading to errors. So desired column is saved in temporary table and it is fetched from that temp. table and then compared with current ColumnSchema
        $desiredFromDb = $this->tmpSaveNewCol($tableAlias, $desired);

        $this->modifyDesiredInContextOfDesiredFromDb($desired, $desiredFromDb);

        $this->modifyDesired($desiredFromDb);
        $this->modifyDesiredInContextOfCurrent($current, $desiredFromDb);
        $this->modifyDesiredFromDbInContextOfDesired($desired, $desiredFromDb);

        foreach (['type', 'size', 'allowNull', 'defaultValue', 'enumValues'
                    , 'dbType', 'phpType'
                    , 'precision', 'scale', 'unsigned'
        ] as $attr) {
            if ($attr === 'defaultValue') {
                if ($this->isDefaultValueChanged($current, $desiredFromDb)) {
                    $changedAttributes[] = $attr;
                }
            } else {
                if ($current->$attr !== $desiredFromDb->$attr) {
                    $changedAttributes[] = $attr;
                }
            }
        }

//        $positionCurrent = $this->findPosition($desired, true);
//        $positionDesired = $this->findPosition($desired);
//        if ($positionCurrent !== $positionDesired) {
        if ($desired->isPositionReallyChanged) {
            $changedAttributes[] = 'position';
        }

        return $changedAttributes;
    }

    protected function createEnumMigrations():void
    {
        // execute via default
    }

    protected function isDbDefaultSize(ColumnSchema $current):bool
    {
        $defaults = [
            Schema::TYPE_PK => 11,
            Schema::TYPE_BIGPK => 20,
            Schema::TYPE_CHAR => 1,
            Schema::TYPE_STRING => 255,
            Schema::TYPE_TINYINT => 3,
            Schema::TYPE_SMALLINT => 6,
            Schema::TYPE_INTEGER => 11,
            Schema::TYPE_BIGINT => 20,
            Schema::TYPE_DECIMAL => 10,
            Schema::TYPE_BOOLEAN => 1,
            Schema::TYPE_MONEY => 19,
        ];
        return isset($defaults[$current->type]);
    }

    /**
     * @return array|DbIndex[]
     */
    protected function findTableIndexes():array
    {
        $dbIndexes = [];
        try {
            /**@var IndexConstraint[] $indexes */
            $indexes = $this->db->getSchema()->getTableIndexes($this->tableSchema->name);
            $fkIndexes = array_keys($this->tableSchema->foreignKeys);
            foreach ($indexes as $index) {
                if (!$index->isPrimary && !in_array($index->name, $fkIndexes, true)) {
                    $dbIndexes[] = DbIndex::fromConstraint($this->model->tableName, $index);
                }
            }
            return ArrayHelper::index($dbIndexes, 'name');
        } catch (NotSupportedException $e) {
            return [];
        }
    }

    public static function getColumnSchemaBuilderClass(): string
    {
        if (ApiGenerator::isMysql()) {
            return \yii\db\mysql\ColumnSchemaBuilder::class;
        } elseif (ApiGenerator::isMariaDb()) {
            return \SamIT\Yii2\MariaDb\ColumnSchemaBuilder::class;
        }
    }

    public function modifyCurrent(ColumnSchema $current): void
    {
        /** @var $current \yii\db\mysql\ColumnSchema */
        if ($current->phpType === 'integer' && $current->defaultValue !== null) {
            $current->defaultValue = (int)$current->defaultValue;
        }
    }

    public function modifyDesired(ColumnSchema $desired): void
    {
        /** @var $desired cebe\yii2openapi\db\ColumnSchema|\yii\db\mysql\ColumnSchema */
        if ($desired->phpType === 'int' && $desired->defaultValue !== null) {
            $desired->defaultValue = (int)$desired->defaultValue;
        }

        if ($decimalAttributes = ColumnToCode::isDecimalByDbType($desired->dbType)) {
            $desired->precision = $decimalAttributes['precision'];
            $desired->scale = $decimalAttributes['scale'];
        }
    }

    public function modifyDesiredInContextOfCurrent(ColumnSchema $current, ColumnSchema $desired): void
    {
        /** @var $current \yii\db\mysql\ColumnSchema */
        /** @var $desired cebe\yii2openapi\db\ColumnSchema|\yii\db\mysql\ColumnSchema */
        if ($current->dbType === 'tinyint(1)' && $desired->type === 'boolean') {
            if (is_bool($desired->defaultValue) || is_string($desired->defaultValue)) {
                $desired->defaultValue = (int)$desired->defaultValue;
            }
        }

        if ($current->type === $desired->type && !$desired->size && $this->isDbDefaultSize($current)) {
            $desired->size = $current->size;
        }
    }

    /**
     * TODO
     * Check if order/position of column is changed
     * @return void
     */
    public function checkOrder()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function findPosition(ColumnSchema $column, bool $forDrop = false): ?string
    {
        $columnNames = array_keys($forDrop ? $this->tableSchema->columns : $this->newColumns);

        $key = array_search($column->name, $columnNames);
        if ($key > 0) {
            $prevColName = $columnNames[$key - 1];

            if (!$forDrop && !isset($columnNames[$key + 1])) { // if new col is added at last then no need to add 'AFTER' SQL part. This is checked as if next column is present or not
                return null;
            }

            if (array_key_exists($prevColName, $this->newColumns)) {
                return self::POS_AFTER . ' ' . $prevColName;
            }
            return null;

        // if no `$columnSchema` is found, previous column does not exist. This happens when 'after column' is not yet added in migration or added after currently undertaken column
        } elseif ($key === 0) {
            return self::POS_FIRST;
        }

        return null;
    }


    // TODO
    public function handleColumnsPositionsChanges(array $haveNames, array $wantNames)
    {
        $indices = [];
        if ($haveNames !== $wantNames) {
            foreach ($wantNames as $key => $name) {
                if ($name !== $haveNames[$key]) {
                    $indices[] = $key;
                }
            }
        }
        for ($i = 0; $i < count($indices) / 2; $i++) {
            $this->migration->addUpCode($this->recordBuilder->alterColumn(
                $this->model->getTableAlias(),
                $this->newColumns[$wantNames[$indices[$i]]],
                $this->findPosition($this->newColumns[$wantNames[$indices[$i]]])
            ))->addDownCode($this->recordBuilder->alterColumn(
                $this->model->getTableAlias(),
                $this->tableSchema->columns[$wantNames[$indices[$i]]],
                $this->findPosition($this->tableSchema->columns[$wantNames[$indices[$i]]], true)
            ));
        }
//        $this->migration->addUpCode($this->recordBuilder->dropTable($this->model->getTableAlias()));
    }

    public function setPositions()
    {
        $i = 0;
        $haveColumns = $this->tableSchema->columns;
        $onlyColumnNames = array_keys($this->newColumns);
        $haveNamesOnlyColNames = array_keys($haveColumns);
        foreach ($this->newColumns as $columnName => $column) {
            /** @var \cebe\yii2openapi\db\ColumnSchema $column */
            $column->toPosition = [
                'index' => $i + 1,
                'after' => $i === 0 ? null : $onlyColumnNames[$i - 1],
                'before' => $i === (count($onlyColumnNames) - 1) ? null : $onlyColumnNames[$i + 1],
            ];

            if (isset($haveColumns[$columnName])) {
                $index = array_search($columnName, $haveNamesOnlyColNames) + 1;
                $column->fromPosition = [
                    'index' => $index,
                    'after' => $haveNamesOnlyColNames[$index - 2] ?? null,
                    'before' => $haveNamesOnlyColNames[$index] ?? null,
                ];
            }

            $i++;
        }

        $takenIndices = [];
        foreach ($this->newColumns as $columnName => $column) {
            /** @var \cebe\yii2openapi\db\ColumnSchema $column */

            if (!$column->fromPosition || !$column->toPosition) {
                continue;
            }

            if (count($onlyColumnNames) !== count($haveNamesOnlyColNames)) {
                // check if only new columns are added without any explicit position change
                $columnsForCreate = array_diff($onlyColumnNames, $haveNamesOnlyColNames);
                if ($columnsForCreate) {
                    if ($haveNamesOnlyColNames === array_values(array_diff($onlyColumnNames, $columnsForCreate))) {
                        continue;
                    }
                }

                // check if only existing columns are deleted without any explicit position change
                $columnsForDrop = array_diff($haveNamesOnlyColNames, $onlyColumnNames);
                if ($columnsForDrop) {
                    if ($onlyColumnNames === array_values(array_diff($haveNamesOnlyColNames, $columnsForDrop))) {
                        continue;
                    }
                }
            }

            if (is_int(array_search([$column->toPosition['index'], $column->fromPosition['index']], $takenIndices))) {
                continue;
            }
            if ($column->fromPosition === $column->toPosition) {
                continue;
            }
            if ($column->fromPosition['index'] === $column->toPosition['index']) {
                continue;
            }

            $column->isPositionReallyChanged = true;
            $takenIndices[] = [$column->fromPosition['index'], $column->toPosition['index']];
        }
    }

    public function checkAfterPosition($column)
    {
        if ($column->fromPosition['after'] === $column->toPosition['after']
        ) {
            $afterColName = $column->toPosition['after'];
            $afterCol = $this->newColumns[$afterColName] ?? null;
            if ($afterCol) {
                if ($this->checkAfterPosition($afterCol)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    public function checkBeforePosition($column)
    {
        if ($column->fromPosition['before'] === $column->toPosition['before']
        ) {
            $beforeColName = $column->toPosition['before'];
            $beforeCol = $this->newColumns[$beforeColName] ?? null;
            if ($beforeCol) {
                if ($this->checkBeforePosition($beforeCol)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        return false;
    }
}
