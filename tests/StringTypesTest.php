<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class StringTypesTest extends TestCase
{
    public function testPlainStringIsUntouched() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string"
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

        self::assertEquals($expected, $result);
    }

    public function testDateRetained() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "format": "date"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "string",
                "format": "date"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testDateConvertedToDateTime() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "format": "date"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "string",
                "format": "date-time"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'dateToDateTime' => true
        ]);

        self::assertEquals($expected, $result);
    }

    public function testHandlesByteFormat() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "format": "byte"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "string",
                "format": "byte",
                "pattern": "^[\\w\\d+\\/=]*$"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testRetainsCustomFormats() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "format": "custom_email"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "string",
                "format": "custom_email"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testRetainsPasswordFormat() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "format": "password"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "string",
                "format": "password"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testRetainsBinaryFormat() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "format": "binary"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "string",
                "format": "binary"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }
}
