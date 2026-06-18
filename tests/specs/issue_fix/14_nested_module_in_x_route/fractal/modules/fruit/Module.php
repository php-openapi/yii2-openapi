<?php

namespace app\fruit;

class Module extends \yii\base\Module
{

    public function init()
    {
        parent::init();
        $this->modules = [
            'mango' => [
                'class' => \app\fruit\mango\Module::class,
            ],
        ];
    }


}

