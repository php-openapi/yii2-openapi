<?php

namespace app\models\base;

/**
 *
 *
 * @property string $id
 * @property string $name
 * @property string $size
 * @property integer $d
 * @property integer $e
 * @property string $f
 * @property double $dp
 * @property double $dp2
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
            'trim' => [['name', 'f'], 'trim'],
            'name_string' => [['name'], 'string', 'max' => 150],
            'size_string' => [['size'], 'string'],
            'size_in' => [['size'], 'in', 'range' => [
                'x-small',
                'small',
                'medium',
                'large',
                'x-large',
            ]],
            'size_default' => [['size'], 'default', 'value' => 'x-small'],
            'd_integer' => [['d'], 'integer'],
            'e_integer' => [['e'], 'integer'],
            'f_string' => [['f'], 'string', 'max' => 12],
            'dp_double' => [['dp'], 'double'],
            'dp2_double' => [['dp2'], 'double'],
        ];
    }
}
