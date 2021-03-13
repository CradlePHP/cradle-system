<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Fieldset\Format;

use Cradle\Package\System\Fieldset\Field\FieldHandler;

class Number extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'number';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Number';

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
      $parameters[0] = null;
    }

    if (!isset($parameters[1])) {
      $parameters[1] = null;
    }

    if (!isset($parameters[2])) {
      $parameters[2] = 0;
    }

    return (string) number_format(
      $value,
      $parameters[2],
      $parameters[1],
      $parameters[0]
    );
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
          'placeholder' => 'Thousands separator eg. ,'
        ]),
      FieldHandler::makeField('input')
        ->setName('{NAME}[parameters][1]')
        ->setAttributes([
          'type' => 'text',
          'placeholder' => 'Decimal separator eg. .'
        ]),
      FieldHandler::makeField('number')
        ->setName('{NAME}[parameters][2]')
        ->setAttributes([
          'type' => 'number',
          'min' => 0,
          'max' => 10,
          'step' => 1,
          'placeholder' => 'Decimal range eg. 2'
        ])
    ];
  }
}
