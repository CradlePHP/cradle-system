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
 * Relative Format
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Relative extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'relative';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Relative Date Format';

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
    $timezone = cradle('tz');
    $offset = $timezone->getOffset();

    if (isset($this->parameters[0]) && $this->parameters[0]) {
      return $timezone->toRelative(time() - $offset, 7, $this->parameters[0]);
    }

    return $timezone->toRelative(time() - $offset);
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
        ->setAttributes([ 'placeholder' => 'eg. F d, Y' ])
    ];
  }
}
