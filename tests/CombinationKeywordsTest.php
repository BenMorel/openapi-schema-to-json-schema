<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class CombinationKeywordsTest extends TestCase
{
    public function testIteratesAllOfs() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "allOf": [
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "integer"
                            }
                        }
                    },
                    {
                        "allOf": [
                            {
                                "type": "number"
                            }
                        ]
                    }
                ]
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "allOf": [
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "integer"
                            }
                        }
                    },
                    {
                        "allOf": [
                            {
                                "type": "number"
                            }
                        ]
                    }
                ]
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        self::assertEquals($expected, $result);
    }

    public function testIteratesAnyOfs() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "anyOf": [
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "integer"
                            }
                        }
                    },
                    {
                        "anyOf": [
                            {
                                "type": "object",
                                "properties": {
                                    "bar": {
                                        "type": "number"
                                    }
                                }
                            }
                        ]
                    }
                ]
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "anyOf": [
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "integer"
                            }
                        }
                    },
                    {
                        "anyOf": [
                            {
                                "type": "object",
                                "properties": {
                                    "bar": {
                                        "type": "number"
                                    }
                                }
                            }
                        ]
                    }
                ]
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        self::assertEquals($expected, $result);
    }

    public function testIteratesOneOfs() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "oneOf": [
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "integer"
                            }
                        }
                    },
                    {
                        "oneOf": [
                            {
                                "type": "object",
                                "properties": {
                                    "bar": {
                                        "type": "number"
                                    }
                                }
                            }
                        ]
                    }
                ]
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "oneOf": [
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "integer"
                            }
                        }
                    },
                    {
                        "oneOf": [
                            {
                                "type": "object",
                                "properties": {
                                    "bar": {
                                        "type": "number"
                                    }
                                }
                            }
                        ]
                    }
                ]
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        self::assertEquals($expected, $result);
    }

    public function testConvertsTypesInNot() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "not": {
                        "type": "string",
                        "minLength": 8
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
                    "not": {
                        "type": "string",
                        "minLength": 8
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        self::assertEquals($expected, $result);
    }

    public function testConvertsTypesInNot2() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "not": {
                    "type": "string",
                    "minLength": 8
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "not": {
                    "type": "string",
                    "minLength": 8
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        self::assertEquals($expected, $result);
    }

    public function testNestedCombinationKeywords() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "anyOf": [
                    {
                        "allOf": [
                            {
                                "type": "object",
                                "properties": {
                                    "foo": {
                                        "type": "string",
                                        "nullable": true
                                    }
                                }
                            },
                            {
                                "type": "object",
                                "properties": {
                                    "bar": {
                                        "type": "integer",
                                        "nullable": true
                                    }
                                }
                            }
                        ]
                    },
                    {
                        "type": "object",
                        "properties": {
                            "foo": {
                                "type": "string"
                            }
                        }
                    },
                    {
                        "not": {
                            "type": "string",
                            "example": "foobar"
                        }
                    }
                ]
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "anyOf": [
                    {
                        "allOf": [
                            {
                                "type": "object",
                                "properties": {
                                    "foo": {
                                        "type": ["string", "null"]
                                    }
                                }
                            },
                            {
                                "type": "object",
                                "properties": {
                                    "bar": {
                                        "type": ["integer", "null"]
                                    }
                                }
                            }
                        ]
                    },
                    {
                        "type": "object",
                        "properties": {
                            "foo": {
                                "type": "string"
                            }
                        }
                    },
                    {
                        "not": {
                            "type": "string"
                        }
                    }
                ]
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);
        self::assertEquals($expected, $result);
    }
}
