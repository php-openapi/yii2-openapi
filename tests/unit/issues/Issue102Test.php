<?php

namespace tests\unit\issues;

use tests\DbTestCase;
use Yii;

class Issue102Test extends DbTestCase
{
    // https://github.com/php-openapi/yii2-openapi/issues/102
    public function test102FractalactionNotGeneratedForRootPath()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/102_fractalaction_not_generated_for_root_path/index.php");

        $this->runGenerator($testFile);
        // TODO
//        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
//            'recursive' => true,
//        ]);
//        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/102_fractalaction_not_generated_for_root_path/mysql"), [
//            'recursive' => true,
//        ]);
//        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
