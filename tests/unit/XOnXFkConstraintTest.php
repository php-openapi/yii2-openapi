<?php

namespace tests\unit;

use tests\DbTestCase;
use Yii;
use yii\helpers\FileHelper;

class XOnXFkConstraintTest extends DbTestCase
{
    public function testIndex()
    {
        // default DB is Mysql ----------------------------------------
        $this->changeDbToPgsql();
        $testFile = Yii::getAlias("@specs/x_on_x_fk_constraint/x_on_x_fk_constraint.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_mysql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_on_x_fk_constraint/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 2);
    }
}
