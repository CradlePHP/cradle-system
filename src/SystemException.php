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
class SystemException extends BaseException
{

  /**
   * @const string ERROR_FIELDSET_NOT_FOUND Error template
   */
  const ERROR_FIELDSET_NOT_FOUND = 'Could not find fieldset %s.';

  /**
   * @const string ERROR_FIELDSET_EXISTS
   */
  const ERROR_FIELDSET_EXISTS = 'Unable to restore %s, fieldset already exists.';

  /**
   * @const string ERROR_FIELDSET_ARCHIVE_EXISTS
   */
  const ERROR_FIELDSET_ARCHIVE_EXISTS = 'Unable to archive %s, an archive of the fieldset already exists.';

  /**
   * @const string ERROR_NO_SCHEMA Error template
   */
  const ERROR_NO_SCHEMA = 'Schema is not loaded';

  /**
   * @const string ERROR_SCHEMA_NOT_FOUND Error template
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
   * Create a new exception for missing fieldset
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forFieldsetNotFound($name): SystemException
  {
    $message = sprintf(static::ERROR_FIELDSET_NOT_FOUND, $name);
    return new static($message);
  }

  /**
   * Create a new exception if fieldset already exists
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forFieldsetAlreadyExists(string $name): SystemException
  {
    $message = sprintf(static::ERROR_FIELDSET_EXISTS, $name);
    return new static($message);
  }

  /**
   * Create a new exception if an archived of the
   * given fieldset already exists.
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forFieldsetArchiveExists(string $name): SystemException
  {
    $message = sprintf(static::ERROR_FIELDSET_ARCHIVE_EXISTS, $name);
    return new static($message);
  }

  /**
   * Create a new exception for missing Schema
   *
   * @return SystemException
   */
  public static function forNoRelation($name, $relation): SystemException
  {
    return new static(sprintf(static::ERROR_SCHEMA_NO_RELATION, $name, $relation));
  }

  /**
   * Create a new exception for missing Schema
   *
   * @return SystemException
   */
  public static function forNoSchema(): SystemException
  {
    return new static(static::ERROR_NO_SCHEMA);
  }

  /**
   * Create a new exception for missing Schema
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forSchemaNotFound($name): SystemException
  {
    $message = sprintf(static::ERROR_SCHEMA_NOT_FOUND, $name);
    return new static($message);
  }

  /**
   * Create a new exception if schema already exists
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forSchemaAlreadyExists(string $name): SystemException
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
   * @return SystemException
   */
  public static function forSchemaArchiveExists(string $name): SystemException
  {
    $message = sprintf(static::ERROR_SCHEMA_ARCHIVE_EXISTS, $name);
    return new static($message);
  }
}
