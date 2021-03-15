<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation\General;

use Cradle\Package\System\Field\FieldRegistry;

use Cradle\Package\System\Validation\AbstractValidator;
use Cradle\Package\System\Validation\ValidatorInterface;
use Cradle\Package\System\Validation\ValidationTypes;

/**
 * Not Equal Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class NotEqual extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'ne';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Not Equal';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_GENERAL;

  /**
   * When they choose this validator in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldRegistry::makeField('text')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'placeholder' => 'Enter Value',
          'required' => 'required'
        ])
    ];
  }

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
    return isset($this->parameters[0]) && $this->parameters[0] == $value;
  }
}
