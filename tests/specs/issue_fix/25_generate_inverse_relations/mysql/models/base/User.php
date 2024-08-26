<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $name
 *
 * @property array|\app\models\Account[] $accounts
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
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string', 'max' => 128],
        ];
    }

    public function getAccounts()
    {
        return $this->hasMany(\app\models\Account::class, ['user_id' => 'id'])->inverseOf('user');
    }

    public function getAccount()
    {
        return $this->hasOne(\app\models\Account::class, ['user_id' => 'id'])->inverseOf('user');
    }

    public function getAccount2()
    {
        return $this->hasOne(\app\models\Account::class, ['user2_id' => 'id'])->inverseOf('user2');
    }

    public function getAccount3()
    {
        return $this->hasOne(\app\models\Account::class, ['user3' => 'id'])->inverseOf('user3');
    }
}
