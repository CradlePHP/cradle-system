<?php //-->

namespace Cradle\Package\System\Fieldset\Validation\Pack;


class ValidPastDate extends ValidDate
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'pastdate';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Past Date';

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   *
   * @return bool
   */
  public function valid($value = null): bool
  {
    return parent::valid($value) && strtotime($value) < time();
  }
}
