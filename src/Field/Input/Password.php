<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Input;

/**
 * Password Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
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
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'password';

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value = null)
  {
    if (is_null($value)) {
      return $value;
    }

    return password_hash($value, PASSWORD_DEFAULT);
  }
}
