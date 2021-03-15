<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Format\Json;

use Cradle\Package\System\Format\AbstractFormatter;
use Cradle\Package\System\Format\FormatterInterface;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Comma Separated Format
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class CommaSeparated extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'comma';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Comma Separated';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_JSON;

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
    return implode(', ', $value);
  }
}
