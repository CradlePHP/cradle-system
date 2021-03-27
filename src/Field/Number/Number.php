<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Number;

use Cradle\Package\System\Field\Input\Input;

use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Number Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Number extends Input
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'number';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Number Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'number';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_NUMBER;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_NUMBER
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_NUMBER,
    FormatTypes::TYPE_CUSTOM
  ];

  /**
   * Validation check
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return bool
   */
  public function valid(
    $value = null,
    string $name = null,
    array $row = []
  ): bool
  {
    return is_numeric($value)
      && (
        !isset($this->attributes['min'])
        || !is_numeric($this->attributes['min'])
        || $this->attributes['min'] <= $value
      )
      && (
        !isset($this->attributes['max'])
        || !is_numeric($this->attributes['max'])
        || $this->attributes['max'] >= $value
      );
  }
}
