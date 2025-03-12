<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Payments
 * @method static Payments makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Payments saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Payments[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Payments[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PaymentsFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Payments|\yii\db\ActiveRecord
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
        $model = new Payments();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->currency = $faker->currencyCode;
        $model->samples = array_map(function () use ($faker, $uniqueFaker) {
            return (new SampleFaker)->generateModel()->attributes;
        }, range(1, 4));
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
