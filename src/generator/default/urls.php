<?php

use yii\helpers\VarDumper;

?>
<?= '<?php' ?>

/**
* OpenAPI UrlRules
*
* This file is auto generated.
*/
<?php $rules = VarDumper::export($urls); ?>
return <?= str_replace('\\\\', '\\', $rules); ?>;
