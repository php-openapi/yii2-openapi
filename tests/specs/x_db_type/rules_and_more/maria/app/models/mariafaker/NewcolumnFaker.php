<?php
namespace app\models\mariafaker;

use Faker\UniqueGenerator;
use app\models\mariamodel\Newcolumn;

/**
 * Fake data generator for Newcolumn
 * @method static \app\models\mariamodel\Newcolumn makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\mariamodel\Newcolumn saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\mariamodel\Newcolumn[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\mariamodel\Newcolumn[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class NewcolumnFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\mariamodel\Newcolumn|\yii\db\ActiveRecord
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
        $model = new \app\models\mariamodel\Newcolumn();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = substr($faker->text(255), 0, 255);
        $model->last_name = $faker->sentence;
        $model->dec_col = $faker->randomFloat();
        $model->json_col = $faker->words();
        $model->varchar_col = substr($faker->text(5), 0, 5);
        $model->numeric_col = $faker->randomFloat();
        $model->json_col_def_n = $faker->words();
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
