<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use BenMorel\OpenApiSchemaToJsonSchema\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;

class InvalidTypesTest extends TestCase
{
    /**
     * @dataProvider providerInvalidTypes
     */
    public function testInvalidTypes($type) : void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Type ' . json_encode($type) . ' is not a valid type.');

        $schema = (object) [
            'type' => $type
        ];

        Convert::openapiSchemaToJsonSchema($schema);
    }

    public function providerInvalidTypes() : array
    {
        return [
            ['dateTime'],
            ['foo'],
            [['string', null]], // 'null' should be a string
        ];
    }

    public function testInvalidTypeInsideComplexSchema() : void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Type "invalidtype" is not a valid type.');

        $schema = json_decode(file_get_contents(__DIR__ . '/schemas/schema-2-invalid-type.json'));

        Convert::openapiSchemaToJsonSchema($schema);
    }

    /**
     * @dataProvider providerValidTypes
     */
    public function testValidTypes(string $type) : void
    {
        $schema = (object) [
            'type' => $type
        ];

        $expected = (object) [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'type' => $type
        ];

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function providerValidTypes() : array
    {
        return [
            ['integer'],
            ['number'],
            ['string'],
            ['boolean'],
            ['object'],
            ['array']
        ];
    }
}
