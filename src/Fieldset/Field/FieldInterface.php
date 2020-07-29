<?php //-->

namespace Cradle\Package\System\Fieldset\Field;

interface FieldInterface
{
  /**
   * Returns recommended output formats
   *
   * @return array
   */
  public function getConfigFormats(): array;

  /**
   * In the schema form we need to provide a label for this field
   *
   * @return string
   */
  public function getConfigLabel(): string;

  /**
   * In the schema form we need to provide a unique slug name
   *
   * @return string
   */
  public function getConfigName(): string;

  /**
   * When they choose this field in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public function getConfigParameters(): array;

  /**
   * Returns possible data types so schema
   * based stores know how to interpret this field
   *
   * @return array
   */
  public function getConfigTypes(): array;

  /**
   * Returns whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function hasAttributes(): bool;

  /**
   * Returns whether or not to show options fieldset
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function hasOptions(): bool;

  /**
   * Returns whether or not to enable the filterable checkbox
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function isFilterable(): bool;

  /**
   * Returns whether or not to enable the searchable checkbox
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function isSearchable(): bool;

  /**
   * Returns whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   *
   * @return bool
   */
  public function isSortable(): bool;

  /**
   * Prepares the value for some sort of insertion
   *
   * @param *mixed $value
   *
   * @return ?scalar
   */
  public function prepare($value);

  /**
   * Renders the field for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function render($value = null): ?string;

  /**
   * Validation check
   *
   * @param *mixed $value
   *
   * @return bool
   */
  public function valid($value): bool;
}
