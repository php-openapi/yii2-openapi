<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\FakerStubResolver;
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\openapi\PropertySchema;
use cebe\yii2openapi\lib\openapi\ComponentSchema;
use tests\TestCase;
use Yii;
use yii\db\Schema as YiiDbSchema;

class FakerStubResolverTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     **/
    public function testResolve(Attribute $column, PropertySchema $property, $expected)
    {
        $resolver = Yii::createObject(['class' => FakerStubResolver::class], [$column, $property]);
        self::assertEquals($expected, $resolver->resolve());
    }

    public function dataProvider()
    {
        $schemaFile = Yii::getAlias("@specs/blog.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $openApiSchema = $openApi->components->schemas['Fakerable'];
        $schema = new ComponentSchema($openApiSchema, 'Fakerable');
        return [
            [
                (new Attribute('id'))->setPhpType('int')->setDbType(YiiDbSchema::TYPE_BIGPK)->setRequired(),
                $schema->getProperty('id'),
                '$uniqueFaker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('someint'))->setPhpType('int')->setDbType(YiiDbSchema::TYPE_BIGPK)->setRequired(),
                $schema->getProperty('id'),
                '$faker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('active'))->setPhpType('bool')->setDbType(YiiDbSchema::TYPE_BOOLEAN)->setRequired(),
                $schema->getProperty('active'),
                '$faker->boolean',
            ],
            [
                (new Attribute('floatval'))->setPhpType('float')->setDbType(YiiDbSchema::TYPE_FLOAT)->setRequired(),
                $schema->getProperty('floatval'),
                '$faker->randomFloat()',
            ],
            [
                (new Attribute('doubleval'))
                    ->setPhpType($schema->getProperty('doubleval')->guessPhpType())
                    ->setDbType($schema->getProperty('doubleval')->guessDbType())
                    ->setRequired(),
                $schema->getProperty('doubleval'),
                '$faker->randomFloat()',
            ],
            [
                (new Attribute('floatval_lim'))
                    ->setPhpType('float')->setDbType(YiiDbSchema::TYPE_FLOAT)
                    ->setLimits(0, 1, null)->setRequired(),
                $schema->getProperty('floatval_lim'),
                '$faker->randomFloat(null, 0, 1)',
            ],
            [
                (new Attribute('int_simple'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)->setRequired(),
                $schema->getProperty('int_simple'),
                '$faker->numberBetween(0, 1000000)',
            ],
            [
                (new Attribute('int_created_at'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)->setRequired(),
                $schema->getProperty('int_created_at'),
                '$faker->unixTime',
            ],
            [
                (new Attribute('int_min'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(5, null, null)->setRequired(),
                $schema->getProperty('int_min'),
                '$faker->numberBetween(5, 1000000)',
            ],
            [
                (new Attribute('int_max'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(null, 5, null)->setRequired(),
                $schema->getProperty('int_max'),
                '$faker->numberBetween(0, 5)',
            ],
            [
                (new Attribute('int_minmax'))
                    ->setPhpType('int')->setDbType(YiiDbSchema::TYPE_INTEGER)
                    ->setLimits(5, 25, null)->setRequired(),
                $schema->getProperty('int_minmax'),
                '$faker->numberBetween(5, 25)',
            ],
            // [
            //     (new Attribute('uuid'))->setPhpType('string')->setDbType('uuid'),
            //     $schema->getProperty('uuid'),
            //     '$faker->uuid',
            // ],
            [
                (new Attribute('str_text'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_TEXT)->setRequired(),
                $schema->getProperty('str_text'),
                '$faker->sentence',
            ],
            [
                (new Attribute('str_varchar'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_STRING)->setRequired(),
                $schema->getProperty('str_varchar'),
                '$faker->sentence',
            ],
            [
                (new Attribute('str_varchar'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_STRING)
                    ->setSize(100)->setRequired(),
                $schema->getProperty('str_varchar'),
                'substr($faker->text(100), 0, 100)',
            ],
            [
                (new Attribute('str_date'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATE)->setRequired(),
                $schema->getProperty('str_date'),
                '$faker->dateTimeThisCentury->format(\'Y-m-d\')',
            ],
            [
                (new Attribute('str_datetime'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATETIME)->setRequired(),
                $schema->getProperty('str_datetime'),
                '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(\'Y-m-d H:i:s\')',
            ],

            // optional() wrapping — the 4 combinations of required×example for the date type
            // (date is the most visible case: ->format() chain exercises the nullsafe ?-> replacement)

            // not required + no example → null fallback
            [
                (new Attribute('str_date'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATE),
                $schema->getProperty('str_date'),
                '$faker->optional(0.92)->dateTimeThisCentury?->format(\'Y-m-d\') ?? null',
            ],
            // not required + has example → example as fallback
            [
                (new Attribute('str_date_ex'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATE),
                $schema->getProperty('str_date_ex'),
                '$faker->optional(0.92)->dateTimeThisCentury?->format(\'Y-m-d\') ?? \'2020-03-14\'',
            ],
            // required + no example → no wrapping (covered by str_date above, repeated for clarity)
            // required + has example → example as fallback (required does NOT block wrapping when example present)
            [
                (new Attribute('str_date_ex'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATE)->setRequired(),
                $schema->getProperty('str_date_ex'),
                '$faker->optional(0.92)->dateTimeThisCentury?->format(\'Y-m-d\') ?? \'2020-03-14\'',
            ],
            // nullable=false + no example → no wrapping (treated like required)
            [
                (new Attribute('str_date'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATE)->setNullable(false),
                $schema->getProperty('str_date'),
                '$faker->dateTimeThisCentury->format(\'Y-m-d\')',
            ],
            // datetime: verify nullsafe chain with ->format() and example fallback
            [
                (new Attribute('str_datetime_ex'))->setPhpType('string')->setDbType(YiiDbSchema::TYPE_DATETIME),
                $schema->getProperty('str_datetime_ex'),
                '$faker->optional(0.92)->dateTimeThisYear(\'now\', \'UTC\')?->format(\'Y-m-d H:i:s\') ?? \'2020-03-14 21:42:17\'',
            ],
        ];
    }
}
