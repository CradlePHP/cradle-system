<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

class Time extends Date
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
   * @const string TYPE HTML input field type
   */
  const TYPE = 'time';

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

    return date('H:i:s', strtotime($value));
  }
}
