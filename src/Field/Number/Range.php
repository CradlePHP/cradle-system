<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Number;

use Cradle\Handlebars\HandlebarsHandler;

/**
 * Range Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Range extends Number
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'range';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Range Field';

  /**
   * Renders the field for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function render($value = null): ?string
  {
    $data = [
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ];

    $data['attributes']['type'] = static::INPUT_TYPE;
    $data['attributes']['data-do'] = 'multirange-field';
    $data['attributes']['tabindex'] = -1;

    $handlebars = HandlebarsHandler::i();
    $template = $handlebars->compile(file_get_contents(__DIR__ . '/template/range.html'));
    return $template($data);
  }
}
