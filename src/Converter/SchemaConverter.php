<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Converter;

use BenMorel\OpenApiSchemaToJsonSchema\Exception\InvalidTypeException;
use BenMorel\OpenapiSchemaToJsonSchema\Options;

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
     * @param object  $properties
     * @param Options $options
     *
     * @return object
     */
    private static function convertProperties(object $properties, Options $options) : object
    {
        $props = new stdClass;

        foreach ($properties as $key => $property) {
            $removeProp = false;

            foreach ($options->removeProps as $prop) {
                if (isset($properties->{$prop}) && $properties->{$prop} === true) {
                    $removeProp = true;
                    break;
                }
            }

            if ($removeProp) {
                continue;
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
                $schema->enum[] = 'null';
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

        $settings = [
            'MIN_INT_32' => -2147483648,
		    'MAX_INT_32' => 2147483647,
		    'MIN_INT_64' => -9223372036854775808,
            'MAX_INT_64' => 9223372036854775807,
		    'MIN_FLOAT' => - pow(2, 128),
		    'MAX_FLOAT' => pow(2, 128) - 1,
		    'MIN_DOUBLE' => PHP_FLOAT_MIN,
		    'MAX_DOUBLE' => PHP_FLOAT_MAX,

            // Matches base64 (RFC 4648)
            // Matches `standard` base64 not `base64url`. The specification does not
            // exclude it but current ongoing OpenAPI plans will distinguish both.
            'BYTE_PATTERN' => '^[\\w\\d+\\/=]*$'
        ];

        if ($format === 'date' && $options->dateToDateTime) {
            return self::convertFormatDate($schema);
        }

        $formatConverters = [
            'int32'  => [self::class, 'convertFormatInt32'],
            'int64'  => [self::class, 'convertFormatInt64'],
            'float'  => [self::class, 'convertFormatFloat'],
            'double' => [self::class, 'convertFormatDouble'],
            'byte'   => [self::class, 'convertFormatByte']
        ];

        if (! isset($formatConverters[$format])) {
            return $schema;
        }

        $converter = $formatConverters[$format];

        return $converter($schema, $settings);
    }

    /**
     * @param object $schema
     * @param array  $settings
     *
     * @return object
     */
    private static function convertFormatInt32(object $schema, array $settings) : object
    {
        $schema->minimum = $settings['MIN_INT_32'];
        $schema->maximum = $settings['MAX_INT_32'];

        return $schema;
    }

    /**
     * @param object $schema
     * @param array  $settings
     *
     * @return object
     */
    private static function convertFormatInt64(object $schema, array $settings) : object
    {
        $schema->minimum = $settings['MIN_INT_64'];
        $schema->maximum = $settings['MAX_INT_64'];

        return $schema;
    }

    /**
     * @param object $schema
     * @param array  $settings
     *
     * @return object
     */
    private static function convertFormatFloat(object $schema, array $settings) : object
    {
        $schema->minimum = $settings['MIN_FLOAT'];
        $schema->maximum = $settings['MAX_FLOAT'];

        return $schema;
    }

    /**
     * @param object $schema
     * @param array  $settings
     *
     * @return object
     */
    private static function convertFormatDouble(object $schema, array $settings) : object
    {
        $schema->minimum = $settings['MIN_DOUBLE'];
        $schema->maximum = $settings['MAX_DOUBLE'];

        return $schema;
    }

    /**
     * @param object $schema
     *
     * @return object
     */
    private static function convertFormatDate(object $schema) : object
    {
        $schema->format = 'date-time';

        return $schema;
    }

    /**
     * @param object $schema
     * @param array  $settings
     *
     * @return object
     */
    private static function convertFormatByte(object $schema, array $settings) : object
    {
        $schema->pattern = $settings['BYTE_PATTERN'];

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
