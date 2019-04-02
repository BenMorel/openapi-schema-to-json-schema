<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use BenMorel\OpenApiSchemaToJsonSchema\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;

class InvalidTypesTest extends TestCase
{
    public function testInvalidTypeDateTime() : void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Type "dateTime" is not a valid type.');

        $schema = (object) [
            'type' => 'dateTime'
        ];

        Convert::openapiSchemaToJsonSchema($schema);
    }

    public function testInvalidTypeFoo() : void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Type "foo" is not a valid type.');

        $schema = (object) [
            'type' => 'foo'
        ];

        Convert::openapiSchemaToJsonSchema($schema);
    }

    public function testInvalidTypeNotAsString() : void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Type ["string",null] is not a valid type.');

        $schema = (object) [
            'type' => ['string', null]
        ];

        Convert::openapiSchemaToJsonSchema($schema);
    }

    public function testInvalidTypeInsideComplexSchema() : void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Type "invalidtype" is not a valid type.');

        $schema = json_decode(file_get_contents(__DIR__ . '/schemas/schema-2-invalid-type.json'));

        Convert::openapiSchemaToJsonSchema($schema);
    }
}
