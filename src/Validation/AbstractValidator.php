<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation;

use Cradle\Package\System\Validation\ValidationTypes;

/**
 * Abstractly defines a validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
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
   * When they choose this validator in a schema form,
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
   * @return ValidatorInterface
   */
  public function setParameters(array $parameters): ValidatorInterface
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

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field validating
   * @param ?array  $row   the row submitted with the value
   *
   * @return bool
   */
  abstract public function valid(
    $value = null,
    string $name = null,
    array $row = []
  ): bool;
}
