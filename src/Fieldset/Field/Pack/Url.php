<?php //-->

namespace Cradle\Package\System\Fieldset\Field\Pack;

class Url extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'url';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'URL Field';

  /**
   * @const string TYPE HTML input field type
   */
  const TYPE = 'url';

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool
  {
    return preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0'.
    '-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $value);
  }
}
