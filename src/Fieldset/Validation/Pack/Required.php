<?php //-->

namespace Cradle\Package\System\Fieldset\Validation\Pack;

use Cradle\Package\System\Fieldset\Validation\ValidationTypes;
use Cradle\Package\System\Fieldset\Validation\AbstractValidator;
use Cradle\Package\System\Fieldset\Validation\ValidatorInterface;

class Required extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'required';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Required';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_GENERAL;

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   *
   * @return bool
   */
  public function valid($value = null): bool
  {
    return !is_null($value);
  }
}
