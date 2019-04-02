<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class ComplexSchemasTest extends TestCase
{
    public function testConvertSchema() : void
    {
        $schema = json_decode(file_get_contents(__DIR__ . '/schemas/schema-1.json'));
        $expected = json_decode(file_get_contents(__DIR__ . '/schemas/schema-1-expected.json'));

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testConvertingComplexSchemaInPlace() : void
    {
        $schema = json_decode(file_get_contents(__DIR__ . '/schemas/schema-1.json'));
        $expected = json_decode(file_get_contents(__DIR__ . '/schemas/schema-1-expected.json'));

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'cloneSchema' => false
        ]);

        $this->assertEquals($expected, $result);
        $this->assertSame($schema, $result);
    }
}
