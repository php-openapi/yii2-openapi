<?php
/**@var FractalAction $action */

use cebe\yii2openapi\lib\items\FractalAction;

?>
$model = $this-><?= $action->findModelMethodName ?>($<?= $action->idParam ?>);
$this->checkAccess('<?= $action->id ?>', $model);
if ($model->delete() === false) {
throw new \yii\web\ServerErrorHttpException('Failed to delete the object for unknown reason.');
}
Yii::$app->getResponse()->setStatusCode(204);
