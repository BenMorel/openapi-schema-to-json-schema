<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class NullableOneAnyAllOfTest extends TestCase
{
    /**
     * A nullable one|any|allOf with a single entry.
     */
    private const SINGLE = <<<'JSON'
            {
                "nullable": true,
                "xxxOf": [
                    {
                        "$ref": "#/components/schemas/User"
                    }
                ]
            }
JSON;

    private const SINGLE_EXPECTED = <<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "oneOf": [
                    {
                        "type": "null"
                    },
                    {
                        "xxxOf": [
                            {
                                "$ref": "#/components/schemas/User"
                            }
                        ]
                    }
                ]
            }
JSON;

    /**
     * A nullable one|any|allOf with multiple entries.
     */
    private const MULTIPLE = <<<'JSON'
            {
                "nullable": true,
                "xxxOf": [
                    {
                        "$ref": "#/components/schemas/User"
                    },
                    {
                        "type": "object",
                        "properties": {
                            "id": {
                                "type": "integer"
                            }
                        },
                        "required": ["id"]
                    }
                ]
            }
JSON;

    private const MULTIPLE_EXPECTED = <<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "oneOf": [
                    {
                        "type": "null"
                    },
                    {
                        "xxxOf": [
                            {
                                "$ref": "#/components/schemas/User"
                            },
                            {
                                "type": "object",
                                "properties": {
                                    "id": {
                                        "type": "integer"
                                    }
                                },
                                "required": ["id"]
                            }
                        ]
                    }
                ]
            }
JSON;

    /**
     * A nullable one|any|allOf with an object that has a nullable property itself.
     */
    private const RECURSIVE = <<<'JSON'
            {
                "nullable": true,
                "xxxOf": [
                    {
                        "type": "object",
                        "properties": {
                            "id": {
                                "type": "integer",
                                "nullable": true
                            }
                        }
                    }
                ]
            }
JSON;

    private const RECURSIVE_EXPECTED = <<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "oneOf": [
                    {
                        "type": "null"
                    },
                    {
                        "xxxOf": [
                            {
                                "type": "object",
                                "properties": {
                                    "id": {
                                        "type": ["integer", "null"]
                                    }
                                }
                            }
                        ]
                    }
                ]
            }
JSON;

    /**
     * @dataProvider providerHandlesNullableOneAnyAllOf
     *
     * @param string $schema   The OpenAPI schema.
     * @param string $expected The expected JSON schema.
     */
    public function testHandlesNullableOneAnyAllOf(string $schema, string $expected) : void
    {
        $schema = json_decode($schema);
        $expected = json_decode($expected);

        $result = Convert::openapiSchemaToJsonSchema($schema);
        self::assertEquals($expected, $result);
    }

    public function providerHandlesNullableOneAnyAllOf() : iterable
    {
        foreach (['oneOf', 'anyOf', 'allOf'] as $xOf) {
            yield [
                str_replace('xxxOf', $xOf, self::SINGLE),
                str_replace('xxxOf', $xOf, self::SINGLE_EXPECTED)
            ];

            yield [
                str_replace('xxxOf', $xOf, self::MULTIPLE),
                str_replace('xxxOf', $xOf, self::MULTIPLE_EXPECTED)
            ];

            yield [
                str_replace('xxxOf', $xOf, self::RECURSIVE),
                str_replace('xxxOf', $xOf, self::RECURSIVE_EXPECTED)
            ];
        }
    }
}
