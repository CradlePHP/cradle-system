<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Json;

use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Field\FieldRegistry;

/**
 * Table Fieldset
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Table extends Json
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'table';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Table Fieldset';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_TABLE,
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
      file_get_contents(__DIR__ . '/template/table.html')
    );
    return $template([
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ]);
  }

  /**
   * When they choose this format in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldRegistry::makeField('textlist')
        ->setName('{NAME}[options]')
        ->setAttributes([
          'data-label-add' => 'Add Column'
        ])
    ];
  }
}
