<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property string $name name
 * @property string $photo
 * @property string $profile_photo
 * @property string $pdf
 * @property string $a_file
 * @property string $profile
 *
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
            'trim' => [['name', 'photo', 'profile_photo', 'pdf', 'a_file', 'profile'], 'trim'],
            'required' => [['name'], 'required'],
            'name_string' => [['name'], 'string', 'max' => 128],
            'photo_image' => [['photo'], 'image'],
            'profile_photo_image' => [['profile_photo'], 'image'],
            'pdf_file' => [['pdf'], 'file'],
            'a_file_file' => [['a_file'], 'file'],
            'profile_string' => [['profile'], 'string'],
        ];
    }
}
