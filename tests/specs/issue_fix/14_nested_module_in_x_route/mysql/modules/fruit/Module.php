<?php

namespace app\fruit;

class Module extends \yii\base\Module
{

    public function init()
    {
        parent::init();
        $this->modules = [
            'mango' => [
                // you should consider using a shorter namespace here!
                'class' => \app\fruit\mango\Module::class,
            ],
        ];
    }


}

