<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation\Date;

/**
 * Future Date Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class ValidFutureDate extends ValidDate
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'futuredate';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Future Date';

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field validating
   * @param ?array  $row   the row submitted with the value
   *
   * @return bool
   */
  public function valid($value = null, string $name = null, array $row = []): bool
  {
    return parent::valid($value) && strtotime($value) > time();
  }
}
