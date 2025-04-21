<?php

namespace app\modules\petinfo\controllers;

class PetDetailController extends \app\modules\petinfo\controllers\base\PetDetailController
{

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        //TODO implement checkAccess
    }

    public function actionList()
    {
        //TODO implement actionList
    }


}

