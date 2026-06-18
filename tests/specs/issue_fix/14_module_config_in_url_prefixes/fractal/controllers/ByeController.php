<?php

namespace app\controllers;

class ByeController extends \app\controllers\base\ByeController
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

    public function actionCreate()
    {
        //TODO implement actionCreate
    }


}

