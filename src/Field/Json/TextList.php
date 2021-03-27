<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Json;

use Cradle\Package\System\Field\FieldTypes;

/**
 * Text List Fieldset
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class TextList extends Json
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'textlist';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Text List Fieldset';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_STRING_LIST,
    FieldTypes::TYPE_JSON,
    FieldTypes::TYPE_OBJECT
  ];

  /**
   * Prepares the value for some sort of insertion
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?scalar
   */
  public function prepare($value = null, string $name = null, array $row = [])
  {
    return json_encode($value);
  }

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
      file_get_contents(__DIR__ . '/template/textlist.html')
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
