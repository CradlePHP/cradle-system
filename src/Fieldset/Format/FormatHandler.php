<?php //-->

namespace Cradle\Package\System\Fieldset\Format;

class FormatHandler
{
  /**
   * @var array $formatters
   */
  protected static $formatters = [];

  /**
   * Registers a format
   *
   * @param *FormatterInterface $field
   */
  public static function register(FormatterInterface $formatter)
  {
    $name = $formatter->getConfigName();
    self::$formatters[$name] = $formatter;
  }

  /**
   * Returns a format
   *
   * @param *string $name
   *
   * @return ?FormatterInterface
   */
  public static function getFormat(string $name): ?FormatterInterface
  {
    if (isset(self::$formatters[$name])) {
      return self::$formatters[$name];
    }

    return null;
  }
}
