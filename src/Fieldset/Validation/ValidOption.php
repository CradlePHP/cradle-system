<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Fieldset\Validation;

use Cradle\Package\System\Fieldset\Field\FieldHandler;

class ValidOption extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'one';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Option';

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
      FieldHandler::makeField('textlist')
        ->setName('{NAME}[parameters]')
        ->setAttributes([
          'data-label-add' => 'Add Option'
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
    return is_null($value) || in_array($value, $this->parameters);
  }
}
