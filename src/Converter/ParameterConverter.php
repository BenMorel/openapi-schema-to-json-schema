<?php

declare(strict_types=1);

namespace BenMorel\OpenApiSchemaToJsonSchema\Converter;

use BenMorel\OpenApiSchemaToJsonSchema\Exception\InvalidInputException;
use BenMorel\OpenapiSchemaToJsonSchema\Options;

use stdClass;

class ParameterConverter
{
    /**
     * @param object  $parameter
     * @param Options $options
     *
     * @return object
     *
     * @throws InvalidInputException
     */
    public static function convertFromParameter(object $parameter, Options $options) : object
    {
        if (isset($parameter->schema)) {
            return self::convertParameterSchema($parameter, $parameter->schema, $options);
        }

        if (isset($parameter->content)) {
            return self::convertFromContents($parameter, $options);
        }

        throw new InvalidInputException('OpenAPI parameter must have either a \'schema\' or a \'content\' property.');
    }

    /**
     * @param object  $parameter
     * @param Options $options
     *
     * @return object
     */
    private static function convertFromContents(object $parameter, Options $options) : object
    {
        $schemas = new stdClass;

        foreach ($parameter->content as $mime => $content) {
            $schema = isset($content->schema) && is_object($content->schema) ? $content->schema : new stdClass;
            $schemas->{$mime} = self::convertParameterSchema($parameter, $schema, $options);
        }

        return $schemas;
    }

    /**
     * @param object $parameter
     * @param object $schema
     * @param Options $options
     *
     * @return object
     */
    private static function convertParameterSchema(object $parameter, object $schema, Options $options) : object
    {
        $jsonSchema = SchemaConverter::convertFromSchema($schema, $options);

        if (isset($parameter->description)) {
            $jsonSchema->description = $parameter->description;
        }

        return $jsonSchema;
    }
}
