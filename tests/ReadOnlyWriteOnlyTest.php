<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class ReadOnlyWriteOnlyTest extends TestCase
{
    /**
     * @dataProvider providerRemovingReadOnlyProp
     */
    public function testRemovingReadOnlyProp(array $options) : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "properties": {
                    "prop1": {
                        "type": "string",
                        "readOnly": true
                    },
                    "prop2": {
                        "type": "string"
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
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, $options);

        $this->assertEquals($expected, $result);
    }

    public function providerRemovingReadOnlyProp() : array
    {
        return [
            [[
                // removing readOnly prop
                'removeReadOnly' => true
            ]],
            [[
                // removing readOnly prop even if keeping
                'removeReadOnly' => true,
                'keepNotSupported' => ['readOnly']
            ]]
        ];
    }

    /**
     * @dataProvider providerRemovingWriteOnlyProp
     */
    public function testRemovingWriteOnlyProp(array $options) : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "properties": {
                    "prop1": {
                        "type": "string",
                        "writeOnly": true
                    },
                    "prop2": {
                        "type": "string"
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
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, $options);

        $this->assertEquals($expected, $result);
    }

    public function providerRemovingWriteOnlyProp() : array
    {
        return [
            [[
                // removing writeOnly prop
                'removeWriteOnly' => true
            ]],
            [[
                // removing writeOnly prop even if keeping
                'removeWriteOnly' => true,
                'keepNotSupported' => ['writeOnly']
            ]]
        ];
    }

    public function testRemovingReadOnlyFromRequired() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "required": ["prop1", "prop2"],
                "properties": {
                    "prop1": {
                        "type": "string",
                        "readOnly": true
                    },
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "required": ["prop2"],
                "properties": {
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'removeReadOnly' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testDeletingRequiredIfEmpty() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "required": ["prop1"],
                "properties": {
                    "prop1": {
                        "type": "string",
                        "readOnly": true
                    },
                    "prop2": {
                        "type": "string"
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
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'removeReadOnly' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testDeletingPropertiesIfEmpty() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "required": ["prop1"],
                "properties": {
                    "prop1": {
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
                "type": "object"
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'removeReadOnly' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testNotRemovingReadOnlyPropsByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "required": ["prop1", "prop2"],
                "properties": {
                    "prop1": {
                        "type": "string",
                        "readOnly": true
                    },
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "required": ["prop1", "prop2"],
                "properties": {
                    "prop1": {
                        "type": "string"
                    },
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        $this->assertEquals($expected, $result);
    }

    public function testNotRemovingWriteOnlyPropsByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "required": ["prop1", "prop2"],
                "properties": {
                    "prop1": {
                        "type": "string",
                        "writeOnly": true
                    },
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "required": ["prop1", "prop2"],
                "properties": {
                    "prop1": {
                        "type": "string"
                    },
                    "prop2": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        $this->assertEquals($expected, $result);
    }

    public function testDeepSchema() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "required": ["prop1", "prop2"],
                "properties": {
                    "prop1": {
                        "type": "string",
                        "readOnly": true
                    },
                    "prop2": {
                        "allOf": [
                            {
                                "type": "object",
                                "required": ["prop3"],
                                "properties": {
                                    "prop3": {
                                        "type": "object",
                                        "readOnly": true
                                    }
                                }
                            },
                            {
                                "type": "object",
                                "properties": {
                                    "prop4": {
                                        "type": "object",
                                        "readOnly": true
                                    }
                                }
                            }
                        ]
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "required": ["prop2"],
                "properties": {
                    "prop2": {
                        "allOf": [
                            {
                                "type": "object"
                            },
                            {
                                "type": "object"
                            }
                        ]
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'removeReadOnly' => true
        ]);

        $this->assertEquals($expected, $result);
    }
}
