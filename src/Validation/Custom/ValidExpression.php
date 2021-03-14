<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation\Custom;

use Cradle\Package\System\Field\FieldRegistry;

use Cradle\Package\System\Validation\AbstractValidator;
use Cradle\Package\System\Validation\ValidatorInterface;
use Cradle\Package\System\Validation\ValidationTypes;

/**
 * RegEx Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class ValidExpression extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'expression';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Expression';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_CUSTOM;

  /**
   * When they choose this validator in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldRegistry::makeField('input')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'type' => 'text',
          'placeholder' => 'Enter Regular Expression'
        ])
    ];
  }

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   *
   * @return bool
   */
  public function valid($value = null): bool
  {
    return isset($this->parameters[0]) && preg_match($this->parameters[0], $value);
  }
}
