<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 *
 */
abstract class Invoice extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%invoices}}';
    }

    public function rules()
    {
        return [];
    }

    public function getOrder()
    {
        return $this->hasOne(\app\models\Order::class, ['invoice_id' => 'id'])->inverseOf('invoice');
    }
}
