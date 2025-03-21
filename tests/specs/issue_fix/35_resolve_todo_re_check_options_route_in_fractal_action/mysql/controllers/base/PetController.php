<?php
namespace app\controllers\base;

use insolita\fractal\JsonApiController;
use Yii;

abstract class PetController extends JsonApiController
{
    public function actions()
    {
        return [
            'view' => [
                'class' => \insolita\fractal\actions\ViewAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PetTransformer::class,
                'modelClass' => \app\models\Pet::class,
                'resourceKey' => 'pets',
                'findModel' => null
            ],
            'delete' => [
                'class' => \insolita\fractal\actions\DeleteAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'modelClass' => \app\models\Pet::class,
                'findModel' => null
          ],
            'update' => [
                'class' => \insolita\fractal\actions\UpdateAction::class,
                'checkAccess' => [$this, 'checkAccess'],
                'transformer' => \app\transformers\PetTransformer::class,
                'modelClass' => \app\models\Pet::class,
                'resourceKey' => 'pets',
                'findModel' => null,
                'allowedRelations'=>[],
                'scenario' => 'default'
            ],
            'options' => [
                'class' => \yii\rest\OptionsAction::class,
            ],
        ];
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method checks whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws \yii\web\ForbiddenHttpException if the user does not have access
     */
    abstract public function checkAccess($action, $model = null, $params = []);
}
