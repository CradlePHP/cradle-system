<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Format\String;

use Cradle\Package\System\Field\FieldRegistry;

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
class WordLength extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'wordlength';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Word Length';

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
    $length = $this->parameters[0];
    if (str_word_count($value, 0) > $length) {
      $words = str_word_count($value, 2);
      $position = array_keys($words);
      $value = substr($value, 0, $position[$length]);
    }

    return $value;
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
          'placeholder' => 'Enter maximum number of words',
          'required' => 'required'
        ])
    ];
  }
}
