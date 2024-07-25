<?php

namespace app\models\base;

/**
 *
 *
 * @property integer $id
 * @property string $name
 *
 */
abstract class Upk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%upks}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'name_string' => [['name'], 'string', 'max' => 150],
        ];
    }
}
