<?php
namespace app\models\pgsqlfaker;

use Faker\UniqueGenerator;
use app\models\pgsqlmodel\Alldbdatatype;

/**
 * Fake data generator for Alldbdatatype
 * @method static \app\models\pgsqlmodel\Alldbdatatype makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Alldbdatatype saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\pgsqlmodel\Alldbdatatype[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\pgsqlmodel\Alldbdatatype[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class AlldbdatatypeFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\pgsqlmodel\Alldbdatatype|\yii\db\ActiveRecord
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
        $model = new \app\models\pgsqlmodel\Alldbdatatype();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->string_col = $faker->optional(0.92)->sentence ?? null;
        $model->varchar_col = $faker->optional(0.92)->sentence ?? null;
        $model->text_col = $faker->optional(0.92)->sentence ?? null;
        $model->text_col_array = [];
        $model->varchar_4_col = is_string($s = $faker->optional(0.92)->word(4)) ? substr($s, 0, 4) : null;
        $model->varchar_5_col = is_string($s = $faker->optional(0.92)->text(5)) ? substr($s, 0, 5) : null;
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
        $model->int2_col = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->smallserial_col = $faker->numberBetween(0, 1000000);
        $model->serial2_col = $faker->numberBetween(0, 1000000);
        $model->si_col = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->si_col_2 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bi = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bi2 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->int4_col = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bigserial_col = $faker->numberBetween(0, 1000000);
        $model->bigserial_col_2 = $faker->numberBetween(0, 1000000);
        $model->int_col = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->int_col_2 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->numeric_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->numeric_col_2 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->numeric_col_3 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->double_p_2 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->double_p_3 = $faker->optional(0.92)->randomFloat() ?? null;
        $model->real_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->float4_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->date_col = $faker->optional(0.92)->dateTimeThisCentury?->format('Y-m-d') ?? null;
        $model->time_col = $faker->optional(0.92)->time('H:i:s') ?? null;
        $model->time_col_2 = $faker->optional(0.92)->sentence ?? null;
        $model->time_col_3 = $faker->optional(0.92)->sentence ?? null;
        $model->time_col_4 = is_string($s = $faker->optional(0.92)->word(3)) ? substr($s, 0, 3) : null;
        $model->timetz_col = $faker->optional(0.92)->sentence ?? null;
        $model->timetz_col_2 = is_string($s = $faker->optional(0.92)->word(3)) ? substr($s, 0, 3) : null;
        $model->timestamp_col = $faker->optional(0.92)->dateTimeThisYear('now', 'UTC')?->format('Y-m-d H:i:s') ?? null;
        $model->timestamp_col_2 = $faker->optional(0.92)->unixTime ?? null;
        $model->timestamp_col_3 = $faker->optional(0.92)->unixTime ?? null;
        $model->timestamp_col_4 = is_string($s = $faker->optional(0.92)->unixTime) ? substr($s, 0, 3) : null;
        $model->timestamptz_col = $faker->optional(0.92)->unixTime ?? null;
        $model->timestamptz_col_2 = is_string($s = $faker->optional(0.92)->unixTime) ? substr($s, 0, 3) : null;
        $model->date2 = $faker->optional(0.92)->dateTimeThisCentury?->format('Y-m-d') ?? null;
        $model->timestamp_col_z = $faker->optional(0.92)->dateTimeThisYear('now', 'UTC')?->format('Y-m-d H:i:s') ?? null;
        $model->bit_varying = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bit_varying_n = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bit_varying_n_2 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->bit_varying_n_3 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->box_col = $faker->optional(0.92)->sentence ?? null;
        $model->character_col = $faker->optional(0.92)->sentence ?? null;
        $model->character_n = is_string($s = $faker->optional(0.92)->text(12)) ? substr($s, 0, 12) : null;
        $model->character_varying = $faker->optional(0.92)->sentence ?? null;
        $model->character_varying_n = is_string($s = $faker->optional(0.92)->text(12)) ? substr($s, 0, 12) : null;
        $model->json_col = [];
        $model->jsonb_col = [];
        $model->json_col_def = [];
        $model->json_col_def_2 = [];
        $model->text_def = $faker->optional(0.92)->sentence ?? null;
        $model->json_def = [];
        $model->jsonb_def = [];
        $model->cidr_col = $faker->optional(0.92)->sentence ?? null;
        $model->circle_col = $faker->optional(0.92)->sentence ?? null;
        $model->date_col_z = $faker->optional(0.92)->dateTimeThisCentury?->format('Y-m-d') ?? null;
        $model->float8_col = $faker->optional(0.92)->randomFloat() ?? null;
        $model->inet_col = $faker->optional(0.92)->sentence ?? null;
        $model->interval_col = $faker->optional(0.92)->sentence ?? null;
        $model->interval_col_2 = $faker->optional(0.92)->sentence ?? null;
        $model->interval_col_3 = is_string($s = $faker->optional(0.92)->word(3)) ? substr($s, 0, 3) : null;
        $model->line_col = $faker->optional(0.92)->sentence ?? null;
        $model->lseg_col = $faker->optional(0.92)->sentence ?? null;
        $model->macaddr_col = $faker->optional(0.92)->sentence ?? null;
        $model->money_col = $faker->optional(0.92)->sentence ?? null;
        $model->path_col = $faker->optional(0.92)->sentence ?? null;
        $model->pg_lsn_col = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        $model->point_col = $faker->optional(0.92)->sentence ?? null;
        $model->polygon_col = $faker->optional(0.92)->sentence ?? null;
        $model->serial_col = $faker->numberBetween(0, 1000000);
        $model->serial4_col = $faker->numberBetween(0, 1000000);
        $model->tsquery_col = $faker->optional(0.92)->sentence ?? null;
        $model->tsvector_col = $faker->optional(0.92)->sentence ?? null;
        $model->txid_snapshot_col = $faker->optional(0.92)->sentence ?? null;
        $model->uuid_col = $faker->optional(0.92)->sentence ?? null;
        $model->xml_col = $faker->optional(0.92)->sentence ?? null;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
