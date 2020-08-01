<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Fieldset\Format;

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Fieldset\Format\Pack\Lowercase as FormatStub;

/**
 * Validator layer test
 *
 * @vendor   Cradle
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Fieldset_FormatHandlerTest extends TestCase
{

  /**
   * @covers Cradle\Package\System\Fieldset\Format\FormatHandler::register
   * @covers Cradle\Package\System\Fieldset\Format\FormatHandler::getFormatter
   */
  public function testRegister()
  {
    FormatHandler::register(new FormatStub);
    $actual = FormatHandler::getFormatter('lower');
    $this->assertInstanceOf(FormatStub::class, $actual);

    $actual = FormatHandler::getFormatter('foo');
    $this->assertNull($actual);
  }
}
