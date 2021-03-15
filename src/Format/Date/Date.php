<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Format\Date;

use Cradle\Package\System\Field\FieldRegistry;

use Cradle\Package\System\Format\AbstractFormatter;
use Cradle\Package\System\Format\FormatterInterface;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Date Format
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
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
   * Renders the output format for model forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field formatting
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function format($value = null, string $name = null, array $row = []): bool
  {
    $parameters = $this->parameters;
    if (!isset($parameters[0])) {
      $parameters[0] = 'Y-m-d H:i:s';
    }

    return date($parameters[0], strtotime($value));
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
      FieldRegistry::makeField('text')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'placeholder' => 'eg. Y-m-d',
          'required' => 'required'
        ])
    ];
  }
}
