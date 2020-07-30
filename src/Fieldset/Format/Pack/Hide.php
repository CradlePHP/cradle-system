<?php //-->

namespace Cradle\Package\System\Fieldset\Format\Pack;

use Cradle\Package\System\Fieldset\Format\FormatTypes;
use Cradle\Package\System\Fieldset\Format\AbstractFormatter;
use Cradle\Package\System\Fieldset\Format\FormatterInterface;

class Hide extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'hide';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Hide';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_GENERAL;

  /**
   * Renders the output format for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function format($value = null): ?string
  {
    return null;
  }
}
