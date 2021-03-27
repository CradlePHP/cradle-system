<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Date;

/**
 * Time Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Time extends Datetime
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'time';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Time Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'time';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_TIME
  ];

  /**
   * Prepares the value for some sort of insertion
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?scalar
   */
  public function prepare($value = null, string $name = null, array $row = [])
  {
    if (!$value) {
      return $value;
    }

    return date('H:i:s', strtotime($value));
  }
}
