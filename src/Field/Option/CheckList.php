<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Option;

use Cradle\Package\System\Field\AbstractField;
use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * Check List Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class CheckList extends AbstractField implements FieldInterface
{
  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = true;

  /**
   * @const bool HAS_OPTIONS Whether or not to show options fieldset
   * on the schema form if the field was chosen
   */
  const HAS_OPTIONS = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'checklist';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Check List Fieldset';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_OPTION;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_STRING_LIST,
    FieldTypes::TYPE_JSON,
    FieldTypes::TYPE_OBJECT
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_JSON
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
    return json_encode($value);
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
      file_get_contents(__DIR__ . '/template/checklist.html')
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
