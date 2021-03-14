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
 * Rating Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Rating extends Floating
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'rating';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Rating Field';

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
    $data['attributes']['min'] = 0;
    $data['attributes']['step'] = 0.5;

    $handlebars = HandlebarsHandler::i();
    $template = $handlebars->compile(file_get_contents(__DIR__ . '/template/rating.html'));
    return $template($data);
  }
}
