<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Newcolumn
 * @method static Newcolumn makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Newcolumn saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Newcolumn[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Newcolumn[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class NewcolumnFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Newcolumn|\yii\db\ActiveRecord
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
        $model = new Newcolumn();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = substr($faker->text(255), 0, 255);
        $model->last_name = $faker->optional(0.92)->sentence ?? null;
        $model->dec_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->json_col = [];
        $model->varchar_col = is_string($s = $faker->optional(0.92)->text(5)) ? substr($s, 0, 5) : null;
        $model->numeric_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->json_col_def_n = [];
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
