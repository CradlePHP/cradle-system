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
class Cradle_Package_System_Fieldset_AbstractFormatterTest extends TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    //now we can instantiate the object
    $this->object = new FormatStub();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Format\AbstractFormatter::getConfigLabel
   */
  public function testConfigLabel()
  {
    $actual = $this->object->getConfigLabel();
    $this->assertEquals('Lower Case', $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Format\AbstractFormatter::getConfigName
   */
  public function testConfigName()
  {
    $actual = $this->object->getConfigName();
    $this->assertEquals('lower', $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Format\AbstractFormatter::getConfigParameters
   */
  public function testConfigParameters()
  {
    $actual = $this->object->getConfigParameters();
    $this->assertEmpty($actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Format\AbstractFormatter::getConfigType
   */
  public function testConfigType()
  {
    $actual = $this->object->getConfigType();
    $this->assertEquals('string', $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::setParameters
   */
  public function testSetParameters()
  {
    $actual = $this->object->setParameters([]);
    $this->assertInstanceOf(FormatStub::class, $actual);
  }
}
