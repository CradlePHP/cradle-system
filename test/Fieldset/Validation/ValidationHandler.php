<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Fieldset\Validation;

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Fieldset\Validation\Pack\Required as ValidationStub;

/**
 * Validator layer test
 *
 * @vendor   Cradle
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Fieldset_ValidationHandlerTest extends TestCase
{

  /**
   * @covers Cradle\Package\System\Fieldset\Validation\ValidationHandler::register
   * @covers Cradle\Package\System\Fieldset\Validation\ValidationHandler::getFormatter
   */
  public function testRegister()
  {
    ValidationHandler::register(new ValidationStub);
    $actual = ValidationHandler::getValidator('required');
    $this->assertInstanceOf(ValidationStub::class, $actual);

    $actual = ValidationHandler::getValidator('foo');
    $this->assertNull($actual);
  }
}
