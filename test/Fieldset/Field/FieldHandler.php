<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Fieldset\Field;

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Fieldset\Field\Pack\Text as FieldStub;

/**
 * Validator layer test
 *
 * @vendor   Cradle
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Fieldset_FieldHandlerTest extends TestCase
{

  /**
   * @covers Cradle\Package\System\Fieldset\Field\FieldHandler::register
   * @covers Cradle\Package\System\Fieldset\Field\FieldHandler::getField
   */
  public function testRegister()
  {
    FieldHandler::register(new FieldStub);
    $actual = FieldHandler::getField('text');
    $this->assertInstanceOf(FieldStub::class, $actual);

    $actual = FieldHandler::getField('foo');
    $this->assertNull($actual);
  }
}
