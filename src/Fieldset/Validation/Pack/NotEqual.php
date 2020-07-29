<?php //-->

namespace Cradle\Package\System\Fieldset\Validation\Pack;

use Cradle\Package\System\Fieldset\Field\FieldHandler;

use Cradle\Package\System\Fieldset\Validation\ValidationTypes;
use Cradle\Package\System\Fieldset\Validation\AbstractValidator;
use Cradle\Package\System\Fieldset\Validation\ValidatorInterface;

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
  const TYPE = ValidationTypes::TYPE_NUMBER;

  /**
   * When they choose this validator in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public function getConfigParameters(): array
  {
    return [
      FieldHandler::getField('text')->setAttributes([
        'placeholder' => 'Enter Number'
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
    return isset($this->parameters[0]) || $this->parameters[0] == $value;
  }
}
