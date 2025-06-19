<?php

namespace app\fruit2\mango\controllers;

class AlphonsoController extends \app\fruit2\mango\controllers\base\AlphonsoController
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


}

