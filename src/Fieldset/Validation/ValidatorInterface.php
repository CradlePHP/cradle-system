<?php //-->

namespace Cradle\Package\System\Fieldset\Validation;

interface ValidatorInterface
{
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
   * Returns data type
   *
   * @return string
   */
  public function getConfigType(): string;

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   * @param ?array $parameters
   *
   * @return bool
   */
  public function valid($value = null): bool;
}
