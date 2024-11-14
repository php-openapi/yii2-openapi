<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\db;

class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var string|null|false
     * Custom DB type which contains real DB type
     * Contains x-db-type string if present in OpenAPI YAML/json file
     * @see \cebe\yii2openapi\lib\items\Attribute::$xDbType and `x-db-type` docs in README.md
     * Used to detect what kind of migration code for column is to be generated
     * e.g. `double_p double precision NULL DEFAULT NULL`
     * instead of
     * ```php
     *   $this->createTable('{{%alldbdatatypes}}', [
     *       ...
     *       'double_p' => 'double precision NULL DEFAULT NULL',
     *       ...
     * ```
     */
    public $xDbType;

    /**
     * Used only for MySQL/MariaDB
     * @var array|null
     * [
     *      index => int # position: starts from 1
     *      after => ?string # after column
     *      before => ?string # before column
     * ]
     * If `before` is null then column is last
     * If `after` is null then column is first
     * If both are null then table has only 1 column
     */
    public ?array $fromPosition = null;
    public ?array $toPosition = null;

    /**
     * From `$this->fromPosition` and `$this->toPosition` we can check if the position is changed or not. This is done in `BaseMigrationBuilder::setColumnsPositions()`
     */
    public bool $isPositionChanged = false;
}
