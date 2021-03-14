<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field;

/**
 * Field to data types categorization
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class FieldTypes
{
  const TYPE_BOOL = 'bool';
  const TYPE_DATE = 'date';
  const TYPE_DATETIME = 'datetime';
  const TYPE_FLOAT = 'float';
  const TYPE_INT = 'int';
  const TYPE_STRING = 'string';
  const TYPE_TEXT = 'text';
  const TYPE_TIME = 'time';

  const TYPE_FILE = 'file';
  const TYPE_HASH = 'hash';
  const TYPE_JSON = 'json';
  const TYPE_NUMBER = 'number';
  const TYPE_OBJECT = 'object';
  const TYPE_URL = 'url';

  const TYPE_FILE_LIST = 'file[]';
  const TYPE_FLOAT_LIST = 'float[]';
  const TYPE_HASH_LIST = 'hash[]';
  const TYPE_INT_LIST = 'int[]';
  const TYPE_NUMBER_LIST = 'number[]';
  const TYPE_STRING_LIST = 'string[]';
  const TYPE_TEXT_LIST = 'text[]';
  const TYPE_URL_LIST = 'url[]';

  const TYPE_GENERAL = 'general';
  const TYPE_OPTION = 'option';
  const TYPE_CUSTOM = 'custom';
}
