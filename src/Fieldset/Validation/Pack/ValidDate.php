<?php //-->

namespace Cradle\Package\System\Fieldset\Validation\Pack;

use Cradle\Package\System\Fieldset\Validation\ValidationTypes;
use Cradle\Package\System\Fieldset\Validation\AbstractValidator;
use Cradle\Package\System\Fieldset\Validation\ValidatorInterface;

class ValidDate extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'date';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Date';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_DATE;

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   *
   * @return bool
   */
  public function valid($value = null): bool
  {
    return preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/', $value);
  }
}
