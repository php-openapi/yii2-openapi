<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Fakerable
 * @method static Fakerable makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Fakerable saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Fakerable[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Fakerable[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class FakerableFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Fakerable|\yii\db\ActiveRecord
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
        $model = new Fakerable();
        //$model->id = $uniqueFaker?->numberBetween(0, 1000000) ?? null;
        $model->active = $faker->optional(0.92)->boolean ?? null;
        $model->floatval = $faker->optional(0.92)->randomFloat() ?? null;
        $model->floatval_lim = $faker->optional(0.92)->randomFloat(null, 0, 1) ?? null;
        $model->doubleval = $faker->optional(0.92)->randomFloat() ?? null;
        $model->int_min = $faker->optional(0.92)->numberBetween(5, 1000000) ?? null;
        $model->int_max = $faker->optional(0.92)->numberBetween(0, 5) ?? null;
        $model->int_minmax = $faker->optional(0.92)->numberBetween(5, 25) ?? null;
        $model->int_created_at = $faker->optional(0.92)->unixTime ?? null;
        $model->int_simple = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->str_text = $faker->optional(0.92)->sentence ?? null;
        $model->str_varchar = is_string($s = $faker->optional(0.92)->text(100)) ? substr($s, 0, 100) : null;
        $model->str_date = $faker->optional(0.92)->dateTimeThisCentury?->format('Y-m-d') ?? null;
        $model->str_date_ex = $faker->optional(0.92)->dateTimeThisCentury?->format('Y-m-d') ?? '2020-03-14';
        $model->str_datetime = $faker->optional(0.92)->dateTimeThisYear('now', 'UTC')?->format('Y-m-d H:i:s') ?? null;
        $model->str_datetime_ex = $faker->optional(0.92)->dateTimeThisYear('now', 'UTC')?->format('Y-m-d H:i:s') ?? '2020-03-14 21:42:17';
        $model->str_country = $faker->optional(0.92)->countryCode ?? null;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
