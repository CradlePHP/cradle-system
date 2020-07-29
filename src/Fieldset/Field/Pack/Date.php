<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

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
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value)
  {
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
    return preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $value);
  }
}
