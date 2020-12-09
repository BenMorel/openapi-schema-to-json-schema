<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Converter;

use BenMorel\OpenApiSchemaToJsonSchema\Exception\InvalidTypeException;
use BenMorel\OpenApiSchemaToJsonSchema\Options;

use stdClass;

class SchemaConverter
{
    /**
     * @param object  $schema
     * @param Options $options
     *
     * @return object
     */
    public static function convertFromSchema(object $schema, Options $options) : object
    {
        $schema = self::convertSchema($schema, $options);

        $schema->{'$schema'} = 'http://json-schema.org/draft-04/schema#';

        return $schema;
    }

    /**
     * @param object  $schema
     * @param Options $options
     *
     * @return object
     */
    private static function convertSchema(object $schema, Options $options) : object
    {
        $structs = $options->structs;
        $notSupported = $options->notSupported;

        // Handle nullable oneOf/anyOf/allOf;
        // this is not currently supported by @mikunn's nodejs package, this library is based on:
        // https://github.com/mikunn/openapi-schema-to-json-schema/issues/31

        if (isset($schema->nullable) && $schema->nullable === true) {
            foreach (['oneOf', 'anyOf', 'allOf'] as $xOf) {
                if (isset($schema->{$xOf}) && is_array($schema->{$xOf})) {
                    return self::convertNullableOneAnyAllOf($schema, $options);
                }
            }
        }

        foreach ($structs as $struct) {
            if (isset($schema->{$struct})) {
                if (is_array($schema->{$struct})) {
                    foreach ($schema->{$struct} as $key => $value) {
                        $schema->{$struct}[$key] = self::convertSchema($value, $options);
                    }
                } elseif (is_object($schema->{$struct})) {
                    $schema->{$struct} = self::convertSchema($schema->{$struct}, $options);
                }
            }
        }

        if (isset($schema->properties) && is_object($schema->properties)) {
            $schema->properties = self::convertProperties($schema->properties, $options);

            if (isset($schema->required) && is_array($schema->required)) {
                $schema->required = self::cleanRequired($schema->required, $schema->properties);

                if (! $schema->required) {
                    unset($schema->required);
                }
            }

            if (! (array) $schema->properties) {
                unset($schema->properties);
            }
        }

        if (isset($schema->type)) {
            self::validateType($schema->type);
        }

        $schema = self::convertTypes($schema);
        $schema = self::convertFormat($schema, $options);

        if (isset($schema->{'x-patternProperties'}) && is_object($schema->{'x-patternProperties'}) && $options->supportPatternProperties) {
            $schema = self::convertPatternProperties($schema, $options->patternPropertiesHandler);
        }

        foreach ($notSupported as $prop) {
            unset($schema->{$prop});
        }

        return $schema;
    }

    /**
     * @param mixed $type
     *
     * @throws InvalidTypeException
     */
    private static function validateType($type) : void
    {
        $validTypes = ['integer', 'number', 'string', 'boolean', 'object', 'array'];

        if (! in_array($type, $validTypes, true)) {
            throw new InvalidTypeException('Type ' . json_encode($type) . ' is not a valid type.');
        }
    }

    /**
     * @param object $schema
     * @param Options $options
     *
     * @return object
     */
    private static function convertNullableOneAnyAllOf(object $schema, Options $options) : object
    {
        $schemaCopy = clone $schema;
        unset($schemaCopy->nullable);
        $schemaCopy = self::convertSchema($schemaCopy, $options);

        return (object) [
            'oneOf' => [
                (object) [
                    'type' => 'null'
                ],
                $schemaCopy
            ],
        ];
    }

    /**
     * @param object  $properties
     * @param Options $options
     *
     * @return object
     */
    private static function convertProperties(object $properties, Options $options) : object
    {
        $props = new stdClass;

        foreach ($properties as $key => $property) {
            foreach ($options->removeProps as $prop) {
                if (isset($property->{$prop}) && $property->{$prop} === true) {
                    continue 2;
                }
            }

            $props->{$key} = self::convertSchema($property, $options);
        }

        return $props;
    }

    /**
     * @param object $schema
     *
     * @return object
     */
    private static function convertTypes(object $schema) : object
    {
        if (isset($schema->type) && isset($schema->nullable) && $schema->nullable === true) {
            $schema->type = [$schema->type, 'null'];

            if (isset($schema->enum) && is_array($schema->enum)) {
                $schema->enum[] = null;
            }
        }

        return $schema;
    }

    /**
     * @param object  $schema
     * @param Options $options
     *
     * @return object
     */
    private static function convertFormat(object $schema, Options $options) : object
    {
        $formats = ['date-time', 'email', 'hostname', 'ipv4', 'ipv6', 'uri', 'uri-reference'];

        if (! isset($schema->format) || in_array($schema->format, $formats)) {
            return $schema;
        }

        $format = $schema->format;

        if ($format === 'date' && $options->dateToDateTime) {
            $schema->format = 'date-time';

            return $schema;
        }

        if ($format === 'byte') {
            // Matches base64 (RFC 4648)
            // Matches `standard` base64 not `base64url`. The specification does not
            // exclude it but current ongoing OpenAPI plans will distinguish both.
            $schema->pattern = '^[\\w\\d+\\/=]*$';
        }

        return $schema;
    }

    /**
     * @param object   $schema
     * @param callable $handler
     *
     * @return object
     */
    private static function convertPatternProperties(object $schema, callable $handler) : object
    {
        $schema->patternProperties = $schema->{'x-patternProperties'};
        unset($schema->{'x-patternProperties'});

        return $handler($schema);
    }

    /**
     * @param array  $required
     * @param object $properties
     *
     * @return array
     */
    private static function cleanRequired(array $required, object $properties) : array
    {
        foreach ($required as $key => $value) {
            if (! isset($properties->{$value})) {
                unset($required[$key]);
            }
        }

        return array_values($required);
    }
}
