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
 * Present Date Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class ValidPresentDate extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'presentdate';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Present Date';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_DATE;

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
    return strtotime($value) == strtotime(date('Y-m-d'));
  }
}
