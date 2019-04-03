<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class ItemsTest extends TestCase
{
    public function testItems() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "array",
                "items": {
                    "type": "string",
                    "example": "2017-01-01T12:34:56Z"
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "array",
                "items": {
                    "type": "string"
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        self::assertEquals($expected, $result);
    }
}
