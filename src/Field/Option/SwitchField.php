<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Option;

use Cradle\Handlebars\HandlebarsHandler;

use Cradle\Package\System\Format\FormatTypes;

use Cradle\Package\System\Field\FieldTypes;

/**
 * Switch Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class SwitchField extends Checkbox
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'switch';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Switch Field';

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

    $handlebars = HandlebarsHandler::i();
    $template = $handlebars->compile(file_get_contents(__DIR__ . '/template/switch.html'));
    return $template($data);
  }
}
