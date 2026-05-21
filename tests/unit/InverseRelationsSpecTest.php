<?php

namespace tests\unit;

use tests\DbTestCase;
use Yii;
use yii\helpers\FileHelper;

/**
 * Spec-based test for inverse relation generation in dbmodel.php.
 *
 * Runs the generator against a real spec file and compares all generated files
 * against expected fixtures in tests/specs/issue_fix/inverse_relations/mysql/.
 *
 * Points 1–5 (FK-based naming, no numeric suffixes, @property annotations,
 * x-db-type: false abstract method, x-table: false exclusion) are all verified
 * implicitly by the full file comparison via checkFiles().
 */
class InverseRelationsSpecTest extends DbTestCase
{
    public function testInverseRelations(): void
    {
        $testFile = Yii::getAlias('@specs/issue_fix/inverse_relations/index.php');
        $this->runGenerator($testFile);

        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['*VirtualTask*'],
        ]);
        $expectedFiles = FileHelper::findFiles(
            Yii::getAlias('@specs/issue_fix/inverse_relations/mysql'),
            ['recursive' => true, 'except' => ['*VirtualTask*']]
        );
        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
