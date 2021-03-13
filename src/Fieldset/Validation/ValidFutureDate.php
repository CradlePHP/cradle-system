<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Fieldset\Validation;


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
   * @param ?mixed $value
   *
   * @return bool
   */
  public function valid($value = null): bool
  {
    return parent::valid($value) && strtotime($value) > time();
  }
}
