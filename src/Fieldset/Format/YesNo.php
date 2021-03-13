<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Fieldset\Format;

use Cradle\Package\System\Fieldset\Field\FieldHandler;

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
    $parameters = $this->parameters;
    if (!isset($parameters[0])) {
      $parameters[0] = 'Yes';
    }

    if (!isset($parameters[1])) {
      $parameters[1] = 'No';
    }

    return $value ? $parameters[0]: $parameters[1];
  }

  /**
   * When they choose this format in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldHandler::makeField('input')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'type' => 'text',
          'placeholder' => 'eg. Yes'
        ]),
      FieldHandler::makeField('input')
        ->setName('{NAME}[parameters][1]')
        ->setAttributes([
          'type' => 'text',
          'placeholder' => 'eg. No'
        ])
    ];
  }
}
