<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Date;

/**
 * Date Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Date extends Datetime
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'date';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Date Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'date';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_DATE
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

    return date('Y-m-d', strtotime($value));
  }
}
