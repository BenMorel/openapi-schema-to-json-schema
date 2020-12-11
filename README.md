# OpenAPI schema to JSON Schema

A PHP library to convert an OpenAPI schema or parameter object to [JSON Schema](https://json-schema.org/).

[![Build Status](https://www.travis-ci.com/BenMorel/openapi-schema-to-json-schema.svg?branch=master)](https://www.travis-ci.com/BenMorel/openapi-schema-to-json-schema)
[![Coverage Status](https://coveralls.io/repos/github/BenMorel/openapi-schema-to-json-schema/badge.svg?branch=master)](https://coveralls.io/github/BenMorel/openapi-schema-to-json-schema?branch=master)
[![Latest Stable Version](https://poser.pugx.org/benmorel/openapi-schema-to-json-schema/v/stable)](https://packagist.org/packages/benmorel/openapi-schema-to-json-schema)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

This is a port of the [nodejs package](https://www.npmjs.com/package/openapi-schema-to-json-schema) by [@mikunn](https://github.com/mikunn).

It currently converts from [OpenAPI 3.0](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md) to [JSON Schema Draft 4](http://json-schema.org/specification-links.html#draft-4).

## Why?

OpenAPI is a specification for describing RESTful APIs. OpenAPI 3.0 allows us to describe the structures of request and response payloads in a detailed manner. This would, theoretically, mean that we should be able to automatically validate request and response payloads. However, at the time of writing there aren't many validators around.

The good news is that there are many validators for JSON Schema, such as [justinrainbow/json-schema](https://github.com/justinrainbow/json-schema). The bad news is that OpenAPI 3.0 is not entirely compatible with JSON Schema. The Schema object of OpenAPI 3.0 is an extended subset of JSON Schema Specification Wright Draft 00 with some differences.

The purpose of this project is to fill the gap by doing the conversion between these two formats.

## Features

* converts OpenAPI 3.0 Schema object to JSON Schema Draft 4
* converts OpenAPI 3.0 Parameter object to JSON Schema Draft 4
* deletes `nullable` and adds `"null"` to `type` array if `nullable` is `true`
* supports deep structures with nested `allOf`s etc.
* removes [OpenAPI specific properties](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#fixed-fields-20) such as `discriminator`, `deprecated` etc. unless specified otherwise
* optionally supports `patternProperties` with `x-patternProperties` in the Schema object

**NOTE**: `$ref`s are not dereferenced. Use a dereferencer prior to using this package.

## Installation

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require benmorel/openapi-schema-to-json-schema
```

## Requirements

This library requires:

- PHP 7.2 or later
- the [json](http://php.net/manual/en/book.json.php) extension

There is no dependency on third-party libraries.

## Project status & release process

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (adding new methods, optimizing
existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.**

It is therefore safe to lock your project to a given release cycle, such as `0.1.*`.

If you need to upgrade to a newer release cycle, check the [release history](https://github.com/BenMorel/openapi-schema-to-json-schema/releases)
for a list of changes introduced by each further `0.x.0` version.

## Converting an OpenAPI schema

Here's a small example to get the idea:

```php
use BenMorel\OpenApiSchemaToJsonSchema\Convert;

$schema = json_decode(<<<'JSON'
    {
        "type": "string",
        "format": "date-time",
        "nullable": true
    }
JSON
);

$convertedSchema = Convert::openapiSchemaToJsonSchema($schema);

echo json_encode($convertedSchema, JSON_PRETTY_PRINT);
```

The example prints out:

```json
{
    "type": [
        "string",
        "null"
    ],
    "format": "date-time",
    "$schema": "http:\/\/json-schema.org\/draft-04\/schema#"
}
```

## Converting an OpenAPI parameter

OpenAPI parameters can be converted:

```php
use BenMorel\OpenApiSchemaToJsonSchema\Convert;

$param = json_decode(<<<'JSON'
    {
        "name": "parameter name",
        "in": "query",
        "schema": {
            "type": "string",
            "format": "date"
        }
    }
JSON
);

$convertedSchema = Convert::openapiParameterToJsonSchema($param);

echo json_encode($convertedSchema, JSON_PRETTY_PRINT);
```

The result is as follows:

```json
{
    "type": "string",
    "format": "date",
    "$schema": "http:\/\/json-schema.org\/draft-04\/schema#"
}
```

When a parameter has several schemas (one per MIME type) a map is returned instead:

```json
{
    "name": "parameter name",
    "in": "query",
    "content": {
        "application/javascript": {
            "schema": {
                "type": "string"
            }
        },
        "text/css": {
            "schema": {
                "type": "string"
            }
        }
    }
}
```

would be converted to:

```json
{
    "application\/javascript": {
        "type": "string",
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#"
    },
    "text\/css": {
        "type": "string",
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#"
    }
}
```

## Options

Both `Convert::openapiSchemaToJsonSchema()` and `Convert::openapiParameterToJsonSchema()` accept a `$options` associative array as the second argument, with the following keys:

### `cloneSchema` (bool)

If set to `false`, converts the provided schema in place. If `true`, clones the schema by converting it to JSON and back. The overhead of the cloning is usually negligible. Defaults to `true`.

### `dateToDateTime` (bool)

This is `false` by default and leaves `date` format as is. If set to `true`, sets `format: 'date'` to `format: 'date-time'`.

For example:

```json
{
  "type": "string",
  "format": "date"
}
```

is converted to:

```json
{
    "type": "string",
    "format": "date-time",
    "$schema": "http:\/\/json-schema.org\/draft-04\/schema#"
}
```

### `keepNotSupported` (array)

By default, the following fields are removed from the result schema: `nullable`, `discriminator`, `readOnly`, `writeOnly`, `xml`, `externalDocs`, `example` and `deprecated` as they are not supported by JSON Schema Draft 4. Provide an array of the ones you want to keep (as strings) and they won't be removed.

### `removeReadOnly` (bool)

If set to `true`, will remove properties set as `readOnly`. If the property is set as `required`, it will be removed from the `required` array as well. The property will be removed even if `readOnly` is set to be kept with `keepNotSupported`.

### `removeWriteOnly` (bool)

Similar to `removeReadOnly`, but for `writeOnly` properties.

### `supportPatternProperties` (bool)

If set to `true` and `x-patternProperties` property is present, change `x-patternProperties` to `patternProperties` and call `patternPropertiesHandler`. If `patternPropertiesHandler` is not defined, call the default handler. See `patternPropertiesHandler` for more information.

### `patternPropertiesHandler` (function)

Provide a function to handle pattern properties and set `supportPatternProperties` to take effect. The function takes the schema where `x-patternProperties` is defined on the root level. At this point `x-patternProperties` is changed to `patternProperties`. It must return the modified schema.

If the handler is not provided, the default handler is used. If `additionalProperties` is set and is an object, the default handler sets it to false if the `additionalProperties` object has deep equality with a pattern object inside `patternProperties`. This is because we might want to define `additionalProperties` in OpenAPI spec file, but want to validate against a pattern. The pattern would turn out to be useless if `additionalProperties` of the same structure were allowed. Create you own handler to override this functionality.

See `tests/PatternPropertiesTest.php` for examples of how this works.
