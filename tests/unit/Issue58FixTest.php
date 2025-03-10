<?php

namespace tests\unit;

use tests\DbTestCase;
use Yii;
use yii\helpers\FileHelper;

// This class contains tests for various issues present at GitHub
class Issue58FixTest extends DbTestCase
{
    // https://github.com/php-openapi/yii2-openapi/issues/58
    public function test58CreateMigrationForColumnPositionChange()
    {
        $this->deleteTableFor58CreateMigrationForColumnPositionChange();
        $this->createTableFor58CreateMigrationForColumnPositionChange();

        $testFile = Yii::getAlias("@specs/issue_fix/58_create_migration_for_column_position_change_if_a_field_position_is_changed_in_spec/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/58_create_migration_for_column_position_change_if_a_field_position_is_changed_in_spec/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 1);
        $this->deleteTableFor58CreateMigrationForColumnPositionChange();
    }

    private function createTableFor58CreateMigrationForColumnPositionChange()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'description' => 'text not null',
            'name' => 'text not null',
        ])->execute();
    }

    private function deleteTableFor58CreateMigrationForColumnPositionChange()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    private function for58($schema, $expected, $columns = [
        'id' => 'pk',
        'name' => 'text not null',
        'description' => 'text not null',
        'colour' => 'text not null',
        'size' => 'text not null',
    ],                                         $dbs = ['Mysql', 'Mariadb'])
    {
        $deleteTable = function () {
            Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
        };
        $createTable = function () use ($columns) {
            Yii::$app->db->createCommand()->createTable('{{%fruits}}', $columns)->execute();
        };

        $config = [
            'openApiPath' => 'data://text/plain;base64,' . base64_encode($schema),
            'generateUrls' => false,
            'generateModels' => false,
            'generateControllers' => false,
            'generateMigrations' => true,
            'generateModelFaker' => false,
        ];
        $tmpConfigFile = Yii::getAlias("@runtime") . "/tmp-config.php";
        file_put_contents($tmpConfigFile, '<?php return ' . var_export($config, true) . ';');

        foreach ($dbs as $db) {
            $this->{"changeDbTo$db"}();
            $deleteTable();
            $createTable();

            $dbStr = str_replace('db', '', strtolower($db));
            $this->runGenerator($tmpConfigFile, $dbStr);
            $actual = file_get_contents(Yii::getAlias('@app') . '/migrations_' . $dbStr . '_db/m200000_000000_change_table_fruits.php');
            $this->assertSame($expected, $actual);
            $this->runActualMigrations($dbStr, 1);
            $deleteTable();
        }
        FileHelper::unlink($tmpConfigFile);
    }

    // ------------ Delete
    public function test58DeleteLastCol()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
        description:
          type: string
          nullable: false
        colour:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%fruits}}', 'size');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'size', $this->text()->notNull());
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58DeleteLast2ConsecutiveCol()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
        description:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%fruits}}', 'size');
        $this->dropColumn('{{%fruits}}', 'colour');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'colour', $this->text()->notNull());
        $this->addColumn('{{%fruits}}', 'size', $this->text()->notNull());
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58DeleteAColInBetween()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
        colour:
          type: string
          nullable: false
        size:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%fruits}}', 'description');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'description', $this->text()->notNull()->after('name'));
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58Delete2ConsecutiveColInBetween()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false        
        size:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%fruits}}', 'colour');
        $this->dropColumn('{{%fruits}}', 'description');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'description', $this->text()->notNull()->after('name'));
        $this->addColumn('{{%fruits}}', 'colour', $this->text()->notNull()->after('description'));
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58Delete2NonConsecutiveColInBetween()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        description:
          type: string
          nullable: false        
        size:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%fruits}}', 'colour');
        $this->dropColumn('{{%fruits}}', 'name');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'name', $this->text()->notNull()->after('id'));
        $this->addColumn('{{%fruits}}', 'colour', $this->text()->notNull()->after('description'));
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58DeleteLast4Col()
    {
        $columns = [
            'id' => 'pk',
            'name' => 'bool null',
            'description' => 'bool null',
            'colour' => 'bool null',
            'size' => 'bool null',
            'col_6' => 'bool null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: boolean
          nullable: true        
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%fruits}}', 'col_6');
        $this->dropColumn('{{%fruits}}', 'size');
        $this->dropColumn('{{%fruits}}', 'colour');
        $this->dropColumn('{{%fruits}}', 'description');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'description', $this->tinyInteger(1)->null()->defaultValue(null));
        $this->addColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('description'));
        $this->addColumn('{{%fruits}}', 'size', $this->tinyInteger(1)->null()->defaultValue(null)->after('colour'));
        $this->addColumn('{{%fruits}}', 'col_6', $this->tinyInteger(1)->null()->defaultValue(null));
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }

    public function test58DeleteFirst4Col()
    {
        $columns = [
            'name' => 'boolean null',
            'description' => 'boolean null',
            'colour' => 'boolean null',
            'size' => 'boolean null',
            'col_6' => 'boolean null',
            'col_7' => 'boolean null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:        
        col_6:
          type: boolean
          nullable: true
        col_7:
          type: boolean
          nullable: true
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%fruits}}', 'size');
        $this->dropColumn('{{%fruits}}', 'colour');
        $this->dropColumn('{{%fruits}}', 'description');
        $this->dropColumn('{{%fruits}}', 'name');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'name', $this->tinyInteger(1)->null()->defaultValue(null)->first());
        $this->addColumn('{{%fruits}}', 'description', $this->tinyInteger(1)->null()->defaultValue(null)->after('name'));
        $this->addColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('description'));
        $this->addColumn('{{%fruits}}', 'size', $this->tinyInteger(1)->null()->defaultValue(null)->after('colour'));
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }

    // ------------ Add
    public function test58AddAColAtLastPos()
    {
        // default position is last so no `AFTER` needed
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
        description:
          type: string
          nullable: false
        colour:
          type: string
          nullable: false
        size:
          type: string
          nullable: false
        weight:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'weight', $this->text()->notNull());
    }

    public function down()
    {
        $this->dropColumn('{{%fruits}}', 'weight');
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58Add2ConsecutiveColAtLastPos()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
        description:
          type: string
          nullable: false
        colour:
          type: string
          nullable: false
        size:
          type: string
          nullable: false
        weight:
          type: string
          nullable: false
        location:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'weight', $this->text()->notNull()->after('size'));
        $this->addColumn('{{%fruits}}', 'location', $this->text()->notNull());
    }

    public function down()
    {
        $this->dropColumn('{{%fruits}}', 'location');
        $this->dropColumn('{{%fruits}}', 'weight');
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58AddAColInBetween()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
        description:
          type: string
          nullable: false
        weight:
          type: string
          nullable: false
        colour:
          type: string
          nullable: false
        size:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'weight', $this->text()->notNull()->after('description'));
    }

    public function down()
    {
        $this->dropColumn('{{%fruits}}', 'weight');
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58Add2ConsecutiveColInBetween()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
        description:
          type: string
          nullable: false
        weight:
          type: string
          nullable: false
        location:
          type: string
          nullable: false
        colour:
          type: string
          nullable: false
        size:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'weight', $this->text()->notNull()->after('description'));
        $this->addColumn('{{%fruits}}', 'location', $this->text()->notNull()->after('weight'));
    }

    public function down()
    {
        $this->dropColumn('{{%fruits}}', 'location');
        $this->dropColumn('{{%fruits}}', 'weight');
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    public function test58Add2NonConsecutiveColInBetween()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58DeleteLastCol'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
        weight:
          type: string
          nullable: false
        description:
          type: string
          nullable: false
        colour:
          type: string
          nullable: false
        location:
          type: string
          nullable: false
        size:
          type: string
          nullable: false
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'weight', $this->text()->notNull()->after('name'));
        $this->addColumn('{{%fruits}}', 'location', $this->text()->notNull()->after('colour'));
    }

    public function down()
    {
        $this->dropColumn('{{%fruits}}', 'location');
        $this->dropColumn('{{%fruits}}', 'weight');
    }
}

PHP;

        $this->for58($schema, $expected);
    }

    // ------------ Just move columns
    public function test58MoveLast2Col2PosUp()
    {
        $columns = [
            'id' => 'pk',
            'name' => 'bool null',
            'description' => 'bool null',
            'colour' => 'bool null',
            'size' => 'bool null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        colour:
          type: boolean
          nullable: true          
        size:
          type: boolean
          nullable: true
        name:
          type: boolean
          nullable: true
        description:
          type: boolean 
          nullable: true
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('id'));
        $this->alterColumn('{{%fruits}}', 'size', $this->tinyInteger(1)->null()->defaultValue(null)->after('colour'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'size', $this->tinyInteger(1)->null()->defaultValue(null)->after('colour'));
        $this->alterColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('description'));
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }

    // ----------- Miscellaneous
    public function test58Move1Add1Del1Col()
    {
        $columns = [
            'id' => 'pk',
            'name' => 'boolean null',
            'description' => 'boolean null',
            'colour' => 'boolean null',
            'size' => 'boolean null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
          nullable: true
        colour:
          type: boolean
          nullable: true
        name:
          type: boolean
          nullable: true
        description:
          type: boolean
          nullable: true
        col_6:
          type: integer
          nullable: true
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'col_6', $this->integer()->null()->defaultValue(null));
        $this->dropColumn('{{%fruits}}', 'size');
        $this->alterColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('id'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('description'));
        $this->addColumn('{{%fruits}}', 'size', $this->tinyInteger(1)->null()->defaultValue(null));
        $this->dropColumn('{{%fruits}}', 'col_6');
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }

    public function test58Add1Del1ColAtSamePosition()
    {
        $columns = [
            'id' => 'pk',
            'name' => 'bool null',
            'description' => 'bool null',
            'colour' => 'bool null',
            'size' => 'bool null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        name:
          type: boolean
          nullable: true
        description_new:
          type: integer
          default: 7
          nullable: true
        colour:
          type: boolean
          nullable: true
        size:
          type: boolean
          nullable: true
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'description_new', $this->integer()->null()->defaultValue(7)->after('name'));
        $this->dropColumn('{{%fruits}}', 'description');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'description', $this->tinyInteger(1)->null()->defaultValue(null)->after('name'));
        $this->dropColumn('{{%fruits}}', 'description_new');
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }

    public function test58Add3Del2ColAtDiffPos()
    {
        $columns = [
            'id' => 'pk',
            'name' => 'bool null',
            'description' => 'bool null',
            'colour' => 'bool null',
            'size' => 'bool null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        col_6:
          type: boolean
          nullable: true
        name:
          type: boolean
          nullable: true
        col_7:
          type: boolean
          nullable: true
        col_8:
          type: boolean
          nullable: true
        size:
          type: boolean
          nullable: true
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'col_6', $this->boolean()->null()->defaultValue(null)->after('id'));
        $this->addColumn('{{%fruits}}', 'col_7', $this->boolean()->null()->defaultValue(null)->after('name'));
        $this->addColumn('{{%fruits}}', 'col_8', $this->boolean()->null()->defaultValue(null)->after('col_7'));
        $this->dropColumn('{{%fruits}}', 'colour');
        $this->dropColumn('{{%fruits}}', 'description');
    }

    public function down()
    {
        $this->addColumn('{{%fruits}}', 'description', $this->tinyInteger(1)->null()->defaultValue(null)->after('name'));
        $this->addColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('description'));
        $this->dropColumn('{{%fruits}}', 'col_8');
        $this->dropColumn('{{%fruits}}', 'col_7');
        $this->dropColumn('{{%fruits}}', 'col_6');
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }

    // This test fails. See description of https://github.com/php-openapi/yii2-openapi/pull/59
//    public function test58Add3Del2Move3ColAtDiffPos()
//    {
//        $columns = [
//            'id' => 'pk',
//            'name' => 'bool null',
//            'description' => 'bool null',
//            'colour' => 'bool null',
//            'size' => 'bool null',
//            'col_6' => 'bool null',
//        ];
//
//        $schema = <<<YAML
//openapi: 3.0.3
//info:
//  title: 'test58MoveColumns'
//  version: 1.0.0
//components:
//  schemas:
//    Fruit:
//      type: object
//      properties:
//        id:
//          type: integer
//        size:
//          type: boolean
//        col_9:
//          type: boolean
//        name:
//          type: boolean
//        col_7:
//          type: boolean
//        description:
//          type: boolean
//        col_8:
//          type: boolean
//paths:
//  '/':
//    get:
//      responses:
//        '200':
//          description: OK
//YAML;
//
//        $expected = <<<'PHP'
//<?php
//
///**
// * Table for Fruit
// */
//class m200000_000000_change_table_fruits extends \yii\db\Migration
//{
//    public function up()
//    {
//    }
//
//    public function down()
//    {
//    }
//}
//
//PHP;
//
//        $this->for58($schema, $expected, $columns);
//    }

    public function test58MoveAColAndChangeItsDataType()
    {
        $columns = [
            'id' => 'pk',
            'name' => 'bool null',
            'description' => 'bool null',
            'colour' => 'bool null',
            'size' => 'bool null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
          nullable: true
        description:
          type: boolean
          nullable: true
        colour:
          type: integer
          nullable: true
        name:
          type: boolean
          nullable: true
        size:
          type: boolean
          nullable: true
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%fruits}}', 'colour', $this->integer()->null()->defaultValue(null));
        $this->alterColumn('{{%fruits}}', 'name', $this->tinyInteger(1)->null()->defaultValue(null)->after('colour'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->tinyInteger(1)->null()->defaultValue(null)->after('id'));
        $this->alterColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null));
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }

    public function test58MoveAColDownwards()
    {
        $columns = [
            'id' => 'pk',
            'name' => 'bool null',
            'description' => 'bool null',
            'colour' => 'bool null',
            'size' => 'bool null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer        
        description:
          type: boolean
          nullable: true
        colour:
          type: boolean
          nullable: true
        name:
          type: boolean
          nullable: true
        size:
          type: boolean
          nullable: true
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->tinyInteger(1)->null()->defaultValue(null)->after('colour'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->tinyInteger(1)->null()->defaultValue(null)->after('id'));
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }

    public function test58MoveAColUpwards()
    {
        $columns = [
            'id' => 'pk',
            'name' => 'bool null',
            'description' => 'bool null',
            'colour' => 'bool null',
            'size' => 'bool null',
        ];

        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test58MoveColumns'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      properties:
        id:
          type: integer
        colour:
          type: boolean
          nullable: true
        name:
          type: boolean
          nullable: true
        description:
          type: boolean
          nullable: true
        size:
          type: boolean
          nullable: true
paths:
  '/':
    get:
      responses:
        '200':
          description: OK
YAML;

        $expected = <<<'PHP'
<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('id'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'colour', $this->tinyInteger(1)->null()->defaultValue(null)->after('description'));
    }
}

PHP;

        $this->for58($schema, $expected, $columns);
    }
}
