<?php

namespace app\modules\fgh\controllers;

class PetDetail2Controller extends \app\modules\fgh\controllers\base\PetDetail2Controller
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

