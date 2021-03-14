<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Option;

use Cradle\Handlebars\HandlebarsHandler;

use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Radio Fieldset
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Radio extends Select
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'radio';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Radio Fieldset';

  /**
   * Renders the field for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function render($value = null): ?string
  {
    $handlebars = HandlebarsHandler::i();
    $template = $handlebars->compile(file_get_contents(__DIR__ . '/template/radio.html'));
    return $template([
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ]);
  }
}
