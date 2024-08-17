<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $name
 *
 */
abstract class Fruit extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%fruits}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'name_string' => [['name'], 'string'],
        ];
    }
}
