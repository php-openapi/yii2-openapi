<?php

namespace app\controllers;

class ContactController extends \app\controllers\base\ContactController
{

    public function checkAccess($action, $model = null, $params = [])
    {
        //TODO implement checkAccess
    }

    public function actionListForAccount($accountId)
    {
        //TODO implement actionListForAccount
        // In order to conform with OpenAPI spec, response of this action must have one of the following HTTP status code: 200, 403
    }

    public function actionViewForAccount($accountId, $contactId)
    {
        //TODO implement actionViewForAccount
        // In order to conform with OpenAPI spec, response of this action must have one of the following HTTP status code: 200, 403
    }


}

