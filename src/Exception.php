<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System;

use Exception as BaseException;

/**
 * Resolver exceptions
 *
 * @package  Cradle
 * @category Resolver
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Exception extends BaseException
{
    /**
     * @const string ERROR_NO_MODEL Error template
     */
    const ERROR_NO_SCHEMA = 'Schema is not loaded';

    /**
     * @const string ERROR_CONFIG_NOT_FOUND Error template
     */
    const ERROR_SCHEMA_NOT_FOUND = 'Could not find schema %s.';

    /**
     * @const string ERROR_SCHEMA_EXISTS
     */
    const ERROR_SCHEMA_EXISTS = 'Unable to restore %s, schema already exists.';

    /**
     * @const string ERROR_SCHEMA_ARCHIVE_EXISTS
     */
    const ERROR_SCHEMA_ARCHIVE_EXISTS = 'Unable to archive %s, an archive of the schema already exists.';

    /**
     * @const string ERROR_SCHEMA_NO_RELATION
     */
    const ERROR_SCHEMA_NO_RELATION = '%s has no relation to %s';

    /**
     * Create a new exception for missing Schema
     *
     * @return Exception
     */
    public static function forNoRelation($name, $relation): Exception
    {
        return new static(sprintf(static::ERROR_SCHEMA_NO_RELATION, $name, $relation));
    }

    /**
     * Create a new exception for missing Schema
     *
     * @return Exception
     */
    public static function forNoSchema(): Exception
    {
        return new static(static::ERROR_NO_SCHEMA);
    }

    /**
     * Create a new exception for missing config
     *
     * @param *string $name
     *
     * @return Exception
     */
    public static function forSchemaNotFound($name): Exception
    {
        $message = sprintf(static::ERROR_SCHEMA_NOT_FOUND, $name);
        return new static($message);
    }

    /**
     * Create a new exception if schema already exists
     *
     * @param *string $name
     *
     * @return Exception
     */
    public static function forSchemaAlreadyExists(string $name)
    {
        $message = sprintf(static::ERROR_SCHEMA_EXISTS, $name);
        return new static($message);
    }

    /**
     * Create a new exception if an archived of the
     * given schema already exists.
     *
     * @param *string $name
     *
     * @return Exception
     */
    public static function forSchemaArchiveExists(string $name)
    {
        $message = sprintf(static::ERROR_SCHEMA_ARCHIVE_EXISTS, $name);
        return new static($message);
    }
}
