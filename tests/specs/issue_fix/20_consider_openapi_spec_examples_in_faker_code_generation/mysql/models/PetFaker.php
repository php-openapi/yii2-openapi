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
        $model->name = $faker->optional(0.92, 'cat')->sentence;
        $model->age = $faker->optional(0.92, 2)->numberBetween(0, 1000000);
        $model->tags = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->sentence;
        }, range(1, 4));
        $model->tags_arbit = $faker->optional(0.92, [
    'long-tail',
    'short-tail',
    'black',
    'white',
])->words();
        $model->number_arr = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->randomFloat();
        }, range(1, 4));
        $model->number_arr_min_uniq = array_map(function () use ($faker, $uniqueFaker) {
            return $uniqueFaker->randomFloat();
        }, range(1, 6));
        $model->int_arr = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->numberBetween(0, 1000000);
        }, range(1, 4));
        $model->int_arr_min_uniq = array_map(function () use ($faker, $uniqueFaker) {
            return $uniqueFaker->numberBetween(0, 1000000);
        }, range(1, 7));
        $model->bool_arr = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->boolean;
        }, range(1, 4));
        $model->arr_arr_int = array_map(function () use ($faker, $uniqueFaker) {
            return array_map(function () use ($faker, $uniqueFaker) {
            return $faker->numberBetween(0, 1000000);
        }, range(1, 4));
        }, range(1, 4));
        $model->arr_arr_str = array_map(function () use ($faker, $uniqueFaker) {
            return array_map(function () use ($faker, $uniqueFaker) {
            return $faker->sentence;
        }, range(1, 4));
        }, range(1, 4));
        $model->arr_arr_arr_str = array_map(function () use ($faker, $uniqueFaker) {
            return array_map(function () use ($faker, $uniqueFaker) {
            return array_map(function () use ($faker, $uniqueFaker) {
            return $faker->sentence;
        }, range(1, 5));
        }, range(1, 4));
        }, range(1, 3));
        $model->arr_of_obj = array_map(function () use ($faker, $uniqueFaker) {
            return [
'id' => $uniqueFaker->numberBetween(0, 1000000),
'name' => $faker->sentence,
'age' => $faker->numberBetween(0, 200),
'user' => $faker->randomElement(\app\models\User::find()->select("id")->column()),
'user_2' => array_map(function () use ($faker, $uniqueFaker) {
            return (new UserFaker)->generateModel()->attributes;
        }, range(1, 4)),
'tags' => array_map(function () use ($faker, $uniqueFaker) {
            return $uniqueFaker->sentence;
        }, range(1, 4)),
'arr_arr_int_2' => array_map(function () use ($faker, $uniqueFaker) {
            return array_map(function () use ($faker, $uniqueFaker) {
            return $faker->numberBetween(0, 1000000);
        }, range(1, 11));
        }, range(1, 4)),
'appearance' => [
'height' => $faker->numberBetween(0, 20),
'weight' => $faker->numberBetween(0, 1000000),
'email' => $faker->safeEmail,
'nested_obj' => [
'id' => $uniqueFaker->numberBetween(0, 1000000),
'title' => $faker->title,
],
],
];
        }, range(1, 3));
        $model->user_ref_obj_arr = array_map(function () use ($faker, $uniqueFaker) {
            return (new UserFaker)->generateModel()->attributes;
        }, range(1, 3));
        $model->one_of_arr = array_map(function () use ($faker, $uniqueFaker) {
            $dataType0 = $faker->numberBetween(0, 1000000);$dataType1 = $faker->sentence;$dataType2 = $faker->boolean;return ${"dataType".rand(0, 2)};
        }, range(1, 4));
        $model->one_of_arr_complex = array_map(function () use ($faker, $uniqueFaker) {
            $dataType0 = $faker->numberBetween(0, 1000000);$dataType1 = $faker->sentence;$dataType2 = $faker->boolean;$dataType3 = $faker->words();$dataType4 = array_map(function () use ($faker, $uniqueFaker) {
            return $faker->sentence;
        }, range(1, 4));$dataType5 = [
'id' => $uniqueFaker->numberBetween(0, 1000000),
];$dataType6 = array_map(function () use ($faker, $uniqueFaker) {
            return (new UserFaker)->generateModel()->attributes;
        }, range(1, 4));$dataType7 = (new FruitFaker)->generateModel()->attributes;return ${"dataType".rand(0, 7)};
        }, range(1, 8));
        $model->one_of_from_multi_ref_arr = array_map(function () use ($faker, $uniqueFaker) {
            $dataType0 = (new UserFaker)->generateModel()->attributes;$dataType1 = (new FruitFaker)->generateModel()->attributes;return ${"dataType".rand(0, 1)};
        }, range(1, 4));
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
