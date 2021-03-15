<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Validation\General;

use Cradle\Package\System\Validation\AbstractValidator;
use Cradle\Package\System\Validation\ValidatorInterface;
use Cradle\Package\System\Validation\ValidationTypes;

/**
 * Unique Validator
 *
 * @vendor   Cradle
 * @package  System
 * @standard PSR-2
 */
class Unique extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'unique';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Unique';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_GENERAL;

  /**
   * Renders the executes the validation for model forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field validating
   * @param ?array  $row   the row submitted with the value
   *
   * @return bool
   */
  public function valid($value = null, string $name = null, array $row = []): bool
  {
    $search = Service::get('sql')
        ->getResource()
        ->search($table)
        ->addFilter($name . '= %s', $data[$name]);

    if (isset($data[$primary])) {
        $search->addFilter($primary . ' != %s', $data[$primary]);
    }

    if ($search->getTotal()) {
        $errors[$name] = $validation['message'];
    }
  }
}
