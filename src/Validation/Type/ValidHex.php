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
 * Hex Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class ValidHex extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'hex';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Hex';

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
    return preg_match('/^[0-9a-fA-F]{6}$/', $value);
  }
}