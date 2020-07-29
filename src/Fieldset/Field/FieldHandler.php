<?php //-->

namespace Cradle\Package\System\Fieldset\Field;

class FieldHandler
{
  /**
   * @var array $fields
   */
  protected static $fields = [];

  /**
   * Registers a field
   *
   * @param *FieldInterface $field
   */
  public static function register(FieldInterface $field)
  {
    $name = $field->getConfigName();
    self::$fields[$name] = $field;
  }

  /**
   * Returns a field
   *
   * @param *string $name
   *
   * @return ?FieldInterface
   */
  public static function getField(string $name): ?FieldInterface
  {
    if (isset(self::$fields[$name])) {
      return self::$fields[$name];
    }

    return null;
  }
}
