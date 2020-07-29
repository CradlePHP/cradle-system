<?php //-->

namespace Cradle\Package\System\Fieldset\Validation;

class ValidationHandler
{
  /**
   * @var array $validators
   */
  protected static $validators = [];

  /**
   * Registers a validator
   *
   * @param *ValidatorInterface $validator
   */
  public static function register(ValidatorInterface $validator)
  {
    $name = $validator->getConfigName();
    self::$validators[$name] = $validator;
  }

  /**
   * Returns a validator
   *
   * @param *string $name
   *
   * @return ?ValidatorInterface
   */
  public static function getValidator(string $name): ?ValidatorInterface
  {
    if (isset(self::$validators[$name])) {
      return self::$validators[$name];
    }

    return null;
  }
}
