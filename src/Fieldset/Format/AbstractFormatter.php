<?php //-->

namespace Cradle\Package\System\Fieldset\Format;

use Cradle\Package\System\Fieldset\Format\FormatTypes;

abstract class AbstractFormatter
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
  const TYPE = FormatTypes::TYPE_GENERAL;

  /**
   * @var array $parameters List of parametrs to consider when formatting
   */
  protected $parameters = [];

  /**
   * In the schema form we need to provide a label for this format
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
   * When they choose this format in a schema form,
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
   * Renders the output format for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  abstract public function format($value = null): ?string;

  /**
   * Sets the parameters that will be
   * considered when rendering the template
   *
   * @param *array $parameters
   *
   * @return FormatterInterface
   */
  public function setParameters(array $parameters): FormatterInterface
  {
    $this->parameters = $parameters;
    return $this;
  }
}
