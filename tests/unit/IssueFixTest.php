<?php

namespace tests\unit;

use tests\DbTestCase;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\FileHelper;

// This class contains tests for various issues present at GitHub
class IssueFixTest extends DbTestCase
{
    // fix https://github.com/cebe/yii2-openapi/issues/107
    // 107_no_syntax_error
    public function testMigrationsAreNotGeneratedWithSyntaxError()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/no_syntax_error_107/mysql/no_syntax_error_107.php");
        $this->deleteTablesForNoSyntaxError107();
        $this->createTableForNoSyntaxError107();
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/no_syntax_error_107/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 1);
        $this->deleteTables();
    }

    private function deleteTablesForNoSyntaxError107()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    private function createTableForNoSyntaxError107()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'varchar(255)',
        ])->execute();
    }

    public function testFloatIssue()
    {
        // test no migrations are generaeted
        $this->changeDbToPgsql();
        $this->deleteTablesForFloatIssue();
        $this->createTableForFloatIssue();
        $testFile = Yii::getAlias("@specs/issue_fix/float_issue/float_issue.php");
        $this->runGenerator($testFile, 'pgsql');
        $this->expectException(InvalidArgumentException::class);
        FileHelper::findDirectories(Yii::getAlias('@app') . '/migration');
        FileHelper::findDirectories(Yii::getAlias('@app') . '/migrations');
        FileHelper::findDirectories(Yii::getAlias('@app') . '/migrations_mysql_db');
        FileHelper::findDirectories(Yii::getAlias('@app') . '/migrations_maria_db');
        FileHelper::findDirectories(Yii::getAlias('@app') . '/migrations_pgsql_db');
        $this->deleteTables();
    }

    private function deleteTables()
    {
        $this->deleteTablesForFloatIssue();
        $this->deleteTablesForNoSyntaxError107();
        $this->deleteTableForQuoteInAlterColumn();
        $this->deleteTableForTimestampIssue143();
        $this->deleteTablesForWrongMigrationForPgsqlForStringVarcharDatatype149();
    }

    private function deleteTablesForFloatIssue()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    private function deleteTableForTimestampIssue143()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%timestamp143s}}')->execute();
    }

    private function createTableForFloatIssue()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'vat_percent' => 'float default 0',
        ])->execute();
    }

    private function createTableForTimestampIssue143()
    {
        Yii::$app->db->createCommand()->createTable('{{%timestamp143s}}', [
            'id' => 'pk',
            'created_at timestamp null default null',
            'updated_at timestamp null default null',
        ])->execute();
    }

    public function testCamelCaseColumnNameIssue127()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/camel_case_127/camel_case_127.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/camel_case_127/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 1);
        $this->deleteTables();

        $this->changeDbToPgsql();
        $testFile = Yii::getAlias("@specs/issue_fix/camel_case_127/camel_case_127.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/camel_case_127/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 1);
        $this->deleteTables();
    }

    public function testQuoteInAlterColumn()
    {
        $this->changeDbToPgsql();
        $this->deleteTableForQuoteInAlterColumn();
        $this->createTableForQuoteInAlterColumn();
        $testFile = Yii::getAlias("@specs/issue_fix/quote_in_alter_table/pgsql/quote_in_alter_table.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/quote_in_alter_table/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 1);
        $this->deleteTables();
    }

    private function deleteTableForQuoteInAlterColumn()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    private function createTableForQuoteInAlterColumn()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            // 'colourName' => 'circle',
            'colourName' => 'varchar(255)',
        ])->execute();
    }

    // Stub -> https://github.com/cebe/yii2-openapi/issues/132
    // public function testCreateTableInDownCode()
    // {
    //     $testFile = Yii::getAlias("@specs/issue_fix/create_table_in_down_code/create_table_in_down_code.php");
    //     $this->deleteTablesForCreateTableInDownCode();
    //     $this->createTableForCreateTableInDownCode();
    //     $this->runGenerator($testFile, 'mysql');
    //     // $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
    //     //     'recursive' => true,
    //     // ]);
    //     // $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/create_table_in_down_code/mysql/app"), [
    //     //     'recursive' => true,
    //     // ]);
    //     // $this->checkFiles($actualFiles, $expectedFiles);
    //     // $this->runActualMigrations('mysql', 1);
    // }

    // private function deleteTablesForCreateTableInDownCode()
    // {
    //     Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    //     Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%animals}}')->execute();
    // }

    // private function createTableForCreateTableInDownCode()
    // {
    //     Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
    //         'id' => 'pk',
    //         'colourName' => 'varchar(255)',
    //     ])->execute();
    //     Yii::$app->db->createCommand()->createTable('{{%animals}}', [
    //         'id' => 'pk',
    //         'colourName' => 'varchar(255)',
    //     ])->execute();
    // }

    // fix https://github.com/cebe/yii2-openapi/issues/143
    // timestamp_143
    public function testTimestampIssue143()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/timestamp_143/mysql/timestamp_143.php");
        $this->deleteTableForTimestampIssue143();
        $this->createTableForTimestampIssue143();
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);

        // check no files are generated
        $this->assertEquals(count($actualFiles), 0);
        $this->deleteTables();
    }

    // https://github.com/cebe/yii2-openapi/issues/148
    public function testModelNameMoreThanOnceInFakerIssue148()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/model_name_more_than_once_in_faker_148/model_name_more_than_once_in_faker_148.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);

        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/model_name_more_than_once_in_faker_148/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/cebe/yii2-openapi/issues/149
    // wrongMigrationForPgsqlForStringVarcharDatatype
    // wrong_migration_for_pgsql_is_generated_for_string_varchar_datatype
    public function testWrongMigrationForPgsqlForStringVarcharDatatype149()
    {
        $this->changeDbToPgsql();
        $this->deleteTablesForWrongMigrationForPgsqlForStringVarcharDatatype149();
        $this->createTableForWrongMigrationForPgsqlForStringVarcharDatatype149();
        $testFile = Yii::getAlias("@specs/issue_fix/wrong_migration_for_pgsql_is_generated_for_string_varchar_datatype_149/wrong_migration_for_pgsql_is_generated_for_string_varchar_datatype_149.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);

        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/wrong_migration_for_pgsql_is_generated_for_string_varchar_datatype_149/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 1);
        $this->deleteTables();
    }

    private function createTableForWrongMigrationForPgsqlForStringVarcharDatatype149()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'string(150)', #  not null
        ])->execute();
    }

    private function deleteTablesForWrongMigrationForPgsqlForStringVarcharDatatype149()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    // https://github.com/cebe/yii2-openapi/issues/153
    // nullable false should put attribute in required section in model validation rules
    public function testNullableFalseInRequired()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/153_nullable_false_in_required/153_nullable_false_in_required.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/153_nullable_false_in_required/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    public function test162BugDollarrefWithXFaker()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/162_bug_dollarref_with_x_faker/162_bug_dollarref_with_x_faker.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/162_bug_dollarref_with_x_faker/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // 163_generator_crash_when_using_reference_inside_an_object
    public function test163GeneratorCrashWhenUsingReferenceInsideAnObject()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/163_generator_crash_when_using_reference_inside_an_object/index.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/163_generator_crash_when_using_reference_inside_an_object/pgsql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // #175 https://github.com/cebe/yii2-openapi/issues/175
    // Bug: allOf with multiple $refs
    public function test175BugAllOfWithMultipleDollarRefs()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/175_bug_allof_with_multiple_dollarrefs/index.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/175_bug_allof_with_multiple_dollarrefs/pgsql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // #172 https://github.com/cebe/yii2-openapi/issues/172
    // schema.yaml: requestBody has no effect
    public function test172SchemayamlRequestBodyHasNoEffect()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/172_schemayaml_requestbody_has_no_effect/index.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/172_schemayaml_requestbody_has_no_effect/pgsql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/cebe/yii2-openapi/issues/159
    public function test159BugGiiapiGeneratedRulesEmailid()
    {
        $this->changeDbToMariadb();
        $testFile = Yii::getAlias("@specs/issue_fix/159_bug_giiapi_generated_rules_emailid/index.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/159_bug_giiapi_generated_rules_emailid/maria"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/cebe/yii2-openapi/issues/158
    public function test158BugGiiapiGeneratedRulesEnumWithTrim()
    {
        $this->changeDbToMariadb();
        $testFile = Yii::getAlias("@specs/issue_fix/158_bug_giiapi_generated_rules_enum_with_trim/index.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/158_bug_giiapi_generated_rules_enum_with_trim/maria"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

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
            'description' => 'text',
            'name' => 'text',
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
        col_7:
          type: boolean
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
        size:
          type: boolean
        name:
          type: boolean
        description:
          type: boolean        
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
        colour:
          type: boolean
        name:
          type: boolean
        description:
          type: boolean
        col_6:
          type: boolean
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
        $this->addColumn('{{%fruits}}', 'col_6', $this->boolean()->null()->defaultValue(null));
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
        description_new:
          type: boolean
        colour:
          type: boolean
        size:
          type: boolean
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
        $this->addColumn('{{%fruits}}', 'description_new', $this->boolean()->null()->defaultValue(null)->after('name'));
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
        name:
          type: boolean
        col_7:
          type: boolean
        col_8:
          type: boolean
        size:
          type: boolean
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
        description:
          type: boolean
        colour:
          type: integer
        name:
          type: boolean
        size:
          type: boolean
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
        colour:
          type: boolean
        name:
          type: boolean
        size:
          type: boolean
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
        name:
          type: boolean
        description:
          type: boolean
        size:
          type: boolean
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
