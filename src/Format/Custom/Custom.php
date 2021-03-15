<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Format\Custom;

use Cradle\Package\System\Field\FieldRegistry;

use Cradle\Package\System\Format\AbstractFormatter;
use Cradle\Package\System\Format\FormatterInterface;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Custom Format
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Custom extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'custom';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Custom Format';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_CUSTOM;

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
    $template = cradle('handlebars')->compile($this->parameters[0]);

    return $template([
      'row' => $row,
      'value' => $value
    ]);
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
      FieldRegistry::makeField('textarea')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([ 'placeholder' => 'eg. {{user_first}} {{user_last}}' ])
    ];
  }
}
