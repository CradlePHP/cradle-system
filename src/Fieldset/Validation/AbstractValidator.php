<?php //-->

namespace Cradle\Package\System\Fieldset\Validation;

use Cradle\Package\System\Fieldset\Validation\ValidationTypes;

abstract class AbstractValidator
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'unknown';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Unknown';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_GENERAL;

  /**
   * @var array $parameters List of parametrs to consider when validating
   */
  protected $parameters = [];

  /**
   * In the schema form we need to provide a label for this validator
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
   * When they choose this validator in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public function getConfigParameters(): array
  {
    return [];
  }

  /**
   * Returns data type
   *
   * @return string
   */
  public function getConfigType(): string
  {
    return static::TYPE;
  }

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   *
   * @return bool
   */
  abstract public function valid($value = null): bool;

  /**
   * Sets the parameters that will be
   * considered when rendering the template
   *
   * @param *array $parameters
   *
   * @return ValidatorInterface
   */
  public function setParameters(array $parameters): ValidatorInterface
  {
    $this->parameters = $parameters;
    return $this;
  }
}
