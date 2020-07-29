<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

class Color extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'color';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Color Field';

  /**
   * @const string TYPE HTML input field type
   */
  const TYPE = 'color';

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool
  {
    return preg_match('/^[0-9a-fA-F]{6}$/', $value);
  }
}
