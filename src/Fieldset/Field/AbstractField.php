<?php //-->

namespace Cradle\Package\System\Fieldset\Field;

abstract class AbstractField
{
  /**
   * @const bool FORCE_FILTERABLE Whether or not to force the field to filterable
   */
  const FORCE_FILTERABLE = false;

  /**
   * @const bool FORCE_SEARCHABLE Whether or not to force the field to searchable
   */
  const FORCE_SEARCHABLE = false;

  /**
   * @const bool FORCE_SORTABLE Whether or not to force the field to sortable
   */
  const FORCE_SORTABLE = false;

  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = false;

  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show options fieldset
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
   * @const array TYPES List of possible data types
   */
  const TYPES = [];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [];

  /**
   * @var *string $name The applied field name
   */
  protected $name;

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
   * Returns recommended output formats
   *
   * @return array
   */
  public function getConfigFormats(): array
  {
    return static::FORMATS;
  }

  /**
   * In the schema form we need to provide a label for this field
   *
   * @return string
   */
  public function getConfigLabel(): string
  {
    return static::LABEL;
  }

  /**
   * In the schema form we need to provide a unique slug name
   *
   * @return string
   */
  public function getConfigName(): string
  {
    return static::NAME;
  }

  /**
   * When they choose this field in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public function getConfigParameters(): array
  {
    return [];
  }

  /**
   * Returns possible data types so schema
   * based stores know how to interpret this field
   *
   * @return array
   */
  public function getConfigTypes(): array
  {
    $types = static::TYPES;
    $types[] = static::NAME;
    return $types;
  }

  /**
   * Returns whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function hasAttributes(): bool
  {
    return static::HAS_ATTRIBUTES;
  }

  /**
   * Returns whether or not to show options fieldset
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function hasOptions(): bool
  {
    return static::HAS_OPTIONS;
  }

  /**
   * Returns whether or not to enable the filterable checkbox
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function isFilterable(): bool
  {
    return static::IS_FILTERABLE;
  }

  /**
   * Returns whether or not to enable the searchable checkbox
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function isSearchable(): bool
  {
    return static::IS_SEARCHABLE;
  }

  /**
   * Returns whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function isSortable(): bool
  {
    return static::IS_SORTABLE;
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
   * @return string
   */
  abstract public function render($value = null): ?string;

  /**
   * Returns the name of the field
   *
   * @param *string $name
   *
   * @return FieldConfigInterface
   */
  public function setName(string $name): FieldInterface
  {
    $this->name = $name;
    return $this;
  }

  /**
   * Sets the attributes that will be
   * considered when rendering the template
   *
   * @param *array $attributes
   *
   * @return FieldConfigInterface
   */
  public function setAttributes(array $attributes): FieldInterface
  {
    $this->attributes = $attributes;
    return $this;
  }

  /**
   * Sets the options that will be
   * considered when rendering the template
   *
   * @param *array $options
   *
   * @return FieldConfigInterface
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
   * @return FieldConfigInterface
   */
  public function setParameters(array $parameters): FieldInterface
  {
    $this->parameters = $parameters;
    return $this;
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
