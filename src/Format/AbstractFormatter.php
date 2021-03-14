<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Format;

use Cradle\Package\System\Format\FormatTypes;

/**
 * Abstractly defines a formatter
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
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
   * Renders the output format for model forms
   *
   * @param ?mixed $value
   *
   * @return ?string
   */
  abstract public function format($value = null): ?string;

  /**
   * When they choose this format in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [];
  }

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

  /**
   * Converts instance to an array
   *
   * @return array
   */
  public static function toConfigArray(): array
  {
    return [
      'name' => static::NAME,
      'type' => static::TYPE,
      'label' => static::LABEL
    ];
  }
}
