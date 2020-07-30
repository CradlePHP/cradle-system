<?php //-->

namespace Cradle\Package\System\Fieldset\Format\Pack;

use Cradle\Package\System\Fieldset\Format\FormatTypes;
use Cradle\Package\System\Fieldset\Format\AbstractFormatter;
use Cradle\Package\System\Fieldset\Format\FormatterInterface;

use Cradle\Package\System\Fieldset\Field\FieldHandler;

class Date extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'date';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Date Format';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_DATE;

  /**
   * When they choose this format in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public function getConfigParameters(): array
  {
    return [
      FieldHandler::getField('text')->setAttributes([
        'placeholder' => 'eg. Y-m-d'
      ])
    ];
  }

  /**
   * Renders the output format for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function format($value = null): ?string
  {
    $parameters = $this->parameters;
    if (!isset($parameters[0])) {
      $parameters[0] = 'Y-m-d H:i:s';
    }

    return date($parameters[0], strtotime($value));
  }
}
