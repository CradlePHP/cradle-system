<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Date;

use Cradle\Package\System\Field\Input\Input;

use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Datetime Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Datetime extends Input
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'datetime';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Datetime Field';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_DATE;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_DATETIME
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_DATE
  ];

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value)
  {
    if (is_null($value)) {
      return $value;
    }

    return date('Y-m-d H:i:s', strtotime($value));
  }

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool
  {
    return strtotime($value) !== false;
  }
}
