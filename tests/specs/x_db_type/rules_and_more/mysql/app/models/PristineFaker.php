<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Pristine
 * @method static Pristine makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Pristine saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Pristine[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Pristine[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PristineFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Pristine|\yii\db\ActiveRecord
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
        $model = new Pristine();
        $model->custom_id_col = $faker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->tag = $faker->optional(0.92)->sentence ?? null;
        $model->new_col = is_string($s = $faker->optional(0.92)->text(17)) ? substr($s, 0, 17) : null;
        $model->col_5 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->col_6 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->col_7 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->col_8 = [];
        $model->col_9 = is_string($s = $faker->optional(0.92)->text(9)) ? substr($s, 0, 9) : null;
        $model->col_10 = is_string($s = $faker->optional(0.92)->text(10)) ? substr($s, 0, 10) : null;
        $model->col_11 = $faker->optional(0.92)->sentence ?? null;
        $model->price = $faker->optional(0.92)->randomFloat() ?? null;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
