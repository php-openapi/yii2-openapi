<?php
namespace app\models\mariafaker;

use Faker\UniqueGenerator;
use app\models\mariamodel\Alldbdatatype;

/**
 * Fake data generator for Alldbdatatype
 * @method static \app\models\mariamodel\Alldbdatatype makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\mariamodel\Alldbdatatype saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\mariamodel\Alldbdatatype[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\mariamodel\Alldbdatatype[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class AlldbdatatypeFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\mariamodel\Alldbdatatype|\yii\db\ActiveRecord
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
        $model = new \app\models\mariamodel\Alldbdatatype();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->string_col = is_string($s = $faker->optional(0.92)->text(255)) ? substr($s, 0, 255) : null;
        $model->varchar_col = is_string($s = $faker->optional(0.92)->text(132)) ? substr($s, 0, 132) : null;
        $model->text_col = $faker->optional(0.92)->sentence ?? null;
        $model->varchar_4_col = is_string($s = $faker->optional(0.92)->word(4)) ? substr($s, 0, 4) : null;
        $model->char_4_col = is_string($s = $faker->optional(0.92)->word(4)) ? substr($s, 0, 4) : null;
        $model->char_5_col = $faker->optional(0.92)->sentence ?? null;
        $model->char_6_col = $faker->sentence;
        $model->char_7_col = substr($faker->text(6), 0, 6);
        $model->char_8_col = $faker->optional(0.92)->sentence ?? null;
        $model->decimal_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->bit_col = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bit_2 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bit_3 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->ti = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->ti_2 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->ti_3 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->si_col = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->si_col_2 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->mi = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bi = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->int_col = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->int_col_2 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->numeric_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->float_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->float_2 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->float_3 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->double_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->double_p = $faker->optional(0.92)->randomFloat() ?? null;
        $model->double_p_2 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->real_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->date_col = $faker->optional(0.92)->dateTimeThisCentury?->format('Y-m-d') ?? null;
        $model->time_col = $faker->optional(0.92)->time('H:i:s') ?? null;
        $model->datetime_col = $faker->optional(0.92)->dateTimeThisYear('now', 'UTC')?->format('Y-m-d H:i:s') ?? null;
        $model->timestamp_col = $faker->optional(0.92)->dateTimeThisYear('now', 'UTC')?->format('Y-m-d H:i:s') ?? null;
        $model->year_col = $faker->optional(0.92)->year ?? null;
        $model->json_col = [];
        $model->json_col_def = [];
        $model->json_col_def_2 = [];
        $model->text_def = $faker->optional(0.92)->sentence ?? null;
        $model->json_def = [];
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
