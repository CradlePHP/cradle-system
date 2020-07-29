<?php //-->

namespace Cradle\Package\System\Fieldset\Format\Pack;

use Cradle\Package\System\Fieldset\Format\FormatTypes;
use Cradle\Package\System\Fieldset\Format\AbstractFormatter;
use Cradle\Package\System\Fieldset\Format\FormatterInterface;

class YesNo extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'yesno';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Yes/No';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_NUMBER;

  /**
   * Renders the output format for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function format($value = null): ?string
  {
    return $value ? 'yes': 'no';
  }
}
