<?php
namespace app\models\pgsqlfaker;

use Faker\UniqueGenerator;
use app\models\pgsqlmodel\Pristine;

/**
 * Fake data generator for Pristine
 * @method static \app\models\pgsqlmodel\Pristine makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Pristine saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Pristine[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\pgsqlmodel\Pristine[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PristineFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\pgsqlmodel\Pristine|\yii\db\ActiveRecord
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
        $model = new \app\models\pgsqlmodel\Pristine();
        $model->custom_id_col = $faker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->tag = $faker->optional(0.92)->sentence ?? null;
        $model->new_col = $faker->optional(0.92)->sentence ?? null;
        $model->col_5 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->col_6 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->col_7 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->col_8 = [];
        $model->col_9 = $faker->optional(0.92)->sentence ?? null;
        $model->col_10 = $faker->optional(0.92)->sentence ?? null;
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
