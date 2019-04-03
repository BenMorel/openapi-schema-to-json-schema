<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class UnsupportedPropertiesTest extends TestCase
{
    public function testRemoveDiscriminatorByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "oneOf": [
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "string"
                            }
                        }
                    },
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "string"
                            }
                        }
                    }
                ],
                "discriminator": {
                    "propertyName": "foo"
                }
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
                                "type": "string"
                            }
                        }
                    },
                    {
                        "type": "object",
                        "required": ["foo"],
                        "properties": {
                            "foo": {
                                "type": "string"
                            }
                        }
                    }
                ]
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testRemoveReadOnlyByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "readOnly": {
                        "type": "string",
                        "readOnly": true
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
                    "readOnly": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testRemoveWriteOnlyByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "test": {
                        "type": "string",
                        "writeOnly": true
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
                    "test": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testRemoveXmlByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "string",
                        "xml": {
                            "attribute": true
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
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testRemoveExternalDocsByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "foo": {
                        "type": "string"
                    }
                },
                "externalDocs": {
                    "url": "http://foo.bar"
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
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        self::assertEquals($expected, $result);
    }

    public function testRemoveExampleByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "example": "foo"
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

    public function testRemoveDeprecatedByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "string",
                "deprecated": true
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

    public function testRetainingFields() : void
    {
        $schema = json_decode(<<<'JSON'
            {
                "type": "object",
                "properties": {
                    "readOnly": {
                        "type": "string",
                        "readOnly": true,
                        "example": "foo"
                    },
                    "anotherProp": {
                        "type": "object",
                        "properties": {
                            "writeOnly": {
                                "type": "string",
                                "writeOnly": true
                            }
                        }
                    }
                },
                "discriminator": "bar"
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "properties": {
                    "readOnly": {
                        "type": "string",
                        "readOnly": true
                    },
                    "anotherProp": {
                        "type": "object",
                        "properties": {
                            "writeOnly": {
                                "type": "string"
                            }
                        }
                    }
                },
                "discriminator": "bar"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'keepNotSupported' => ['readOnly', 'discriminator']
        ]);

        self::assertEquals($expected, $result);
    }
}
