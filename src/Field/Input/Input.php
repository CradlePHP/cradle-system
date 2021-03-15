<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Input;

use Cradle\Package\System\Field\AbstractField;
use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Input Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Input extends AbstractField implements FieldInterface
{
  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = true;

  /**
   * @const ?string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = null;

  /**
   * @const bool IS_FILTERABLE Whether or not to enable the filterable checkbox
   * on the schema form if the field was chosen
   */
  const IS_FILTERABLE = true;

  /**
   * @const bool IS_SEARCHABLE Whether or not to enable the searchable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SEARCHABLE = true;

  /**
   * @const bool IS_SORTABLE Whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SORTABLE = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'input';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Input Field';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_STRING
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_STRING,
    FormatTypes::TYPE_NUMBER,
    FormatTypes::TYPE_DATE,
    FormatTypes::TYPE_HTML,
    FormatTypes::TYPE_CUSTOM
  ];

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

    if (static::INPUT_TYPE) {
      $data['attributes']['type'] = static::INPUT_TYPE;
    }

    $template = cradle('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/input.html')
    );
    return $template($data);
  }
}
