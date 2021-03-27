<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Field\Custom;

/**
 * Updated Field
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Updated extends Created
{
  /**
   * @const ?string FORCE_LABEL the label here will be used as
   *                            the label of the field.
   */
  const FORCE_LABEL = 'Updated';

  /**
   * @const ?string FORCE_NAME the name here will be used as the name of
   *                           the field. Dont confuse this with the type name.
   */
  const FORCE_NAME = 'updated';

  /**
   * @const string NAME Config name
   */
  const NAME = 'updated';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Updated';

  /**
   * Prepares the value for some sort of insertion
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?scalar
   */
  public function prepare($value = null, string $name = null, array $row = [])
  {
    if (strtotime($value) !== false) {
      return date('Y-m-d H:i:s', strtotime($value));
    }

    return date('Y-m-d H:i:s');
  }
}
