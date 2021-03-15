<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation\Type;

use Cradle\Package\System\Validation\AbstractValidator;
use Cradle\Package\System\Validation\ValidatorInterface;
use Cradle\Package\System\Validation\ValidationTypes;

/**
 * Credit Card Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class ValidCC extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'cc';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Credit Card';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_TYPE;

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
    return preg_match('/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]'.
    '{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-'.
    '5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/', $value);
  }
}
