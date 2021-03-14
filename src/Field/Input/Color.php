<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Input;

/**
 * Color Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Color extends Input
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
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'color';

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
