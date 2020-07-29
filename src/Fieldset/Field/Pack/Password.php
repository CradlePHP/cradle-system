<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

class Password extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'password';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Password Field';

  /**
   * @const string TYPE HTML input field type
   */
  const TYPE = 'password';

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value)
  {
    return password_hash($value, PASSWORD_DEFAULT);
  }
}
