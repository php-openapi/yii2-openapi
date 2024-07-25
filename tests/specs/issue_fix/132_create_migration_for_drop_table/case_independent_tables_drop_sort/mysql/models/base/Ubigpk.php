<?php

namespace app\models\base;

/**
 *
 *
 * @property string $id
 * @property string $name
 *
 */
abstract class Ubigpk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%ubigpks}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'name_string' => [['name'], 'string', 'max' => 150],
        ];
    }
}
