<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Fieldset\Validator;

/**
 * Validator layer test
 *
 * @vendor   Cradle
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Fieldset_ValidatorTest extends TestCase
{
  /**
   * @covers Cradle\Package\System\Fieldset\Validator::getCreateErrors
   */
  public function testGetCreateErrors()
  {
    $actual = Validator::getCreateErrors(['singular' => '']);
    $this->assertEquals('Singular is required', $actual['singular']);
    $this->assertEquals('Keyword is required', $actual['name']);
    $this->assertEquals('Fields is required', $actual['fields']);
    $this->assertEquals('Plural is required', $actual['plural']);
  }

  /**
   * @covers Cradle\Package\System\Fieldset\Validator::getUpdateErrors
   */
  public function testGetUpdateErrors()
  {
    $actual = Validator::getUpdateErrors(['name' => '$*@()SD))IJ']);

    $this->assertEquals('Keyword must only have letters, numbers, dashes', $actual['name']);
  }
}
