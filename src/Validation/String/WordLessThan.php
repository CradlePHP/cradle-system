<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation\String;

use Cradle\Package\System\Field\FieldRegistry;

use Cradle\Package\System\Validation\AbstractValidator;
use Cradle\Package\System\Validation\ValidatorInterface;
use Cradle\Package\System\Validation\ValidationTypes;

/**
 * Words Less Than Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class WordLessThan extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'word_lt';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Words Less Than';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_STRING;

  /**
   * When they choose this validator in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldRegistry::makeField('number')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'placeholder' => 'Enter Number',
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
    return isset($this->parameters[0]) && str_word_count($value) < $this->parameters[0];
  }
}
