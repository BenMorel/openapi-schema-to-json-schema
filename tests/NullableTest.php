<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class NullableTest extends TestCase
{
    public function testHandlesNullableTrueWithoutEnum() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "nullable": true
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": ["string", "null"]
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testHandlesNullableFalseWithoutEnum() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "nullable": false
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "string"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testHandlesNullableTrueWithEnum() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "enum": ["a", "b"],
                "nullable": true
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": ["string", "null"],
                "enum": ["a", "b", null]
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testHandlesNullableFalseWithEnum() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "enum": ["a", "b"],
                "nullable": false
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "string",
                "enum": ["a", "b"]
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }
}
