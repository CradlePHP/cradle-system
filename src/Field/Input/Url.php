<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Input;

use Cradle\Package\System\Format\FormatTypes;

/**
 * URL Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Url extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'url';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'URL Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'url';

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_STRING,
    FormatTypes::TYPE_DATE,
    FormatTypes::TYPE_HTML,
    FormatTypes::TYPE_CUSTOM
  ];

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool
  {
    return preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0'.
    '-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $value);
  }
}
