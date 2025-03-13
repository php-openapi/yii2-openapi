<?php

namespace tests\unit\issues;

use tests\DbTestCase;
use Yii;
use yii\helpers\FileHelper;

class Issue100Test extends DbTestCase
{
    // https://github.com/php-openapi/yii2-openapi/issues/100
    public function testFixNumberingIssueInRelationNamesInCaseOfMoreThanOneSimilarBelongsToRelation()
    {
        $testFile = Yii::getAlias("@specs/issue_fix/100_fix_numbering_issue_in_relation_names_in_case_of_more_than_one_similar_belongs_to_relation/index.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/issue_fix/100_fix_numbering_issue_in_relation_names_in_case_of_more_than_one_similar_belongs_to_relation/mysql"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
