<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Json;

/**
 * Textarea List Fieldset
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class TextareaList extends TextList
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'textarealist';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Textarea List Fieldset';

  /**
   * Renders the field for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function render($value = null): ?string
  {
    $template = cradle('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/textarealist.html')
    );
    return $template([
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ]);
  }
}
