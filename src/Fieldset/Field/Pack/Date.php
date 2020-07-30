<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

use Cradle\Package\System\Fieldset\Format\FormatTypes;

class Date extends Text
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
   * @const string TYPE HTML input field type
   */
  const TYPE = 'date';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [];

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

    return date('Y-m-d', strtotime($value));
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
