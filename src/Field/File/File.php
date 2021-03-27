<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\File;

use Cradle\Package\System\Field\AbstractField;
use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

/**
 * File Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class File extends AbstractField implements FieldInterface
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
  const NAME = 'file';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'File Field';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_FILE;

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
    FormatTypes::TYPE_HTML,
    FormatTypes::TYPE_CUSTOM
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
    $file = cradle('event')->method('file-upload', [ 'data' => $value ]);

    if (!isset($file['data'])) {
      return $value;
    }

    return $file['data'];
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
      file_get_contents(__DIR__ . '/template/file.html')
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
