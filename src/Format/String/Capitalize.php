<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Format\String;

use Cradle\Package\System\Format\AbstractFormatter;
use Cradle\Package\System\Format\FormatterInterface;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Capitalize Format
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Capitalize extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'capital';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Capitalize';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_STRING;

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
    return ucwords($value);
  }
}
