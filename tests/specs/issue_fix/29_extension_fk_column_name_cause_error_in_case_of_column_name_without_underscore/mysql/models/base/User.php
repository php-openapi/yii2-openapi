<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $name
 *
 */
abstract class User extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%users}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'name_string' => [['name'], 'string'],
        ];
    }
}
