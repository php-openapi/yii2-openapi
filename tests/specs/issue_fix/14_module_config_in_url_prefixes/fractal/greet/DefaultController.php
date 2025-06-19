<?php

namespace app\greet;

class DefaultController extends \app\greet\base\DefaultController
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

    public function action()
    {
        //TODO implement action
    }


}

