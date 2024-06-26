<?php

namespace app\models\base;

/**
 *
 *
 * @property integer $id
 * @property string $name
 *
 */
abstract class Animal extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%the_animal_table_name}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'name_string' => [['name'], 'string', 'max' => 150],
        ];
    }
}
