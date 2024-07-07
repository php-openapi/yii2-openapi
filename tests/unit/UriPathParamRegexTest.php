<?php

namespace tests\unit;

use tests\DbTestCase;
use Yii;
use yii\helpers\FileHelper;

class UriPathParamRegexTest extends DbTestCase
{
    public function testIndex()
    {
        $testFile = Yii::getAlias("@specs/uri_path_param_regex/uri_path_param_regex.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/uri_path_param_regex/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
