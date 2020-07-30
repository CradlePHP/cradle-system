<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

class Datetime extends Date
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
   * @const string TYPE HTML input field type
   */
  const TYPE = 'datetime';

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
}
