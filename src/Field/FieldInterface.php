<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field;

/**
 * Required field methods
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
interface FieldInterface
{
  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value = null);

  /**
   * Renders the field for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function render($value = null): ?string;

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool;
}
