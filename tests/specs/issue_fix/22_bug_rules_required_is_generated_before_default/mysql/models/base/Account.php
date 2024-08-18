<?php

namespace app\models\base;

/**
 * Account
 *
 * @property int $id
 * @property string $name account name
 * @property string $paymentMethodName
 * @property bool $verified
 *
 */
abstract class Account extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%accounts}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name', 'paymentMethodName'], 'trim'],
            'name_string' => [['name'], 'string', 'max' => 128],
            'paymentMethodName_string' => [['paymentMethodName'], 'string'],
            'verified_boolean' => [['verified'], 'boolean'],
            'verified_default' => [['verified'], 'default', 'value' => false],
            'required' => [['name', 'verified'], 'required'],
        ];
    }
}
