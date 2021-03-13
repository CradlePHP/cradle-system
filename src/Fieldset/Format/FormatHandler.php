<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Fieldset\Format;

class FormatHandler
{
  /**
   * @var array $formatters
   */
  protected static $formatters = [];

  /**
   * Returns a format
   *
   * @param *string $name
   *
   * @return ?FormatterInterface
   */
  public static function getFormatter(string $name): ?FormatterInterface
  {
    if (isset(self::$formatters[$name])) {
      return self::$formatters[$name];
    }

    return null;
  }

  /**
   * Returns all formatters
   *
   * @return array
   */
  public static function getFormatters(): array
  {
    return self::$formatters;
  }

  /**
   * Returns a format instance
   *
   * @param *string $name
   *
   * @return ?FormatterInterface
   */
  public static function makeFormatter(string $name): ?FormatterInterface
  {
    if (isset(self::$formatters[$name])) {
      return cradle()->resolve(self::$formatters[$name]);
    }

    return null;
  }

  /**
   * Registers a format
   *
   * @param *string $field
   *
   * @return bool true if was registered successfully
   */
  public static function register(string $formatter)
  {
    if (!is_subclass_of($formatter, FormatterInterface::class)
      || !$formatter::NAME
    ) {
      return false;
    }

    self::$formatters[$formatter::NAME] = $formatter;
    return true;
  }
}
