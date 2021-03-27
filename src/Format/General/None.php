<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Format\General;

use Cradle\Package\System\Format\AbstractFormatter;
use Cradle\Package\System\Format\FormatterInterface;
use Cradle\Package\System\Format\FormatTypes;

/**
 * No Custom Format
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class None extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'none';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'No Filter';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_GENERAL;

  /**
   * Renders the output format for model forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field formatting
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function format(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string
  {
    return $value;
  }
}
