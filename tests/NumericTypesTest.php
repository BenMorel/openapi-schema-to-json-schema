<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class NumericTypesTest extends TestCase
{
    public function testHandlesInt32Format() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "integer",
                "format": "int32"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "integer",
                "format": "int32"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testHandlesInt64Format() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "integer",
                "format": "int64"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "integer",
                "format": "int64"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testHandlesFloatFormat() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "number",
                "format": "float"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "number",
                "format": "float"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testHandlesDoubleFormat() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "number",
                "format": "double"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "number",
                "format": "double"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }
}
