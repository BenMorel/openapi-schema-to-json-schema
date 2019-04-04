<?php

declare(strict_types=1);

namespace BenMorel\OpenapiSchemaToJsonSchema;

/**
 * The options passed to the converters.
 * This is an internal class, that should not be used directly.
 */
class Options
{
    /**
     * If set to `false`, converts the provided schema in place.
     * If `true`, clones the schema by converting it to JSON and back.
     *
     * @var bool
     */
    public $cloneSchema = true;

    /**
     * This is `false` by default and leaves `date` format as is.
     * If set to `true`, sets `format: 'date'` to `format: 'date-time'`.
     *
     * @var bool
     */
    public $dateToDateTime = false;

    /**
     * If set to `true` and `x-patternProperties` property is present, change `x-patternProperties` to
     * `patternProperties` and call `patternPropertiesHandler`. If `patternPropertiesHandler` is not defined, call the
     * default handler. See `patternPropertiesHandler` for more information.
     *
     * @var bool
     */
    public $supportPatternProperties = false;

    /**
     * @var callable
     */
    public $patternPropertiesHandler;

    /**
     * @var string[]
     */
    public $removeProps = [];

    /**
     * @var string[]
     */
    public $structs;

    /**
     * @var string[]
     */
    public $notSupported;
}
