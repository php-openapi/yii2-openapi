<?php

namespace app\controllers;

class CommentController extends \app\controllers\base\CommentController
{

    public function checkAccess($action, $model = null, $params = [])
    {
        //TODO implement checkAccess
    }

    public function actionListForPost($postId)
    {
        //TODO implement actionListForPost
    }

    public function actionCreateForPost($postId)
    {
        //TODO implement actionCreateForPost
        // In order to conform with OpenAPI spec, response of this action must have one of the following HTTP status code: 201, default
    }

    public function actionViewForPost($slug, $id)
    {
        //TODO implement actionViewForPost
    }

    public function actionDeleteForPost($slug, $id)
    {
        //TODO implement actionDeleteForPost
        // In order to conform with OpenAPI spec, response of this action must have one of the following HTTP status code: 204
    }

    public function actionUpdateForPost($slug, $id)
    {
        //TODO implement actionUpdateForPost
    }


}

