<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Sample
 * @method static Sample makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Sample saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Sample[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Sample[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class SampleFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Sample|\yii\db\ActiveRecord
     * @example
     *  $model = (new PostFaker())->generateModels(['author_id' => 1]);
     *  $model = (new PostFaker())->generateModels(function($model, $faker, $uniqueFaker) {
     *            $model->scenario = 'create';
     *            $model->author_id = 1;
     *            return $model;
     *  });
    **/
    public function generateModel($attributes = [])
    {
        $faker = $this->faker;
        $uniqueFaker = $this->uniqueFaker;
        $model = new Sample();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->message = $faker->sentence;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
