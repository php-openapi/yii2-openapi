<?php

namespace app\models\base;

/**
 *
 *
 * @property integer $id
 * @property string $name
 * @property integer $fruit_id
 *
 */
abstract class Pristine extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%pristines}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'name_string' => [['name'], 'string', 'max' => 151],
            'fruit_id_integer' => [['fruit_id'], 'integer'],
        ];
    }
}
