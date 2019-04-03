<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class PropertiesTest extends TestCase
{
    public function testProperties() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "required": ["bar"],
                "properties": {
                    "foo": {
                        "type": "string",
                        "example": "2017-01-01T12:34:56Z"
                    },
                    "bar": {
                        "type": "string",
                        "nullable": true
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "required": ["bar"],
                "properties": {
                    "foo": {
                        "type": "string"
                    },
                    "bar": {
                        "type": ["string", "null"]
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        $this->assertEquals($expected, $result);
    }

    public function testAdditionalPropertiesIsFalse() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "string",
                        "example": "2017-01-01T12:34:56Z"
                    }
                },
                "additionalProperties": false
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "string"
                    }
                },
                "additionalProperties": false
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        $this->assertEquals($expected, $result);
    }

    public function testAdditionalPropertiesIsTrue() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "string",
                        "example": "2017-01-01T12:34:56Z"
                    }
                },
                "additionalProperties": true
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "string"
                    }
                },
                "additionalProperties": true
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        $this->assertEquals($expected, $result);
    }

    public function testAdditionalPropertiesIsAnObject() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "string",
                        "example": "2017-01-01T12:34:56Z"
                    }
                },
                "additionalProperties": {
                    "type": "object",
                    "properties": {
                        "foo": {
                            "type": "string"
                        }
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "string"
                    }
                },
                "additionalProperties": {
                    "type": "object",
                    "properties": {
                        "foo": {
                            "type": "string"
                        }
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        $this->assertEquals($expected, $result);
    }
}
