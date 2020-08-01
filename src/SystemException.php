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
   * @const string ERROR_ARCHIVE_EXISTS
   */
  const ERROR_ARCHIVE_EXISTS = 'Unable to archive %s, archive already exists.';

  /**
   * @const string ERROR_ARCHIVE_NOT_FOUND
   */
  const ERROR_ARCHIVE_NOT_FOUND = 'Archive %s not found';

  /**
   * @const string ERROR_FILE_EXISTS
   */
  const ERROR_FILE_EXISTS = 'Unable to restore %s, file already exists.';

  /**
   * @const string ERROR_FILE_NOT_FOUND
   */
  const ERROR_FILE_NOT_FOUND = 'File %s not found';

  /**
   * @const string ERROR_FOLDER_NOT_FOUND
   */
  const ERROR_FOLDER_NOT_FOUND = 'Folder %s not found';

  /**
   * @const string ERROR_NO_SCHEMA Error template
   */
  const ERROR_NO_SCHEMA = 'Schema is not loaded';

  /**
   * @const string ERROR_SCHEMA_NO_RELATION
   */
  const ERROR_SCHEMA_NO_RELATION = '%s has no relation to %s';

  /**
   * Create a new exception if an archive already exists.
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forArchiveExists(string $name): SystemException
  {
    $message = sprintf(static::ERROR_ARCHIVE_EXISTS, $name);
    return new static($message);
  }

  /**
   * Create a new exception for missing folder
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forArchiveNotFound($name): SystemException
  {
    $message = sprintf(static::ERROR_ARCHHIVE_NOT_FOUND, $name);
    return new static($message);
  }

  /**
   * Create a new exception if a file already exists.
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forFileExists(string $name): SystemException
  {
    $message = sprintf(static::ERROR_FILE_EXISTS, $name);
    return new static($message);
  }

  /**
   * Create a new exception for missing folder
   *
   * @param *string $name
   *
   * @return SystemException
   */
  public static function forFileNotFound($name): SystemException
  {
    $message = sprintf(static::ERROR_FILE_NOT_FOUND, $name);
    return new static($message);
  }

  /**
   * Create a new exception for missing folder
   *
   * @param ?string $path
   *
   * @return SystemException
   */
  public static function forFolderNotFound(?string $path = null): SystemException
  {
    $message = sprintf(static::ERROR_FOLDER_NOT_FOUND, $path ? $path: '?');
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
}
