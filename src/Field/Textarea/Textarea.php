<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Textarea;

use Cradle\Package\System\Field\AbstractField;
use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Textarea Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Textarea extends AbstractField implements FieldInterface
{
  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = true;

  /**
   * @const bool IS_SEARCHABLE Whether or not to enable the searchable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SEARCHABLE = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'textarea';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Textarea Field';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_TEXT,
    FormatTypes::TYPE_HTML
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
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value = null)
  {
    if (!$value) {
      return $value;
    }

    //fix for textarea in textarea
    return str_replace('<\/textarea>', '</textarea>', $value);
  }

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
      file_get_contents(__DIR__ . '/template/textarea.html')
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
