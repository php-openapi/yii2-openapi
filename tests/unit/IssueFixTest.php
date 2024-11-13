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

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%ubigpks}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%bigpks}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%upks}}')->execute();
    }

    // Create migration for drop table if a entire schema is deleted from OpenAPI spec #132
    // https://github.com/cebe/yii2-openapi/issues/132
    public function testCreateMigrationForDropTable132()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/132_create_migration_for_drop_table/132_create_migration_for_drop_table.php");
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
        Yii::$app->db->createCommand()->dropForeignKey('name', '{{%pristines}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%pristines}}')->execute();
        Yii::$app->db->createCommand()->dropForeignKey('name2', '{{%fruits}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%upks}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%bigpks}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%ubigpks}}')->execute();

        Yii::$app->db->createCommand()->dropForeignKey('animal_fruit_fk', '{{%the_mango_table_name}}')->execute();
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
        Yii::$app->db->createCommand()->dropForeignKey('name', '{{%pristines}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%pristines}}')->execute();
        Yii::$app->db->createCommand()->dropForeignKey('name2', '{{%fruits}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%upks}}')->execute();
        Yii::$app->db->createCommand('DROP TYPE mood')->execute();
        Yii::$app->db->createCommand('DROP TYPE enum_itt_upks_e2')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%bigpks}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%ubigpks}}')->execute();

        Yii::$app->db->createCommand()->dropForeignKey('animal_fruit_fk', '{{%the_mango_table_name}}')->execute();
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
}
