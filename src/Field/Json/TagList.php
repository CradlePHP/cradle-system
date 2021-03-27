<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Json;

/**
 * Tag List Fieldset
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class TagList extends TextList
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'taglist';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Tag List Fieldset';

  /**
   * Renders the field for model forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function render(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string
  {
    $template = cradle('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/taglist.html')
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
