<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation\Date;

use Cradle\Package\System\Validation\AbstractValidator;
use Cradle\Package\System\Validation\ValidatorInterface;
use Cradle\Package\System\Validation\ValidationTypes;

/**
 * Date Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class ValidDate extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'date';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Date';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_DATE;

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   *
   * @return bool
   */
  public function valid($value = null): bool
  {
    return preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $value);
  }
}
