<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

/** @noinspection InterfacesAsConstructorDependenciesInspection */
/** @noinspection PhpUndefinedFieldInspection */

namespace cebe\yii2openapi\lib;

use cebe\openapi\exceptions\IOException;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use cebe\yii2openapi\lib\exceptions\InvalidDefinitionException;
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\JunctionSchemas;
use cebe\yii2openapi\lib\openapi\ComponentSchema;
use cebe\yii2openapi\lib\openapi\PropertySchema;
use stdClass;
use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use function str_replace;
use const PHP_EOL;

/**
 * Guess faker for attribute
 * @link https://github.com/fzaninotto/Faker#formatters
 **/
class FakerStubResolver
{
    public const MAX_INT = 1000000;

    private Attribute $attribute;

    private PropertySchema $property;

    private ?Config $config;

    public function __construct(Attribute $attribute, PropertySchema $property, ?Config $config = null)
    {
        $this->attribute = $attribute;
        $this->property = $property;
        $this->config = $config;
    }

    /**
     * @throws InvalidConfigException
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     * @throws InvalidDefinitionException
     * @throws ExceptionInterface
     * @throws IOException
     */
    public function resolve(): ?string
    {
        if ($this->property->xFaker === false) {
            $this->attribute->setFakerStub(null);
            return null;
        }
        if ($this->property->hasAttr(CustomSpecAttr::FAKER)) {
            $fakerVal = $this->property->getAttr(CustomSpecAttr::FAKER);
            if ($fakerVal === false) {
                $this->attribute->setFakerStub(null);
                return null;
            }
            return $fakerVal;
        }

        if ($this->attribute->isReadOnly() && $this->attribute->isVirtual()) {
            return null;
        }

        // column name ends with `_id`/FK
        if (substr($this->attribute->columnName, -3) === '_id' || !empty($this->attribute->fkColName)) {
            $config = $this->config;
            if (!$config) {
                $config = new Config;
            }
            $mn = $config->modelNamespace;
            return '$faker->randomElement(\\' . $mn
                . ($mn ? '\\' : '')
                . ucfirst((string)$this->attribute->reference) . '::find()->select("id")->column())'; // TODO PK "id" can be also something else
        }

        $limits = $this->attribute->limits;

        if ($this->attribute->phpType === 'bool') {
            $result = '$faker->boolean';
        } elseif (in_array($this->attribute->phpType, ['int', 'integer'])) {
            $result = $this->fakeForInt($limits['min'], $limits['max']);
        } elseif ($this->attribute->phpType === 'string') {
            $result = $this->fakeForString();
        } elseif (in_array($this->attribute->phpType, ['float', 'double'])) {
            $result = $this->fakeForFloat($limits['min'], $limits['max']);
        } elseif ($this->attribute->phpType === 'array' ||
            substr($this->attribute->phpType, -2) === '[]') {
            $result = $this->fakeForArray($this->property->getProperty());
        } elseif ($this->attribute->phpType === 'object') {
            $result = $this->fakeForObject($this->property->getProperty());
        } else {
            return null;
        }

        if (!$this->property->hasAttr('example')) {
            return $result;
        }
        if (stripos($result, 'uniqueFaker') !== false) {
            return $result;
        }
        $example = $this->property->getAttr('example');
        $example = VarExporter::export($example);
        return str_replace('$faker->', '$faker->optional(0.92, ' . $example . ')->', $result);
    }

    private function fakeForString(): ?string
    {
        $formats = [
            'date' => '$faker->dateTimeThisCentury->format(\'Y-m-d\')',
            'date-time' => '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(\'Y-m-d H:i:s\')', // DATE_ATOM=>ISO-8601
            'email' => '$faker->safeEmail',

            // for x-db-type
            'datetime' => '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(\'Y-m-d H:i:s\')', // DATE_ATOM=>ISO-8601
            'timestamp' => '$faker->dateTimeThisYear(\'now\', \'UTC\')->format(\'Y-m-d H:i:s\')', // DATE_ATOM=>ISO-8601
            'time' => '$faker->time(\'H:i:s\')',
            'year' => '$faker->year',
        ];
        $format = $this->property->getAttr('format');
        $format = $format === null ? $this->property->getAttr('x-db-type') : $format;
        if ($format && isset($formats[$format])) {
            return $formats[$format];
        }
        $enum = $this->property->getAttr('enum');
        if (!empty($enum) && is_array($enum)) {
            $items = str_replace([PHP_EOL, '  ', ',]'], ['', '', ']'], VarDumper::export($enum));
            return '$faker->randomElement(' . $items . ')';
        }
        if ($this->attribute->columnName === 'title'
            && $this->attribute->size
            && (int)$this->attribute->size < 10) {
            return '$faker->title';
        }
        if ($this->attribute->primary || $this->attribute->isReference()) {
            $size = $this->attribute->size ?? 255;
            return 'substr($uniqueFaker->sha256, 0, ' . $size . ')';
        }

        $patterns = [
            '~_id$~' => '$uniqueFaker->numberBetween(0, 1000000)',
            '~uuid$~' => '$uniqueFaker->uuid',
            '~slug$~' => '$uniqueFaker->slug',
            '~firstname~i' => '$faker->firstName',
            '~password~i' => '$faker->password',
            '~(last|sur)name~i' => '$faker->lastName',
            '~(company|employer)~i' => '$faker->company',
            '~(city|town)~i' => '$faker->city',
            '~(post|zip)code~i' => '$faker->postcode',
            '~streetaddress~i' => '$faker->streetAddress',
            '~address~i' => '$faker->address',
            '~street~i' => '$faker->streetName',
            '~state~i' => '$faker->state',
            '~county~i' => 'sprintf("%s County", $faker->city)',
            '~country~i' => '$faker->countryCode',
            '~lang~i' => '$faker->languageCode',
            '~locale~i' => '$faker->locale',
            '~currency~i' => '$faker->currencyCode',
            '~(hash|token)~i' => '$faker->sha256',
            '~e?mail~i' => '$faker->safeEmail',
            '~timestamp~i' => '$faker->unixTime',
            '~.*At$~' => '$faker->dateTimeThisCentury->format(\'Y-m-d H:i:s\')', // createdAt, updatedAt, ...
            '~.*ed_at$~i' => '$faker->dateTimeThisCentury->format(\'Y-m-d H:i:s\')', // created_at, updated_at, ...
            '~(phone|fax|mobile|telnumber)~i' => '$faker->e164PhoneNumber',
            '~(^lat|coord)~i' => '$faker->latitude',
            '~^lon~i' => '$faker->longitude',
            '~title~i' => '$faker->sentence',
            '~(body|summary|article|content|descr|comment|detail)~i' => '$faker->paragraphs(6, true)',
            '~(url|site|website|href)~i' => '$faker->url',
            '~(username|login)~i' => '$faker->userName',
        ];
        $size = $this->attribute->size > 0 ? $this->attribute->size : null;
        foreach ($patterns as $pattern => $fake) {
            if (preg_match($pattern, $this->attribute->columnName)) {
                if ($size) {
                    return 'substr(' . $fake . ', 0, ' . $size . ')';
                }
                return $fake;
            }
        }

        if ($size) {
            $method = 'text';
            if ($size < 5) {
                $method = 'word';
            }
            return 'substr($faker->' . $method . '(' . $size . '), 0, ' . $size . ')';
        }
        return '$faker->sentence';
    }

    private function fakeForInt(?int $min, ?int $max): ?string
    {
        $fakerVariable = 'faker';
        if (preg_match('~_?id$~', $this->attribute->columnName)) {
            $fakerVariable = 'uniqueFaker';
        }
        if ($min !== null && $max !== null) {
            return "\${$fakerVariable}->numberBetween($min, $max)";
        }

        if ($min !== null) {
            return "\${$fakerVariable}->numberBetween($min, " . self::MAX_INT . ")";
        }

        if ($max !== null) {
            return "\${$fakerVariable}->numberBetween(0, $max)";
        }

        $patterns = [
            '~timestamp~i' => '$faker->unixTime',
            '~.*At$~' => '$faker->unixTime', // createdAt, updatedAt, ...
            '~.*_date$~' => '$faker->unixTime', // creation_date, ...
            '~.*ed_at$~i' => '$faker->unixTime', // created_at, updated_at, ...
        ];
        foreach ($patterns as $pattern => $fake) {
            if (preg_match($pattern, $this->attribute->columnName)) {
                return $fake;
            }
        }
        return "\${$fakerVariable}->numberBetween(0, " . self::MAX_INT . ")";
    }

    private function fakeForFloat(?int $min, ?int $max): ?string
    {
        if ($min !== null && $max !== null) {
            return "\$faker->randomFloat(null, $min, $max)";
        }
        if ($min !== null) {
            return "\$faker->randomFloat(null, $min)";
        }
        if ($max !== null) {
            return "\$faker->randomFloat(null, 0, $max)";
        }
        return '$faker->randomFloat()';
    }

    /**
     * @param int $count let's set a number to default number of elements
     * @throws InvalidConfigException
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     * @throws InvalidDefinitionException|ExceptionInterface
     * @throws IOException
     */
    private function fakeForArray(SpecObjectInterface $property, int $count = 4): string
    {
        $uniqueItems = false;
        if ($property->minItems) {
            $count = $property->minItems;
        }
        if ($property->maxItems) {
            $maxItems = $property->maxItems;
            if ($maxItems < $count) {
                $count = $maxItems;
            }
        }
        if (!empty($property->uniqueItems)) {
            $uniqueItems = $property->uniqueItems;
        }

        // TODO consider example of OpenAPI spec

        /** @var Schema|Reference|null $items */
        $items = $property->items;

        if (!$items) {
            return $this->arbitraryArray();
        }

        if ($items instanceof Reference) {
            $aFakerForRef = $this->aElementFaker($items, $this->attribute->columnName);
            return $this->wrapInArray($aFakerForRef, $uniqueItems, $count);
        }
        if (!empty($items->oneOf)) {
            return $this->wrapInArray($this->handleOneOf($items, $count), $uniqueItems, $count, true);
        }

        $type = $items->type;
        if ($type === null) {
            return $this->arbitraryArray();
        }
        $aFaker = $this->aElementFaker($this->property->getProperty(), $this->attribute->columnName);
        if (in_array($type, ['string', 'number', 'integer', 'boolean', 'array'])) {
            return $this->wrapInArray($aFaker, $uniqueItems, $count);
        }

        if ($type === 'object') {
            $result = $this->fakeForObject($items);
            return $this->wrapInArray($result, $uniqueItems, $count);
        }

        // TODO more complex type array/object; also consider $ref; may be recursively; may use `oneOf`

//        return '$faker->words()'; // TODO implement faker for array; also consider min, max, unique

//        if ($this->attribute->required) {
//            return '["a" => "b"]'; // TODO this is incorrect, array schema should be checked first
//        }
        return '[]';
    }

    /**
     * @internal
     */
    public function fakeForObject(SpecObjectInterface $items): string
    {
        if (!$items->properties) {
            return $this->arbitraryArray();
        }

        $props = '[' . PHP_EOL;

        foreach ($items->properties as $name => $prop) {
            /** @var SpecObjectInterface $prop */

            if (!empty($prop->properties)) { // nested object
                $result = $this->{__FUNCTION__}($prop);
            } else {
                $result = $this->aElementFaker(['items' => $prop->getSerializableData()], $name);
            }
            $props .= '\'' . $name . '\' => ' . $result . ',' . PHP_EOL;
        }

        $props .= ']';

        return $props;
    }

    /**
     * This method must be only used incase of array
     * @param SpecObjectInterface $items
     * @param int $count
     * @return string
     * @throws ExceptionInterface
     * @throws IOException
     * @throws InvalidConfigException
     * @throws InvalidDefinitionException
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     * @internal
     */
    public function handleOneOf(SpecObjectInterface $items, int $count): string
    {
        $result = '';
        foreach ($items->oneOf as $key => $aDataType) {
            /** @var Schema|Reference $aDataType */

            $inp = $aDataType instanceof Reference ? $aDataType : ['items' => $aDataType->getSerializableData()];
            $aFaker = $this->aElementFaker($inp, $this->attribute->columnName);
            $result .= '$dataType' . $key . ' = ' . $aFaker . ';';
        }
        $ct = count($items->oneOf) - 1;
        $result .= 'return ${"dataType".rand(0, ' . $ct . ')}';
        return $result;
    }

    public function wrapInArray(string $aFaker, bool $uniqueItems, int $count, bool $oneOf = false): string
    {
        $ret = $oneOf ? '' : 'return ';
        return 'array_map(function () use ($faker, $uniqueFaker) {
            ' . $ret . ($uniqueItems ? str_replace('$faker->', '$uniqueFaker->', $aFaker) : $aFaker) . ';
        }, range(1, ' . $count . '))';
    }

    public function arbitraryArray(): string
    {
        return '$faker->words()';
    }

    /**
     * This method is only for `fakeForArray()` or methods only used inside `fakeForArray()`. If needed to use outside `fakeForArray()` context then some changes might be required.
     * Also see OpenAPI extension `x-no-relation` in README.md
     * @param $data array|stdClass|SpecObjectInterface
     * @param string|null $columnName
     * @return string|null
     * @throws ExceptionInterface
     * @throws IOException
     * @throws InvalidConfigException
     * @throws InvalidDefinitionException
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     * @internal
     */
    public function aElementFaker($data, ?string $columnName = null): ?string
    {
        if ($data instanceof Reference) {
            $class = str_replace('#/components/schemas/', '', $data->getReference());
            $class .= 'Faker';
            return '(new ' . $class . ')->generateModel()->attributes';
        }

        $inp = $data instanceof SpecObjectInterface ? $data->getSerializableData() : $data;
        $aElementData = Json::decode(Json::encode($inp));
        $columnName = $columnName ?? 'unnamedProp';
        $compoSchemaData = [
            'properties' => [
                $columnName => $aElementData['items']
            ]
        ];

        // This condition is only for properties with type = array
        // If you intend to use this method from out of `fakeForArray()` context then below condition should be changed depending on your use case
        // Also see OpenAPI extension `x-no-relation` in README.md
        if (!empty($compoSchemaData['properties'][$columnName]['items']['$ref'])) {
            $compoSchemaData['properties'][$columnName]['x-no-relation'] = true;
        }

        $schema = new Schema($compoSchemaData);
        $compo = 'UnnamedCompo';
        $cs = new ComponentSchema($schema, $compo);
        if ($this->config) {
            $rc = new ReferenceContext($this->config->getOpenApi(), Yii::getAlias($this->config->openApiPath));
            $schema->setReferenceContext($rc);
        }
        $dbModels = (new AttributeResolver($compo, $cs, new JunctionSchemas([]), $this->config))->resolve();

        return (new static($dbModels->attributes[$columnName], $cs->getProperty($columnName), $this->config))->resolve();
    }
}
