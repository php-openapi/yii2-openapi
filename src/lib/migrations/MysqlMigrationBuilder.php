<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\migrations;

use cebe\yii2openapi\generator\ApiGenerator;
use cebe\yii2openapi\lib\ColumnToCode;
use cebe\yii2openapi\lib\CustomSpecAttr;
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
            $positionDesired = $this->findPosition($desired, false, true);
            $positionCurrent = $this->findPosition($desired, true, true);
            $key = array_search('position', $changed, true);
            unset($changed[$key]);
        }
        $newColumn = clone $current;
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

        // Why this is needed? Often manually created ColumnSchema instance have dbType 'varchar' with size 255 and ColumnSchema fetched from db have 'varchar(255)'. So varchar !== varchar(255). such normal mistake was leading to errors. So desired column is saved in temporary table and it is fetched from that temp. table and then compared with current ColumnSchema
        $desiredFromDb = $this->tmpSaveNewCol($tableAlias, $desired);

        $this->modifyDesiredInContextOfDesiredFromDb($desired, $desiredFromDb);

        $this->modifyDesired($desiredFromDb);
        $this->modifyDesiredInContextOfCurrent($current, $desiredFromDb);
        $this->modifyDesiredFromDbInContextOfDesired($desired, $desiredFromDb);

        $properties = ['type', 'size', 'allowNull', 'defaultValue', 'enumValues'
            , 'dbType', 'phpType'
            , 'precision', 'scale', 'unsigned'#, 'comment'
        ];
        if (!empty($this->config->getOpenApi()->{CustomSpecAttr::DESC_IS_COMMENT})) {
            $properties[] = 'comment';
        }
        foreach ($properties as $attr) {
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

        if (property_exists($desired, 'isPositionChanged') && $desired->isPositionChanged) {
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
        } else {
            throw new \Exception('Unknown database');
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
        /** @var $desired \cebe\yii2openapi\db\ColumnSchema|\yii\db\mysql\ColumnSchema */
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
        /** @var $desired \cebe\yii2openapi\db\ColumnSchema|\yii\db\mysql\ColumnSchema */
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
     * {@inheritDoc}
     */
    public function findPosition(ColumnSchema $column, bool $forDrop = false, bool $forAlter = false): ?string
    {
        $columnNames = array_keys($forDrop ? $this->tableSchema->columns : $this->newColumns);

        $key = array_search($column->name, $columnNames);
        if ($key > 0) {
            $prevColName = $columnNames[$key - 1];
            if (($key === count($columnNames) - 1) && !$forAlter) {
                return null;
            }

            if (array_key_exists($prevColName, $forDrop ? $this->tableSchema->columns : $this->newColumns)) {
                if ($forDrop && !$forAlter) {
                    // if the previous column is the last one in the want names then no need for AFTER
                    $cols = array_keys($this->newColumns);
                    if ($prevColName === array_pop($cols)) {
                        return null;
                    }
                }
                if ($forAlter && $forDrop) {
                    if (!array_key_exists($prevColName, $this->newColumns)) {
                        return null;
                    }
                }
                return self::POS_AFTER . ' ' . $prevColName;
            }
            return null;

        // if no `$columnSchema` is found, previous column does not exist. This happens when 'after column' is not yet added in migration or added after currently undertaken column
        } elseif ($key === 0) {
            return self::POS_FIRST;
        }

        return null;
    }

    public function setColumnsPositions()
    {
        $i = 0;
        $haveColumns = $this->tableSchema->columns;
        $wantNames = array_keys($this->newColumns);
        $haveNames = array_keys($haveColumns);

        // Part 1/2 compute from and to position
        foreach ($this->newColumns as $name => $column) {
            /** @var \cebe\yii2openapi\db\ColumnSchema $column */
            $column->toPosition = [
                'index' => $i + 1,
                'after' => $i === 0 ? null : $wantNames[$i - 1],
                'before' => $i === (count($wantNames) - 1) ? null : $wantNames[$i + 1],
            ];

            if (isset($haveColumns[$name])) {
                $index = array_search($name, $haveNames) + 1;
                $column->fromPosition = [
                    'index' => $index,
                    'after' => $haveNames[$index - 2] ?? null,
                    'before' => $haveNames[$index] ?? null,
                ];
            }

            $i++;
        }

        // Part 2/2 compute is position is really changed

        // check if only new columns are added without any explicit position change
        $namesForCreate = array_diff($wantNames, $haveNames);
        $wantNamesWoNewCols = array_values(array_diff($wantNames, $namesForCreate));
        if ($namesForCreate && $haveNames === $wantNamesWoNewCols) {
            return;
        }
        // check if only existing columns are deleted without any explicit position change
        $namesForDrop = array_diff($haveNames, $wantNames);
        $haveNamesWoDropCols = array_values(array_diff($haveNames, $namesForDrop));
        if ($namesForDrop && $wantNames === $haveNamesWoDropCols) {
            return;
        }
        // check both above simultaneously
        if ($namesForCreate && $namesForDrop && ($wantNamesWoNewCols === $haveNamesWoDropCols)) {
            return;
        }

        $takenIndices = $nonRedundantIndices = []; # $nonRedundantIndices are the wanted ones which are created by moving of one or more columns. Example: if a column is moved from 2nd to 8th position then we will consider only one column is moved ignoring index/position change(-1) of 4rd to 8th column (4->3, 5->4 ...). So migration for this unwanted indices changes won't be generated. `$takenIndices` might have redundant indices
        foreach ($this->newColumns as $column) {
            /** @var \cebe\yii2openapi\db\ColumnSchema $column */

            if (!$column->fromPosition || !$column->toPosition) {
                continue;
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

            $column->isPositionChanged = true;
            $takenIndices[] = [$column->fromPosition['index'], $column->toPosition['index']];

            // -------
            if (($column->fromPosition['before'] !== $column->toPosition['before']) &&
                ($column->fromPosition['after'] !== $column->toPosition['after'])
            ) {
                $nonRedundantIndices[] = [$column->fromPosition['index'], $column->toPosition['index']];
            }
        }

        foreach ($this->newColumns as $column) {
            /** @var \cebe\yii2openapi\db\ColumnSchema $column */

            if (!isset($column->toPosition['index'], $column->fromPosition['index'])) {
                continue;
            }
            $condition = (abs($column->toPosition['index'] - $column->fromPosition['index']) === count($nonRedundantIndices));
            if (($column->fromPosition['before'] === $column->toPosition['before'])
                && $condition
            ) {
                $column->isPositionChanged = false;
                continue;
            }
            if (($column->fromPosition['after'] === $column->toPosition['after'])
                && $condition
            ) {
                $column->isPositionChanged = false;
            }
        }
    }

    public function handleCommentsMigration()
    {
        // nothing to do here as comments can be defined in same statement as of alter/add column in MySQL
        // this method is only for PgSQL
    }
}
