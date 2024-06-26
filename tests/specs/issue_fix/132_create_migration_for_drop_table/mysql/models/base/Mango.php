<?php

namespace app\models\base;

/**
 *
 *
 * @property integer $id
 * @property string $name
 * @property integer $food_of
 *
 */
abstract class Mango extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%the_mango_table_name}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'name_string' => [['name'], 'string', 'max' => 150],
            'food_of_integer' => [['food_of'], 'integer'],
        ];
    }
}
