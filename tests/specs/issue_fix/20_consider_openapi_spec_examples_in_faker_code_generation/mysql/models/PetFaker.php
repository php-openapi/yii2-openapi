<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Pet
 * @method static Pet makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Pet saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Pet[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Pet[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PetFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Pet|\yii\db\ActiveRecord
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
        $model = new Pet();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->optional(0.92)->sentence ?? 'cat';
        $model->age = $faker->optional(0.92)->numberBetween(0, 1000000) ?? 2;
        $model->tags = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->optional(0.92)->sentence ?? null;
        }, range(1, 4));
        $model->tags_arbit = $faker->optional(0.92)->words() ?? [
            'long-tail',
            'short-tail',
            'black',
            'white',
        ];
        $model->number_arr = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->optional(0.92)->randomFloat() ?? null;
        }, range(1, 4));
        $model->number_arr_min_uniq = array_map(function () use ($faker, $uniqueFaker) {
            return $uniqueFaker->optional(0.92)->randomFloat() ?? null;
        }, range(1, 6));
        $model->int_arr = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        }, range(1, 4));
        $model->int_arr_min_uniq = array_map(function () use ($faker, $uniqueFaker) {
            return $uniqueFaker->optional(0.92)->numberBetween(0, 1000000) ?? null;
        }, range(1, 7));
        $model->bool_arr = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->optional(0.92)->boolean ?? null;
        }, range(1, 4));
        $model->arr_arr_int = array_map(function () use ($faker, $uniqueFaker) {
            return array_map(function () use ($faker, $uniqueFaker) {
                return $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
            }, range(1, 4));
        }, range(1, 4));
        $model->arr_arr_str = array_map(function () use ($faker, $uniqueFaker) {
            return array_map(function () use ($faker, $uniqueFaker) {
                return $faker->optional(0.92)->sentence ?? null;
            }, range(1, 4));
        }, range(1, 4));
        $model->arr_arr_arr_str = array_map(function () use ($faker, $uniqueFaker) {
            return array_map(function () use ($faker, $uniqueFaker) {
                return array_map(function () use ($faker, $uniqueFaker) {
                    return $faker->optional(0.92)->sentence ?? null;
                }, range(1, 5));
            }, range(1, 4));
        }, range(1, 3));
        $model->arr_of_obj = array_map(function () use ($faker, $uniqueFaker) {
            return [
                'id' => $uniqueFaker?->numberBetween(0, 1000000) ?? null,
                'name' => $faker->optional(0.92)->sentence ?? null,
                'age' => $faker->optional(0.92)->numberBetween(0, 200) ?? null,
                'user_id' => $faker->randomElement(\app\models\User::find()->select("id")->column()),
                'user_2' => array_map(function () use ($faker, $uniqueFaker) {
                    return (new UserFaker)->generateModel()->attributes;
                }, range(1, 4)),
                'tags' => array_map(function () use ($faker, $uniqueFaker) {
                    return $uniqueFaker->optional(0.92)->sentence ?? null;
                }, range(1, 4)),
                'arr_arr_int_2' => array_map(function () use ($faker, $uniqueFaker) {
                    return array_map(function () use ($faker, $uniqueFaker) {
                        return $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
                    }, range(1, 11));
                }, range(1, 4)),
                'appearance' => [
                    'height' => $faker->optional(0.92)->numberBetween(0, 20) ?? null,
                    'weight' => $faker->optional(0.92)->numberBetween(0, 1000000) ?? null,
                    'email' => $faker->optional(0.92)->safeEmail ?? null,
                    'nested_obj' => [
                        'id' => $uniqueFaker?->numberBetween(0, 1000000) ?? null,
                        'title' => is_string($s = $faker->optional(0.92)->title) ? substr($s, 0, 4) : null,
                    ],
                ],
            ];
        }, range(1, 3));
        $model->user_ref_obj_arr = array_map(function () use ($faker, $uniqueFaker) {
            return (new UserFaker)->generateModel()->attributes;
        }, range(1, 3));
        $model->one_of_arr = array_map(function () use ($faker, $uniqueFaker) {
            $dataType0 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
            $dataType1 = $faker->optional(0.92)->sentence ?? null;
            $dataType2 = $faker->optional(0.92)->boolean ?? null;
            return ${"dataType".rand(0, 2)};
        }, range(1, 4));
        $model->one_of_arr_complex = array_map(function () use ($faker, $uniqueFaker) {
            $dataType0 = $faker->optional(0.92)->numberBetween(0, 1000000) ?? null;
            $dataType1 = $faker->optional(0.92)->sentence ?? null;
            $dataType2 = $faker->optional(0.92)->boolean ?? null;
            $dataType3 = [];
            $dataType4 = array_map(function () use ($faker, $uniqueFaker) {
                return $faker->optional(0.92)->sentence ?? null;
            }, range(1, 4));
            $dataType5 = [
                'id' => $uniqueFaker?->numberBetween(0, 1000000) ?? null,
            ] ?? null;
            $dataType6 = array_map(function () use ($faker, $uniqueFaker) {
                return (new UserFaker)->generateModel()->attributes;
            }, range(1, 4));
            $dataType7 = (new FruitFaker)->generateModel()->attributes;
            return ${"dataType".rand(0, 7)};
        }, range(1, 8));
        $model->one_of_from_multi_ref_arr = array_map(function () use ($faker, $uniqueFaker) {
            $dataType0 = (new UserFaker)->generateModel()->attributes;
            $dataType1 = (new FruitFaker)->generateModel()->attributes;
            return ${"dataType".rand(0, 1)};
        }, range(1, 4));
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
