<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation\Number;

use Cradle\Package\System\Field\FieldRegistry;

/**
 * Number Less Than Equal Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class LessThanEqual extends NotEqual
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'lte';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Less Than Equal';

  /**
   * When they choose this validator in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldRegistry::makeField('number')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'type' => 'text',
          'placeholder' => 'Enter Number'
        ])
    ];
  }

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed $value
   *
   * @return bool
   */
  public function valid($value = null): bool
  {
    return isset($this->parameters[0]) && $value <= $this->parameters[0];
  }
}
