<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation;

/**
 * Required validator methods
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
interface ValidatorInterface
{
  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   * @param ?array $parameters
   *
   * @return bool
   */
  public function valid($value = null): bool;
}
