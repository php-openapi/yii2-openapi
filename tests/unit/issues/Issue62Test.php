<?php

namespace tests\unit\issues;

use tests\DbTestCase;
use Yii;
use yii\helpers\FileHelper;

# https://github.com/php-openapi/yii2-openapi/issues/62
class Issue62Test extends DbTestCase
{
    public function testIndex()
    {
        $this->changeDbToPgsql();

        $testFile = Yii::getAlias("@specs/issue_fix/62_unnecessary_sql_statement_for_comment_on_column_in_pgsql/index.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/62_unnecessary_sql_statement_for_comment_on_column_in_pgsql/pgsql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
