<?php

namespace app\models\base;

/**
 *
 *
 * @property integer $id
 * @property string $name
 * @property string $current_mood
 * @property string $e2
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
            'current_mood_string' => [['current_mood'], 'string'],
            'current_mood_in' => [['current_mood'], 'in', 'range' => [
                'sad',
                'ok',
                'happy',
            ]],
            'e2_string' => [['e2'], 'string'],
            'e2_in' => [['e2'], 'in', 'range' => [
                'sad2',
                'ok2',
                'happy2',
            ]],
        ];
    }
}
