<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Tests;

use BenMorel\OpenApiSchemaToJsonSchema\Convert;
use PHPUnit\Framework\TestCase;

class CloneSchemaTest extends TestCase
{
    /**
     * @dataProvider providerCloneSchema
     */
    public function testCloneSchema(array $options, bool $cloneSchema) : void
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

        $result = Convert::openapiSchemaToJsonSchema($schema, $options);

        self::assertEquals($expected, $result);

        if ($cloneSchema) {
            self::assertNotSame($schema, $result);
        } else {
            self::assertSame($schema, $result);
        }
    }

    public function providerCloneSchema() : array
    {
        return [
            [[], true],
            [['cloneSchema' => true], true],
            [['cloneSchema' => false], false]
        ];
    }
}
