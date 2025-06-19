<?php

namespace app\forum2\controllers;

class Pet3DetailController extends \app\forum2\controllers\base\Pet3DetailController
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

