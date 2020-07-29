<?php //-->

namespace Cradle\Package\System\Fieldset\Validation\Pack;

use Cradle\Package\System\Fieldset\Validation\ValidationTypes;
use Cradle\Package\System\Fieldset\Validation\AbstractValidator;
use Cradle\Package\System\Fieldset\Validation\ValidatorInterface;

class NotEmpty extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'notempty';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Not Empty';

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
    return !!$value;
  }
}
