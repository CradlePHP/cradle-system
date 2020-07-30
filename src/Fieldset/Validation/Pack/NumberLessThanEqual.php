<?php //-->

namespace Cradle\Package\System\Fieldset\Validation\Pack;

class NumberLessThanEqual extends NotEqual
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'lte';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Less Than Equal';

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   *
   * @return bool
   */
  public function valid($value = null): bool
  {
    return isset($this->parameters[0]) && $value <= $this->parameters[0];
  }
}
