<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $content
 * @property int $user
 *
 * @property \app\models\User $userRel
 */
abstract class Post extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%posts}}';
    }

    public function rules()
    {
        return [
            'trim' => [['content'], 'trim'],
            'user_integer' => [['user'], 'integer'],
            'user_exist' => [['user'], 'exist', 'targetRelation' => 'userRel'],
            'content_string' => [['content'], 'string'],
        ];
    }

    public function getUserRel()
    {
        return $this->hasOne(\app\models\User::class, ['id' => 'user']);
    }
}
