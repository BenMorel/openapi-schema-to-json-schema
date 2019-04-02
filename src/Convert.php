<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema;

use BenMorel\OpenApiSchemaToJsonSchema\Converter\ParameterConverter;
use BenMorel\OpenApiSchemaToJsonSchema\Converter\SchemaConverter;

use Closure;

class Convert
{
    /**
     * @param object $schema
     * @param array  $options
     *
     * @return object
     *
     * @throws Exception\InvalidInputException
     * @throws Exception\InvalidTypeException
     */
    public static function openapiSchemaToJsonSchema(object $schema, array $options = []) : object
    {
        $options = self::resolveOptions($options);

        if ($options->cloneSchema) {
            $schema = json_decode(json_encode($schema));
        }

        return SchemaConverter::convertFromSchema($schema, $options);
    }

    /**
     * @param object $schema
     * @param array  $options
     *
     * @return object
     *
     * @throws Exception\InvalidInputException
     * @throws Exception\InvalidTypeException
     */
    public static function openapiParameterToJsonSchema(object $schema, array $options = []) : object
    {
        $options = self::resolveOptions($options);

        if ($options->cloneSchema) {
            $schema = json_decode(json_encode($schema));
        }

        return ParameterConverter::convertFromParameter($schema, $options);
    }

    /**
     * @param array $options
     *
     * @return Options
     */
    private static function resolveOptions(array $options) : Options
    {
        $notSupported = [
            'nullable', 'discriminator', 'readOnly',
            'writeOnly', 'xml', 'externalDocs',
            'example', 'deprecated'
        ];

        $optionsObject = new Options();

        if (isset($options['dateToDateTime'])) {
            $optionsObject->dateToDateTime = (bool) $options['dateToDateTime'];
        }

        if (isset($options['cloneSchema'])) {
            $optionsObject->cloneSchema = (bool) $options['cloneSchema'];
        }

        if (isset($options['supportPatternProperties'])) {
            $optionsObject->supportPatternProperties = (bool) $options['supportPatternProperties'];
        }

        if (isset($options['patternPropertiesHandler']) && is_callable($options['patternPropertiesHandler'])) {
            $optionsObject->patternPropertiesHandler = $options['patternPropertiesHandler'];
        } else {
            $optionsObject->patternPropertiesHandler = self::patternPropertiesHandler();
        }

        if (isset($options['removeReadOnly']) && $options['removeReadOnly']) {
            $optionsObject->removeProps[] = 'readOnly';
        }

        if (isset($options['removeWriteOnly']) && $options['removeWriteOnly']) {
            $optionsObject->removeProps[] = 'writeOnly';
        }

        $optionsObject->structs = ['allOf', 'anyOf', 'oneOf', 'not', 'items', 'additionalProperties'];

        if (isset($options['keepNotSupported']) && is_array($options['keepNotSupported'])) {
            $optionsObject->notSupported = self::resolveNotSupported($notSupported, $options['keepNotSupported']);
        } else {
            $optionsObject->notSupported = $notSupported;
        }

        return $optionsObject;
    }

    /**
     * @param string[] $notSupported
     * @param string[] $toRetain
     *
     * @return string[]
     */
    private static function resolveNotSupported(array $notSupported, array $toRetain) : array
    {
        return array_values(array_diff($notSupported, $toRetain));
    }

    /**
     * @return Closure
     */
    private static function patternPropertiesHandler() : Closure
    {
        return function(object $schema) : object {
            if (! isset($schema->additionalProperties) || ! is_object($schema->additionalProperties)) {
                return $schema;
            }

            if (! isset($schema->patternProperties) || ! is_object($schema->patternProperties)) {
                return $schema;
            }

            $additProps = $schema->additionalProperties;
            $patternsObj = $schema->patternProperties;

            foreach ($patternsObj as $pattern => $patternObj) {
                if ($patternObj == $additProps) {
                    $schema->additionalProperties = false;
                    break;
                }
            }

            return $schema;
        };
    }
}
