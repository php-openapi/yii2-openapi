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

    // https://github.com/php-openapi/yii2-openapi/pull/4#discussion_r1688225258
    public function testCreateMigrationForDropTable132IndependentTablesDropSort()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/132_create_migration_for_drop_table/case_independent_tables_drop_sort/index.php");
        $dropTables = function () {
            Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%ubigpks}}')->execute();
            Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%bigpks}}')->execute();
            Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%upks}}')->execute();
        };

        $dropTables();
        Yii::$app->db->createCommand()->createTable('{{%upks}}', [
            'id' => 'upk',
            'name' => 'string(150)',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%bigpks}}', [
            'id' => 'bigpk',
            'name' => 'string(150)',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%ubigpks}}', [
            'id' => 'ubigpk',
            'name' => 'string(150)',
        ])->execute();

        $this->runGenerator($testFile);
        $this->runActualMigrations('mysql', 4);

        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/132_create_migration_for_drop_table/case_independent_tables_drop_sort/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);

        $dropTables();
    }

    // Create migration for drop table if a entire schema is deleted from OpenAPI spec #132
    // https://github.com/cebe/yii2-openapi/issues/132
    public function testCreateMigrationForDropTable132()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/132_create_migration_for_drop_table/132_create_migration_for_drop_table.php");
        $this->deleteTablesForCreateMigrationForDropTable132();
        $this->createTablesForCreateMigrationForDropTable132();
        $this->runGenerator($testFile);
        $this->runActualMigrations('mysql', 8);

        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/132_create_migration_for_drop_table/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);

        $this->deleteTablesForCreateMigrationForDropTable132();
    }

    private function createTablesForCreateMigrationForDropTable132()
    {
        Yii::$app->db->createCommand()->createTable('{{%upks}}', [
            'id' => 'upk',
            'name' => 'string(150)',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%bigpks}}', [
            'id' => 'bigpk',
            'name' => 'string(150)',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%ubigpks}}', [
            'id' => 'ubigpk',
            'name' => 'string(150)',
            'size' => "ENUM('x-small', 'small', 'medium', 'large', 'x-large') NOT NULL DEFAULT 'x-small'",
            'd SMALLINT UNSIGNED ZEROFILL',
            'e' => 'MEDIUMINT UNSIGNED ZEROFILL',
            'f' => 'decimal(12,4)',
            'dp' => 'double precision',
            'dp2' => 'double precision(10, 4)'
        ])->execute();

        // ---
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'string(150)',
            'food_of' => 'int'
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%pristines}}', [
            'id' => 'pk',
            'name' => 'string(151)',
            'fruit_id' => 'int', // FK
        ])->execute();
        Yii::$app->db->createCommand()->addForeignKey('name', '{{%pristines}}', 'fruit_id', '{{%fruits}}', 'id')->execute();

        // ---
        Yii::$app->db->createCommand()->createTable('{{%the_animal_table_name}}', [
            'id' => 'pk',
            'name' => 'string(150)',
        ])->execute();
        Yii::$app->db->createCommand()->addForeignKey('name2', '{{%fruits}}', 'food_of', '{{%the_animal_table_name}}', 'id')->execute();
        Yii::$app->db->createCommand()->createTable('{{%the_mango_table_name}}', [
            'id' => 'pk',
            'name' => 'string(150)',
            'food_of' => 'int'
        ])->execute();
        Yii::$app->db->createCommand()->addForeignKey('animal_fruit_fk', '{{%the_mango_table_name}}', 'food_of', '{{%the_animal_table_name}}', 'id')->execute();
    }

    private function deleteTablesForCreateMigrationForDropTable132()
    {
        $this->dropFkIfExists('{{%pristines}}', 'name');
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%pristines}}')->execute();

        $this->dropFkIfExists('{{%fruits}}', 'name2');
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%upks}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%bigpks}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%ubigpks}}')->execute();

        $this->dropFkIfExists('{{%the_mango_table_name}}', 'animal_fruit_fk');
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%the_mango_table_name}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%the_animal_table_name}}')->execute();
    }

    // Create migration for drop table if a entire schema is deleted from OpenAPI spec #132
    // https://github.com/cebe/yii2-openapi/issues/132
    // For PgSQL
    public function testCreateMigrationForDropTable132ForPgsql()
    {
        $this->changeDbToPgsql();
        $testFile = Yii::getAlias("@specs/issue_fix/132_create_migration_for_drop_table/132_create_migration_for_drop_table.php");
        $this->deleteTablesForCreateMigrationForDropTable132ForPgsql();
        $this->createTablesForCreateMigrationForDropTable132ForPgsql();
        $this->runGenerator($testFile, 'pgsql');
        $this->runActualMigrations('pgsql', 8);

        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/132_create_migration_for_drop_table/pgsql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);

        $this->deleteTablesForCreateMigrationForDropTable132ForPgsql();
    }

    private function createTablesForCreateMigrationForDropTable132ForPgsql()
    {
        Yii::$app->db->createCommand('CREATE TYPE mood AS ENUM (\'sad\', \'ok\', \'happy\')')->execute();
        Yii::$app->db->createCommand('CREATE TYPE enum_itt_upks_e2 AS ENUM (\'sad2\', \'ok2\', \'happy2\')')->execute();

        Yii::$app->db->createCommand()->createTable('{{%upks}}', [
            'id' => 'upk',
            'name' => 'string(150)',
            'current_mood' => 'mood',
            'e2' => 'enum_itt_upks_e2',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%bigpks}}', [
            'id' => 'bigpk',
            'name' => 'string(150)',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%ubigpks}}', [
            'id' => 'ubigpk',
            'name' => 'string(150)',
            'f' => 'decimal(12,4)',
            'g5' => 'text[]',
            'g6' => 'text[][]',
            'g7' => 'numeric(10,7)',
            'dp double precision',
        ])->execute();

        // ---
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'string(150)',
            'food_of' => 'int'
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%pristines}}', [
            'id' => 'pk',
            'name' => 'string(151)',
            'fruit_id' => 'int', // FK
        ])->execute();
        Yii::$app->db->createCommand()->addForeignKey('name', '{{%pristines}}', 'fruit_id', '{{%fruits}}', 'id')->execute();

        // ---
        Yii::$app->db->createCommand()->createTable('{{%the_animal_table_name}}', [
            'id' => 'pk',
            'name' => 'string(150)',
        ])->execute();
        Yii::$app->db->createCommand()->addForeignKey('name2', '{{%fruits}}', 'food_of', '{{%the_animal_table_name}}', 'id')->execute();
        Yii::$app->db->createCommand()->createTable('{{%the_mango_table_name}}', [
            'id' => 'pk',
            'name' => 'string(150)',
            'food_of' => 'int'
        ])->execute();
        Yii::$app->db->createCommand()->addForeignKey('animal_fruit_fk', '{{%the_mango_table_name}}', 'food_of', '{{%the_animal_table_name}}', 'id')->execute();
    }

    private function deleteTablesForCreateMigrationForDropTable132ForPgsql()
    {
        $this->dropFkIfExists('{{%pristines}}', 'name');
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%pristines}}')->execute();

        $this->dropFkIfExists('{{%fruits}}', 'name2');
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%upks}}')->execute();
        Yii::$app->db->createCommand('DROP TYPE IF EXISTS mood')->execute();
        Yii::$app->db->createCommand('DROP TYPE IF EXISTS enum_itt_upks_e2')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%bigpks}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%ubigpks}}')->execute();

        $this->dropFkIfExists('{{%the_mango_table_name}}', 'animal_fruit_fk');
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%the_mango_table_name}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%the_animal_table_name}}')->execute();
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

    // https://github.com/php-openapi/yii2-openapi/issues/60
    public function test60DescriptionOfAPropertyInSpecMustCorrespondToDbTableColumnComment()
    {
        // MySQL
        $this->deleteTableFor60DescriptionOfAProperty();
        $this->createTableFor60DescriptionOfAProperty();
        $testFile = Yii::getAlias("@specs/issue_fix/60_description_of_a_property_in_spec_must_correspond_to_db_table_column_comment/index.php");
        $this->runGenerator($testFile);
        $this->runActualMigrations();
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/60_description_of_a_property_in_spec_must_correspond_to_db_table_column_comment/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->deleteTableFor60DescriptionOfAProperty();


        // PgSQL
        $this->changeDbToPgsql();
        $this->deleteTableFor60DescriptionOfAProperty();
        $this->createTableFor60DescriptionOfAProperty();
        $this->runGenerator($testFile, 'pgsql');
        $this->runActualMigrations('pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/60_description_of_a_property_in_spec_must_correspond_to_db_table_column_comment/pgsql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->deleteTableFor60DescriptionOfAProperty();
    }

    // https://github.com/php-openapi/yii2-openapi/issues/60
    public function test60ComponentSchemaLevelExtension()
    {


        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test60ComponentSchemaLevelExtension'
  version: 1.0.0
components:
  schemas:
    Fruit:
      type: object
      x-description-is-comment: true
      properties:
        id:
          type: integer
        name:
          type: string
          nullable: false
          description: Hi there
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
        $this->alterColumn('{{%fruits}}', 'name', $this->text()->notNull()->comment('Hi there'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->text()->null());
    }
}

PHP;

        $this->for60($schema, $expected);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/60
    public function test60PropertyLevelExtension()
    {
        $schema = <<<YAML
openapi: 3.0.3
info:
  title: 'test60ComponentSchemaLevelExtension'
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
          x-description-is-comment: true
          description: Hi there
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
        $this->alterColumn('{{%fruits}}', 'name', $this->text()->notNull()->comment('Hi there'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->text()->null());
    }
}

PHP;

        $this->for60($schema, $expected);
    }

    private function for60($spec, $expected)
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'text',
        ])->execute();
        $config = [
            'openApiPath' => 'data://text/plain;base64,' . base64_encode($spec),
            'generateUrls' => false,
            'generateModels' => false,
            'generateControllers' => false,
            'generateMigrations' => true,
            'generateModelFaker' => false,
        ];
        $tmpConfigFile = Yii::getAlias("@runtime") . "/tmp-config.php";
        file_put_contents($tmpConfigFile, '<?php return ' . var_export($config, true) . ';');


        $this->runGenerator($tmpConfigFile);
        $actual = file_get_contents(Yii::getAlias('@app') . '/migrations_mysql_db/m200000_000000_change_table_fruits.php');
        $this->assertSame($expected, $actual);
        $this->runActualMigrations('mysql', 1);
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    private function createTableFor60DescriptionOfAProperty()
    {
        Yii::$app->db->createCommand()->createTable('{{%animals}}', [
            'id' => 'pk',
            'name' => 'text ', # comment "the name"
            'g' => 'text',
            'g2' => 'text',
            'g3' => 'text',
            'g4' => 'text',
            'drop_col' => 'text',
        ])->execute();

        Yii::$app->db->createCommand()->addCommentOnColumn('{{%animals}}', 'name', 'the comment on name col')->execute();
        Yii::$app->db->createCommand()->addCommentOnColumn('{{%animals}}', 'g2', 'the comment on g2 col')->execute();
        Yii::$app->db->createCommand()->addCommentOnColumn('{{%animals}}', 'g3', 'the comment on g3 col remains same')->execute();
        Yii::$app->db->createCommand()->addCommentOnColumn('{{%animals}}', 'g4', 'data type changes but comment remains same')->execute();
    }

    private function deleteTableFor60DescriptionOfAProperty()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%animals}}')->execute();
    }

    // https://github.com/php-openapi/yii2-openapi/issues/3
    public function test3BugAddRemovePropertyAndAtTheSameTimeChangeItAtXIndexes()
    {
        $this->dropTestTableFor3BugAddRemovePropertyAndAtTheSameTimeChangeItAtXIndexes();
        $this->createTestTableFor3BugAddRemovePropertyAndAtTheSameTimeChangeItAtXIndexes();
        $testFile = Yii::getAlias("@specs/issue_fix/3_bug_add_remove_property_and_at_the_same_time_change_it_at_x_indexes/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/3_bug_add_remove_property_and_at_the_same_time_change_it_at_x_indexes/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 1);
        $this->dropTestTableFor3BugAddRemovePropertyAndAtTheSameTimeChangeItAtXIndexes();
    }

    private function createTestTableFor3BugAddRemovePropertyAndAtTheSameTimeChangeItAtXIndexes()
    {
        Yii::$app->db->createCommand()->createTable('{{%addresses}}', [
            'id' => 'pk',
            'name' => 'varchar(64)',
            'shortName' => 'varchar(64)',
            'postalCode' => 'varchar(64)',
        ])->execute();
        Yii::$app->db->createCommand()->createIndex('addresses_shortName_postalCode_key', '{{%addresses}}', ["shortName", "postalCode"], true)->execute();
    }

    private function dropTestTableFor3BugAddRemovePropertyAndAtTheSameTimeChangeItAtXIndexes()
    {
        if ($this->indexExists('addresses_shortName_postalCode_key')) {
            Yii::$app->db->createCommand()->dropIndex('addresses_shortName_postalCode_key', '{{%addresses}}')->execute();
        }
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%addresses}}')->execute();
    }

    // https://github.com/php-openapi/yii2-openapi/issues/29
    public function test29ExtensionFkColumnNameCauseErrorInCaseOfColumnNameWithoutUnderscore()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/29_extension_fk_column_name_cause_error_in_case_of_column_name_without_underscore/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/29_extension_fk_column_name_cause_error_in_case_of_column_name_without_underscore/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/30
    public function test30AddValidationRulesByAttributeNameOrPattern()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/30_add_validation_rules_by_attribute_name_or_pattern/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/30_add_validation_rules_by_attribute_name_or_pattern/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/52
    public function test52BugDependentonAllofWithXFakerFalse()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/52_bug_dependenton_allof_with_x_faker_false/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/52_bug_dependenton_allof_with_x_faker_false/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/53
    public function test53BugInversedReferenceRequireCascade()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/53_bug_inversed_reference_require_cascade/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/53_bug_inversed_reference_require_cascade/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }


    // https://github.com/cebe/yii2-openapi/issues/144
    public function test144MethodsNamingForNonCrudActions()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/144_methods_naming_for_non_crud_actions/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/144_methods_naming_for_non_crud_actions/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/cebe/yii2-openapi/issues/84
    public function test84HowToGenerateControllerCodeWithDistinctMethodNamesInCaseOfPrefixInPaths()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/84_how_to_generate_controller_code_with_distinct_method_names_in_case_of_prefix_in_paths/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/84_how_to_generate_controller_code_with_distinct_method_names_in_case_of_prefix_in_paths/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/20
    public function test20ConsiderOpenApiSpecExamplesInFakeCodeGeneration()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/20_consider_openapi_spec_examples_in_faker_code_generation/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/20_consider_openapi_spec_examples_in_faker_code_generation/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/25
    public function test25GenerateInverseRelations()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/25_generate_inverse_relations/index.php");
        $this->runGenerator($testFile);
        $this->runActualMigrations('mysql', 3);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/25_generate_inverse_relations/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/35
    public function test35ResolveTodoReCheckOptionsRouteInFractalAction()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/35_resolve_todo_re_check_options_route_in_fractal_action/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/35_resolve_todo_re_check_options_route_in_fractal_action/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/35
    public function test35ResolveTodoReCheckOptionsRouteInRestAction()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/35_resolve_todo_re_check_options_route_in_fractal_action/index.php");
        $content = str_replace("'useJsonApi' => true,", "'useJsonApi' => false,", file_get_contents($testFile));
        file_put_contents($testFile, $content);
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/35_resolve_todo_re_check_options_route_in_fractal_action/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/63
    public function test63JustColumnNameRename()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/63_just_column_name_rename/index.php");

        // MySQL
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'text',
            'description' => 'text',
            'colour' => 'text',
        ])->execute();

        $this->runGenerator($testFile);
        $this->runActualMigrations('mysql', 1);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/63_just_column_name_rename/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();

        // PgSQL
        $this->changeDbToPgsql();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'text',
            'description' => 'text',
            'colour' => 'text',
        ])->execute();
        $this->runGenerator($testFile, 'pgsql');
        $this->runActualMigrations('pgsql', 1);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/63_just_column_name_rename/pgsql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    public function test64AddATestForAColumnChangeDataTypeCommentPositionAll3AreChanged()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'text comment "desc"',
            'description' => 'text',
            'col' => 'text',
        ])->execute();

        $testFile = Yii::getAlias("@specs/issue_fix/64_add_a_test_for_a_column_change_data_type_comment_position_all_3_are_changed/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/64_add_a_test_for_a_column_change_data_type_comment_position_all_3_are_changed/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 1);
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    // https://github.com/php-openapi/yii2-openapi/issues/63
    public function test74InvalidSchemaReferenceError()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/74_invalid_schema_reference_error/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/74_invalid_schema_reference_error/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations();
    }

    // https://github.com/php-openapi/yii2-openapi/issues/22
    public function test22BugRulesRequiredIsGeneratedBeforeDefault()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/22_bug_rules_required_is_generated_before_default/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/22_bug_rules_required_is_generated_before_default/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    // https://github.com/php-openapi/yii2-openapi/issues/90
    public function test90ImplementBelongsToRelationsInModels()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/90_implement_belongs_to_relations_in_models/index.php");
        $this->runGenerator($testFile);
//        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
//            'recursive' => true,
//        ]);
//        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/90_implement_belongs_to_relations_in_models/mysql"), [
//            'recursive' => true,
//        ]);
//        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
