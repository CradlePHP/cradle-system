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
class Cradle_Package_System_Fieldset_AbstractFieldTest extends TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    //now we can instantiate the object
    $this->object = new FieldStub();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::getConfigFormats
   */
  public function testConfigFormats()
  {
    $actual = $this->object->getConfigFormats();
    $this->assertEquals('string', $actual[0]);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::getConfigLabel
   */
  public function testConfigLabel()
  {
    $actual = $this->object->getConfigLabel();
    $this->assertEquals('Text Field', $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::getConfigName
   */
  public function testConfigName()
  {
    $actual = $this->object->getConfigName();
    $this->assertEquals('text', $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::getConfigParameters
   */
  public function testConfigParameters()
  {
    $actual = $this->object->getConfigParameters();
    $this->assertEmpty($actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::getConfigTypes
   */
  public function testConfigTypes()
  {
    $actual = $this->object->getConfigTypes();
    $this->assertEquals('string', $actual[0]);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::hasAttributes
   */
  public function testHasAttributes()
  {
    $actual = $this->object->hasAttributes();
    $this->assertTrue($actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::hasOptions
   */
  public function testHasOptions()
  {
    $actual = $this->object->hasOptions();
    $this->assertFalse($actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::isFilterable
   */
  public function testIsFilterable()
  {
    $actual = $this->object->isFilterable();
    $this->assertTrue($actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::isSearchable
   */
  public function testIsSearchable()
  {
    $actual = $this->object->isSearchable();
    $this->assertTrue($actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::isSortable
   */
  public function testIsSortable()
  {
    $actual = $this->object->isSortable();
    $this->assertTrue($actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::prepare
   */
  public function testPrepare()
  {
    $actual = $this->object->prepare('foo');
    $this->assertEquals('foo', $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::setName
   */
  public function testSetName()
  {
    $actual = $this->object->setName('foo');
    $this->assertInstanceOf(FieldStub::class, $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::setAttributes
   */
  public function testSetAttribues()
  {
    $actual = $this->object->setAttributes([
      'placeholder' => 'foo bar'
    ]);
    $this->assertInstanceOf(FieldStub::class, $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::setOptions
   */
  public function testSetOptions()
  {
    $actual = $this->object->setOptions([]);
    $this->assertInstanceOf(FieldStub::class, $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::setParameters
   */
  public function testSetParameters()
  {
    $actual = $this->object->setParameters([]);
    $this->assertInstanceOf(FieldStub::class, $actual);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Field\AbstractField::valid
   */
  public function testValid()
  {
    $actual = $this->object->valid('foo');
    $this->assertTrue($actual);
  }
}
