<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class PatternPropertiesTest extends TestCase
{
    public function testHandlingAdditionalPropertiesOfSameTypeString() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "string"
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
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
                "additionalProperties": false,
                "patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testHandlingAdditionalPropertiesOfSameTypeNumber() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "number"
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
                        "type": "number"
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "additionalProperties": false,
                "patternProperties": {
                    "^[a-z]*$": {
                        "type": "number"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testHandlingAdditionalPropertiesWithOneOfPatternPropertyTypes() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "number"
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    },
                    "^[A-Z]*$": {
                        "type": "number"
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "additionalProperties": false,
                "patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    },
                    "^[A-Z]*$": {
                        "type": "number"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testHandlingAdditionalPropertiesWithMatchingObjects() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "object",
                    "properties": {
                        "test": {
                            "type": "string"
                        }
                    }
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    },
                    "^[A-Z]*$": {
                        "type": "object",
                        "properties": {
                            "test": {
                                "type": "string"
                            }
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
                "additionalProperties": false,
                "patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    },
                    "^[A-Z]*$": {
                        "type": "object",
                        "properties": {
                            "test": {
                                "type": "string"
                            }
                        }
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testHandlingAdditionalPropertiesWithNonMatchingObjects() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "object",
                    "properties": {
                        "test": {
                            "type": "string"
                        }
                    }
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    },
                    "^[A-Z]*$": {
                        "type": "object",
                        "properties": {
                            "test": {
                                "type": "integer"
                            }
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
                "additionalProperties": {
                    "type": "object",
                    "properties": {
                        "test": {
                            "type": "string"
                        }
                    }
                },
                "patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    },
                    "^[A-Z]*$": {
                        "type": "object",
                        "properties": {
                            "test": {
                                "type": "integer"
                            }
                        }
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testHandlingAdditionalPropertiesWithMatchingArray() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "array",
                    "items": {
                        "type": "string"
                    }
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    },
                    "^[A-Z]*$": {
                        "type": "array",
                        "items": {
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
                "additionalProperties": false,
                "patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    },
                    "^[A-Z]*$": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testHandlingAdditionalPropertiesWithCompositionTypes() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "oneOf": [
                        {
                            "type": "string"
                        },
                        {
                            "type": "integer"
                        }
                    ]
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
                        "oneOf": [
                            {
                                "type": "string"
                            },
                            {
                                "type": "integer"
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
                "additionalProperties": false,
                "patternProperties": {
                    "^[a-z]*$": {
                        "oneOf": [
                            {
                                "type": "string"
                            },
                            {
                                "type": "integer"
                            }
                        ]
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => true
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testNotSupportingPatternProperties() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "string"
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
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
                "additionalProperties": {
                    "type": "string"
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => false
        ]);

        $this->assertEquals($expected, $result);
    }

    public function testNotSupportingPatternPropertiesByDefault() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "string"
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
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
                "additionalProperties": {
                    "type": "string"
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema);

        $this->assertEquals($expected, $result);
    }

    public function testSettingCustomPatternPropertiesHandler() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": {
                    "type": "string"
                },
                "x-patternProperties": {
                    "^[a-z]*$": {
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
                "additionalProperties": {
                    "type": "string"
                },
                "patternProperties": false
            }
JSON
        );

        $options = [
            'supportPatternProperties' => true,
            'patternPropertiesHandler' => function(object $schema) : object {
                $schema->patternProperties = false;

                return $schema;
            }
        ];

        $result = Convert::openapiSchemaToJsonSchema($schema, $options);

        $this->assertEquals($expected, $result);
    }

    public function testAdditionalPropertiesNotModifiedIfSetToTrue() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "type": "object",
                "additionalProperties": true,
                "x-patternProperties": {
                    "^[a-z]*$": {
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
                "additionalProperties": true,
                "patternProperties": {
                    "^[a-z]*$": {
                        "type": "string"
                    }
                }
            }
JSON
        );

        $result = Convert::openapiSchemaToJsonSchema($schema, [
            'supportPatternProperties' => true
        ]);

        $this->assertEquals($expected, $result);
    }
}
