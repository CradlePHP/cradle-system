<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Fieldset\Field;

use Cradle\Resolver\ResolverTrait;

class FieldHandler
{
  /**
   * @var array $fields
   */
  protected static $fields = [];

  /**
   * Returns a field
   *
   * @param *string $name
   *
   * @return ?string
   */
  public static function getField(string $name): ?string
  {
    if (isset(self::$fields[$name])) {
      return self::$fields[$name];
    }

    return null;
  }

  /**
   * Returns all fields
   *
   * @return array
   */
  public static function getFields(): array
  {
    return self::$fields;
  }

  /**
   * Returns a field instance
   *
   * @param *string $name
   *
   * @return ?FieldInterface
   */
  public static function makeField(string $name): ?FieldInterface
  {
    if (isset(self::$fields[$name])) {
      return cradle()->resolve(self::$fields[$name]);
    }

    return null;
  }

  /**
   * Registers a field
   *
   * @param *string $field
   *
   * @return bool true if was registered successfully
   */
  public static function register(string $field)
  {
    if (!is_subclass_of($field, FieldInterface::class) || !$field::NAME) {
      return false;
    }

    self::$fields[$field::NAME] = $field;
    return true;
  }
}
