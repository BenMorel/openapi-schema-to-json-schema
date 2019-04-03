<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use BenMorel\OpenApiSchemaToJsonSchema\Exception\InvalidInputException;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testConvertingMinimalOpenApi3Parameter() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "name": "parameter name",
                "in": "cookie",
                "schema": {
                    "type": "string",
                    "nullable": true
                }
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

        $result = Convert::openapiParameterToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testConvertingExtensiveOpenApi3Parameter() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "name": "parameter name",
                "in": "cookie",
                "schema": {
                    "type": "string",
                    "nullable": true
                },
                "required": true,
                "allowEmptyValue": true,
                "deprecated": true,
                "allowReserved": true,
                "style": "matrix",
                "explode": true,
                "example": "parameter example"
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

        $result = Convert::openapiParameterToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testConvertingOpenApi3ParameterWithMimeSchemas() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "name": "parameter name",
                "in": "cookie",
                "content": {
                    "application/javascript": {
                        "schema": {
                            "type": "string",
                            "nullable": true
                        }
                    },
                    "text/css": {
                        "schema": {
                            "type": "string",
                            "nullable": true
                        }
                    }
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "application/javascript": {
                    "$schema": "http://json-schema.org/draft-04/schema#",
                    "type": ["string", "null"]
                },
                "text/css": {
                    "$schema": "http://json-schema.org/draft-04/schema#",
                    "type": ["string", "null"]
                }
            }
JSON
        );

        $result = Convert::openapiParameterToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testConvertingOpenApi3ParameterWithMimesWithoutSchema() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "name": "parameter name",
                "in": "cookie",
                "content": {
                    "application/javascript": {
                        "schema": {
                            "type": "string",
                            "nullable": true
                        }
                    },
                    "text/css": {}
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "application/javascript": {
                    "$schema": "http://json-schema.org/draft-04/schema#",
                    "type": ["string", "null"]
                },
                "text/css": {
                    "$schema": "http://json-schema.org/draft-04/schema#"
                }
            }
JSON
        );

        $result = Convert::openapiParameterToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testUsingOpenApi3ParameterDescription() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "name": "parameter name",
                "in": "cookie",
                "description": "parameter description",
                "schema": {
                    "description": "schema description"
                }
            }
JSON
        );

        $expected = json_decode(<<<'JSON'
        {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "description": "parameter description"
            }
JSON
        );

        $result = Convert::openapiParameterToJsonSchema($schema);
        $this->assertEquals($expected, $result);
    }

    public function testThrowingOnOpenApi3ParametersWithoutSchemas() : void
    {
        $schema = json_decode(<<<'JSON'
        {
                "name": "parameter name",
                "in": "cookie"
            }
JSON
        );

        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage("OpenAPI parameter must have either a 'schema' or a 'content' property.");

        Convert::openapiParameterToJsonSchema($schema);
    }
}
