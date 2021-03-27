<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Option;

use Cradle\Package\System\Format\FormatTypes;

use Cradle\Package\System\Field\AbstractField;
use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Field\FieldTypes;

/**
 * Checkbox Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Checkbox extends AbstractField implements FieldInterface
{
  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = true;

  /**
   * @const bool IS_FILTERABLE Whether or not to enable the filterable checkbox
   * on the schema form if the field was chosen
   */
  const IS_FILTERABLE = true;

  /**
   * @const bool IS_SORTABLE Whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SORTABLE = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'checkbox';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Checkbox Field';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_OPTION;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_BOOL,
    FieldTypes::TYPE_INT,
    FieldTypes::TYPE_NUMBER,
    FieldTypes::TYPE_OPTION
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_NUMBER
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
    if (is_null($value)) {
      return $value;
    }

    return (int) !!$value;
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
    $data = [
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ];

    $template = cradle('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/checkbox.html')
    );
    return $template($data);
  }
}
