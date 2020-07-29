<?php //-->

namespace Cradle\Package\System\Fieldset\Format;

interface FormatterInterface
{
  /**
   * In the schema form we need to provide a label for this format
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
   * When they choose this format in a schema form,
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
   * Renders the output format for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  public function format($value = null): ?string;
}
