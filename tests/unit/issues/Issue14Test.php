<?php

namespace tests\unit\issues;

use tests\DbTestCase;
use Yii;
use yii\helpers\FileHelper;

# https://github.com/php-openapi/yii2-openapi/issues/14
class Issue14Test extends DbTestCase
{
    public function testNestedModuleInXRoute()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/14_nested_module_in_x_route/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/14_nested_module_in_x_route/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }

    public function testModuleConfigInUrlPrefixes()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/14_module_config_in_url_prefixes/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/14_module_config_in_url_prefixes/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
