<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for JsonArrObj
 * @method static JsonArrObj makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static JsonArrObj saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static JsonArrObj[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static JsonArrObj[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class JsonArrObjFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return JsonArrObj|\yii\db\ActiveRecord
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
        $model = new JsonArrObj();
        //$model->id = $uniqueFaker?->numberBetween(0, 1000000) ?? null;
        $model->arr_no_items = [];
        $model->obj_no_props = (object) [];
        $model->arr_obj_no_props = [];
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
