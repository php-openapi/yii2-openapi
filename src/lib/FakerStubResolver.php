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

        // FK: determined by a $ref / allOf[$ref] — not by column name convention
        if (!empty($this->attribute->reference)) {
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
            $property = $this->property->getProperty();
            if ($property->type === 'object') {
                // A JSONB/JSON column declared as type:object in the spec has phpType=array but must
                // be faked as an object, not as an array.
                return $this->fakeForObject($property);
            }
            $result = $this->fakeForArray($property);
            if ($result !== '$faker->words()') { # example for array will only work with a list/`$faker->words()`
                return $result;
            }
        } elseif ($this->attribute->phpType === 'object') {
            $result = $this->fakeForObject($this->property->getProperty());
        } else {
            return null;
        }

        // No optional wrapping for required/non-nullable fields without an example (always generate a real value),
        // or for unique-items fields (optional fallback would break uniqueness).
        if (
            (($this->attribute->isRequired() || $this->attribute->nullable === false) && !$this->property->hasAttr('example')) ||
            $this->property->getAttr('uniqueItems')
        ) {
            if ($this->attribute->phpType === 'string' && $this->attribute->size) {
                return 'substr(' . $result . ', 0, '.$this->attribute->size.')';
            }
            return $result;
        }

        $example = $this->property->getAttr('example');
        $example = VarExporter::export($example);
        $example = preg_replace('/\n/', "\n        ", $example);

        /**
         * $example must be the exact value that goes into the DB column, e.g. '2020-03-14 21:42:17'
         * for a datetime column — not a DateTime object or ISO string with timezone offset.
         * optional() without a default returns null on miss; all -> are made nullsafe so the whole
         * chain collapses to null, then ?? $example inserts the ready-to-store fallback value.
         *
         * Negative lookbehind prevents turning an existing ?-> into ??->
         */
        $wrapped = str_replace('$faker?->', '$faker->optional(0.92)->', preg_replace('/(?<!\?)->/', '?->', $result));

        if ($this->attribute->phpType === 'string' && $this->attribute->size) {
            return 'is_string($s = ' . $wrapped . ') ? substr($s, 0, '.$this->attribute->size.') : ' . $example;
        }

        return $wrapped  . ' ?? ' . $example;
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
            return '$uniqueFaker->sha256';
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
        foreach ($patterns as $pattern => $fake) {
            if (preg_match($pattern, $this->attribute->columnName)) {
                return $fake;
            }
        }

        $size = $this->attribute->size > 0 ? $this->attribute->size : null;
        if ($size) {
            $method = 'text';
            if ($size < 5) {
                $method = 'word';
            }
            return '$faker->' . $method . '(' . $size . ')';
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

        /** @var Schema|Reference|null $items */
        $items = $property->items;

        if (!$items) {
            // Required fields cannot use [] — Yii2's isEmpty() treats empty arrays as blank.
            return $this->attribute->required ? $this->arbitraryArray() : '[]';
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
        if (in_array($type, ['string', 'number', 'integer', 'boolean', 'array'])) {
            $aFaker = $this->aElementFaker($this->property->getProperty(), $this->attribute->columnName);
            return $this->wrapInArray($aFaker, $uniqueItems, $count);
        }

        if ($type === 'object') {
            $result = $this->fakeForObject($items, 1);
            if ($result === '(object) []') {
                return '[]';
            }
            return $this->wrapInArray($result, $uniqueItems, $count);
        }

        return '[]';
    }

    /**
     * Generates a PHP array literal string for an OpenAPI object property.
     * The output is embedded as PHP code in Faker fixture files, not as JSON.
     *
     * Flow: Faker assigns a PHP array to the model property → ActiveRecord passes it
     * to the DB driver → the driver JSON-encodes it before storing.
     * Result in DB:
     *   PHP []              → json_encode([])             → []           (JSON array)
     *   PHP ['key' => 'v']  → json_encode(['key' => 'v']) → {"key": "v"} (JSON object)
     *
     * A non-empty associative array correctly becomes a JSON object in the DB.
     * An empty PHP array always becomes [] in the DB, never {} — regardless of whether
     * the OpenAPI field is typed as "object" or "array". For test/faker data without
     * defined properties this is acceptable, as no schema is enforced.
     * @internal
     */
    public function fakeForObject(SpecObjectInterface $items, int $depth = 0): string
    {
        if (!$items->properties) {
            return '(object) []';
        }

        $indent = str_repeat('    ', $depth + 3);
        $closingIndent = str_repeat('    ', $depth + 2);
        $parts = [];

        foreach ($items->properties as $name => $prop) {
            /** @var SpecObjectInterface $prop */

            if (!$prop instanceof Reference && ($prop->type === 'object' || !empty($prop->properties))) {
                $key = $name;
                $result = $this->fakeForObject($prop, $depth + 1);
            } else {
                ['columnName' => $key, 'fakerStub' => $result] = $this->resolveElement(['items' => $prop->getSerializableData()], $name);
                if (str_starts_with($result, 'array_map')) {
                    $result = $this->reindentArrayMapForObject($result, $depth);
                }
            }
            $parts[] = $indent . '\'' . $key . '\' => ' . $result . ',';
        }

        $props = '[' . PHP_EOL . implode(PHP_EOL, $parts) . PHP_EOL . $closingIndent . ']';

        return $props;
    }

    /**
     * Re-indents a compact wrapInArray() output string to match the correct depth inside fakeForObject().
     * wrapInArray() always uses hardcoded 12/8-space indentation; when its result is embedded as a
     * property value inside a fakeForObject() output, the indentation must be adjusted.
     *
     * For a simple body (single return statement): shift = bodyIndent - 12, placing the return at bodyIndent.
     * For a nested body (return array_map(...)): shift = bodyIndent - 8, so the inner return lands at
     * bodyIndent+4 and the inner closing brace lands at bodyIndent — matching wrapInArray's 12/8 ratio.
     */
    private function reindentArrayMapForObject(string $code, int $depth): string
    {
        $bodyIndent  = str_repeat('    ', $depth + 4);
        $closeIndent = str_repeat('    ', $depth + 3);

        $pat = '/^array_map\(function \(\) use \(\$faker, \$uniqueFaker\) \{\n            (.*)\n        \}, range\(1, (\d+)\)\)$/s';
        if (!preg_match($pat, $code, $m)) {
            return $code;
        }
        [$body, $count] = [$m[1], $m[2]];

        // wrapInArray shifts nested array_map bodies by 4 spaces, so all bodies now start at 12 spaces.
        $shift = strlen($bodyIndent) - 12;
        if ($shift > 0) {
            $body = preg_replace('/\n/', "\n" . str_repeat(' ', $shift), $body);
        }

        return "array_map(function () use (\$faker, \$uniqueFaker) {\n"
            . $bodyIndent  . $body . "\n"
            . $closeIndent . "}, range(1, {$count}))";
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
        $indent = str_repeat(' ', 12);
        foreach ($items->oneOf as $key => $aDataType) {
            /** @var Schema|Reference $aDataType */

            $inp = $aDataType instanceof Reference ? $aDataType : ['items' => $aDataType->getSerializableData()];
            $aFaker = $this->aElementFaker($inp, $this->attribute->columnName);
            // Shift all continuation lines by 4 spaces so that multi-line values
            // (array_map or object literals from fakeForObject) are indented one level
            // deeper than the $dataTypeN assignment (12 → 16 for body, 8 → 12 for closing).
            if (str_contains($aFaker, PHP_EOL)) {
                $aFaker = str_replace(PHP_EOL, PHP_EOL . '    ', $aFaker);
            }
            if ($result !== '') {
                $result .= PHP_EOL . $indent;
            }
            $result .= '$dataType' . $key . ' = ' . $aFaker . ';';
        }
        $ct = count($items->oneOf) - 1;
        $result .= PHP_EOL . $indent . 'return ${"dataType".rand(0, ' . $ct . ')}';
        return $result;
    }

    public function wrapInArray(string $aFaker, bool $uniqueItems, int $count, bool $oneOf = false): string
    {
        $ret = $oneOf ? '' : 'return ';
        $inner = $uniqueItems ? str_replace('$faker->', '$uniqueFaker->', $aFaker) : $aFaker;
        // Only shift when the inner value is itself a nested array_map;
        // handleOneOf and fakeForObject results already carry correct indentation
        if (str_starts_with($inner, 'array_map(') && str_contains($inner, PHP_EOL)) {
            $inner = str_replace(PHP_EOL, PHP_EOL . '    ', $inner);
        }
        return 'array_map(function () use ($faker, $uniqueFaker) {
            ' . $ret . $inner . ';
        }, range(1, ' . $count . '))';
    }

    public function arbitraryArray(): string
    {
        $theFaker = $this->property->getAttr('uniqueItems') ? '$uniqueFaker' : '$faker';
        return $theFaker . '->words()';
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
        return $this->resolveElement($data, $columnName)['fakerStub'];
    }

    /**
     * Resolves the faker stub and the effective column key for a single element.
     * For FK properties (direct $ref or allOf[$ref]), the key uses the same '_id' suffix
     * logic as Attribute::asReference() — both for real DB columns and JSONB sub-properties.
     * @return array
     * @example ['columnName' => 'payment_method_id', 'fakerStub' => '$faker->randomElement(...)']
     */
    private function resolveElement($data, ?string $columnName = null): array
    {
        if ($data instanceof Reference) {
            $class = str_replace('#/components/schemas/', '', $data->getReference()) . 'Faker';
            return ['columnName' => $columnName ?? 'unknownColumn', 'fakerStub' => '(new ' . $class . ')->generateModel()->attributes'];
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
            $compoSchemaData['properties'][$columnName][CustomSpecAttr::NO_RELATION] = true;
        }

        $schema = new Schema($compoSchemaData);
        $compo = 'UnnamedCompo';
        $cs = new ComponentSchema($schema, $compo);
        if ($this->config) {
            $rc = new ReferenceContext($this->config->getOpenApi(), Yii::getAlias($this->config->openApiPath));
            $schema->setReferenceContext($rc);
        }
        $dbModels = (new AttributeResolver($compo, $cs, new JunctionSchemas([]), $this->config))->resolve();
        $attr = $dbModels->attributes[$columnName];

        return [
            'columnName' => $attr->columnName,
            'fakerStub' => (new static($attr, $cs->getProperty($columnName), $this->config))->resolve(),
        ];
    }
}
