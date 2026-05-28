<?php
namespace app\models\mariafaker;

use Faker\UniqueGenerator;
use app\models\mariamodel\Editcolumn;

/**
 * Fake data generator for Editcolumn
 * @method static \app\models\mariamodel\Editcolumn makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\mariamodel\Editcolumn saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\mariamodel\Editcolumn[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\mariamodel\Editcolumn[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class EditcolumnFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\mariamodel\Editcolumn|\yii\db\ActiveRecord
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
        $model = new \app\models\mariamodel\Editcolumn();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = substr($faker->text(254), 0, 254);
        $model->tag = $faker->optional(0.92)->sentence ?? null;
        $model->first_name = is_string($s = $faker->optional(0.92)->text(255)) ? substr($s, 0, 255) : null;
        $model->string_col = $faker->optional(0.92)->sentence ?? null;
        $model->dec_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->str_col_def = substr($faker->word(3), 0, 3);
        $model->json_col = $faker->sentence;
        $model->json_col_2 = $faker->words();
        $model->numeric_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->json_col_def_n = [];
        $model->json_col_def_n_2 = [];
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
