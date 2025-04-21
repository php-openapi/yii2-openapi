<?php

namespace app\fruit2;

class Module extends \yii\base\Module
{

    public function init()
    {
        parent::init();
        $this->modules = [
            'mango' => [
                'class' => \app\fruit2\mango\Module::class,
            ],
        ];
    }


}

