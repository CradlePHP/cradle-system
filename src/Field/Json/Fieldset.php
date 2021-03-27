<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Json;

use Cradle\Package\System\Field\AbstractField;
use Cradle\Package\System\Field\FieldInterface;
use Cradle\Package\System\Field\FieldRegistry;
use Cradle\Package\System\Field\FieldTypes;
use Cradle\Package\System\Format\FormatTypes;

use Cradle\Package\System\Fieldset as SystemFieldset;

/**
 * Custom Fieldset
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Fieldset extends AbstractField implements FieldInterface
{
  /**
   * @const bool IS_SEARCHABLE Whether or not to enable the searchable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SEARCHABLE = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'fieldset';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Custom Fieldset';

  /**
   * @const bool NO_VALIDATION Whether or not to show the validation fieldset
   * on the schema form if the field was chosen
   */
  const NO_VALIDATION = true;

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_JSON;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_JSON,
    FieldTypes::TYPE_OBJECT
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_JSON,
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
    if ($this->parameters[1] === 'hash[]') {
      return $this->renderMultiple($value, $name, $row);
    }

    return $this->renderSingle($value, $name, $row);
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
      FieldRegistry::makeField('text')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'required' => 'required',
          'placeholder' => 'Name of fieldset eg. address'
        ]),
      FieldRegistry::makeField('select')
        ->setName('{NAME}[parameters][1]')
        ->setAttributes([
          'required' => 'required'
        ])
        ->setOptions([
          '' => 'Multiple fieldsets ?',
          'hash[]' => 'Yes',
          'hash' => 'No'
        ])
    ];
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
  public function renderSingle(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string
  {
    //NOTE:
    // $name is like address_street_1
    // $this->name is like profile_address[address_street_1]

    //load the fieldset
    $fieldset = SystemFieldset::load($this->parameters[0]);

    $config = $fieldset->get();
    //make fields to keyval
    $config['fields'] = $fieldset->getFields();
    //add fieldname
    foreach ($config['fields'] as $key => $field) {
      $config['fields'][$key]['fieldname'] = sprintf('%s[%s]', $this->name, $key);
    }

    $template = cradle('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/fieldset_single.html')
    );

    if (!is_array($value)) {
      $value = [];
    }

    //this is the name template
    $config['nameplate'] = $this->name;

    return $template([
      'fieldset' => $config,
      'value' => $value,
      'empty' => [],
      'parameters' => $this->parameters,
      'attributes' => $this->attributes,
    ]);
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
  public function renderMultiple(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string
  {
    //NOTE:
    // $name is like address_street_1
    // $this->name is like profile_address[0][address_street_1]
    $index = sprintf('{INDEX_%s}', substr_count($this->name, '{INDEX_'));
    $nameplate = sprintf('%s[%s]', $this->name, $index);

    //load the fieldset
    $fieldset = SystemFieldset::load($this->parameters[0]);

    $config = $fieldset->get();
    //make fields to keyval
    $config['fields'] = $fieldset->getFields();
    //add fieldname
    foreach ($config['fields'] as $key => $field) {
      $config['fields'][$key]['fieldname'] = sprintf('%s[%s]', $this->name, $key);
      $config['fields'][$key]['nameplate'] = sprintf('%s[%s]', $nameplate, $key);
    }

    $template = cradle('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/fieldset_multiple.html')
    );

    if (!is_array($value)) {
      $value = [];
    }

    $rows = [];
    foreach ($value as $i => $item) {
      $rows[$i]['item'] = $item;
      $rows[$i]['fieldname'] = sprintf('%s[%s]', $this->name, $i);
      foreach ($config['fields'] as $key => $field) {
        $rows[$i]['fields'][$key]['fieldname'] = sprintf('%s[%s][%s]', $this->name, $i, $key);
        $rows[$i]['fields'][$key]['nameplate'] = sprintf('%s[%s]', $nameplate, $key);
        if (isset($item[$key])) {
          $rows[$i]['fields'][$key]['value'] = $item[$key];
        }
      }
    }

    //this is the name template
    $config['nameplate'] = $nameplate;

    return $template([
      'fieldset' => $config,
      'value' => $value,
      'rows' => $rows,
      'empty' => [],
      'parameters' => $this->parameters,
      'attributes' => $this->attributes,
    ]);
  }
}
