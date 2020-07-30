<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

class Number extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'number';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Number Field';

  /**
   * @const string TYPE HTML input field type
   */
  const TYPE = 'number';

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool
  {
    return is_numeric($value)
      && (
        !isset($this->attributes['min'])
        || !is_numeric($this->attributes['min'])
        || $this->attributes['min'] <= $value
      )
      && (
        !isset($this->attributes['max'])
        || !is_numeric($this->attributes['max'])
        || $this->attributes['max'] >= $value
      );
  }
}
