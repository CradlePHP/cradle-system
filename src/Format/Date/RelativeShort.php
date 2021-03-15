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
 * Relative Short Format
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class RelativeShort extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'relativeshort';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Short Relative Format';

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
    $relative = $timezone->toRelative(time() - $offset);

    if (isset($this->parameters[0]) && $this->parameters[0]) {
      $relative = $timezone->toRelative(time() - $offset, 7, $this->parameters[0]);
    } else {
      $relative = $timezone->toRelative(time() - $offset);
    }

    $relative = strtolower($relative);

    $short = str_replace(['ago', 'from now'], '', $relative);
    $short = str_replace(['seconds', 'second'], 'sec', $short);
    $short = str_replace(['minutes', 'minute'], 'min', $short);
    $short = str_replace('hours', 'hrs', $short);
    $short = str_replace('hour', 'hr', $short);
    $short = str_replace(['days', 'day'], 'd', $short);
    $short = str_replace('weeks', 'wks', $short);
    $short = str_replace('week', 'wk', $short);
    $short = str_replace(['months', 'month'], 'mon', $short);
    $short = str_replace('years', 'yrs', $short);
    $short = str_replace('year', 'yr', $short);
    $short = str_replace(['yesterday', 'tomorrow'], '1d', $short);
    $short = str_replace(' ', '', $short);

    if (strpos($relative, 'from now') !== false) {
      $short = '-' . $short;
    }

    return $short;
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
