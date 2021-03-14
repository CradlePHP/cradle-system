<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field;

/**
 * Abstractly defines a field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
abstract class AbstractField
{
  /**
   * @const mixed DEFAULT_VALUE
   */
  const DEFAULT_VALUE = null;

  /**
   * @const int FORCE_FILTERABLE Whether or not to force the field to filterable
   *                             1 means force check,
   *                             0 means force uncheck,
   *                             -1 means do not force
   */
  const FORCE_FILTERABLE = -1;

  /**
   * @const ?string FORCE_LABEL the label here will be used as
   *                            the label of the field.
   */
  const FORCE_LABEL = null;

  /**
   * @const ?string FORCE_NAME the name here will be used as the name of
   *                           the field. Dont confuse this with the type name.
   */
  const FORCE_NAME = null;

  /**
   * @const int FORCE_SEARCHABLE Whether or not to force the field to searchable
   *                             1 means force check,
   *                             0 means force uncheck,
   *                             -1 means do not force
   */
  const FORCE_SEARCHABLE = -1;

  /**
   * @const int FORCE_SORTABLE Whether or not to force the field to sortable
   *                             1 means force check,
   *                             0 means force uncheck,
   *                             -1 means do not force
   */
  const FORCE_SORTABLE = -1;

  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = false;

  /**
   * @const bool HAS_OPTIONS Whether or not to show options fieldset
   * on the schema form if the field was chosen
   */
  const HAS_OPTIONS = false;

  /**
   * @const bool IS_FILTERABLE Whether or not to enable the filterable checkbox
   * on the schema form if the field was chosen
   */
  const IS_FILTERABLE = false;

  /**
   * @const bool IS_SEARCHABLE Whether or not to enable the searchable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SEARCHABLE = false;

  /**
   * @const bool IS_SORTABLE Whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SORTABLE = false;

  /**
   * @const string NAME Config name
   */
  const NAME = 'unknown';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Unknown';

  /**
   * @const bool NO_VALIDATION Whether or not to show the validation fieldset
   * on the schema form if the field was chosen
   */
  const NO_VALIDATION = false;

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_STRING;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [];

  /**
   * @var ?string $name The applied field name
   */
  protected $name = null;

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = [];

  /**
   * @var array $options Hash of options to consider when rendering
   */
  protected $options = [];

  /**
   * @var array $parameters List of parametrs to consider when rendering
   */
  protected $parameters = [];

  /**
   * When they choose this field in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [];
  }

  /**
   * Returns the name of the field
   *
   * @return ?string
   */
  public function getName(): ?string
  {
    return $this->name;
  }

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value)
  {
    return $value;
  }

  /**
   * Renders the field for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  abstract public function render($value = null): ?string;

  /**
   * Sets the attributes that will be
   * considered when rendering the template
   *
   * @param *array $attributes
   *
   * @return FieldInterface
   */
  public function setAttributes(array $attributes): FieldInterface
  {
    $this->attributes = $attributes;
    return $this;
  }

  /**
   * Sets the name of the field
   *
   * @param *string $name
   *
   * @return FieldInterface
   */
  public function setName(string $name): FieldInterface
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Sets the options that will be
   * considered when rendering the template
   *
   * @param *array $options
   *
   * @return FieldInterface
   */
  public function setOptions(array $options): FieldInterface
  {
    $this->options = $options;
    return $this;
  }

  /**
   * Sets the parameters that will be
   * considered when rendering the template
   *
   * @param *array $parameters
   *
   * @return FieldInterface
   */
  public function setParameters(array $parameters): FieldInterface
  {
    $this->parameters = $parameters;
    return $this;
  }

  /**
   * Returns the name of the field
   *
   * @param *mixed $value
   *
   * @return FieldInterface
   */
  public function setValue($value): FieldInterface
  {
    $this->value = $value;
    return $this;
  }

  /**
   * Converts instance to an array
   *
   * @return array
   */
  public static function toConfigArray(): array
  {
    $data = [
      'name' => static::NAME,
      'type' => static::TYPE,
      'label' => static::LABEL,
      'default' => static::DEFAULT_VALUE,
      'formats' => static::FORMATS,
      'validation' => !static::NO_VALIDATION,
      'force' => [
        'name' => static::FORCE_NAME,
        'label' => static::FORCE_LABEL,
        'filterable' => static::FORCE_FILTERABLE,
        'searchable' => static::FORCE_SEARCHABLE,
        'sortable' => static::FORCE_SORTABLE
      ]
    ];

    //indexes
    $data['indexes'] = [];
    if (static::IS_FILTERABLE) {
      $data['indexes'][] = 'filterable';
    }

    if (static::IS_SEARCHABLE) {
      $data['indexes'][] = 'searchable';
    }

    if (static::IS_SORTABLE) {
      $data['indexes'][] = 'sortable';
    }

    //fieldset
    $data['fieldsets'] = [];
    if (static::HAS_ATTRIBUTES) {
      $data['fieldsets'][] = 'attributes';
    }

    if (static::HAS_OPTIONS) {
      $data['fieldsets'][] = 'options';
    }

    return $data;
  }

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool
  {
    return true;
  }
}
