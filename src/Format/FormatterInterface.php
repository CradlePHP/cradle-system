<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Format;

/**
 * Required formatter methods
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
interface FormatterInterface
{
  /**
   * Renders the output format for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function format($value = null): ?string;
}
