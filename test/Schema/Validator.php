<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Schema\Validator;

/**
 * Validator layer test
 *
 * @vendor   Cradle
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Schema_ValidatorTest extends TestCase
{
  /**
   * @covers Cradle\Package\System\Schema\Validator::getCreateErrors
   */
  public function testGetCreateErrors()
  {
    $actual = Validator::getCreateErrors(['singular' => '']);
    $this->assertEquals('Singular should be longer than 2 characters', $actual['singular']);
    $this->assertEquals('Keyword is required', $actual['name']);
    $this->assertEquals('Fields is required', $actual['fields']);
    $this->assertEquals('Plural is required', $actual['plural']);

    $actual = Validator::getCreateErrors([
      'singular' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
    ]);

    $this->assertEquals('Singular should be less than 255 characters', $actual['singular']);
  }

  /**
   * @covers Cradle\Package\System\Schema\Validator::getUpdateErrors
   */
  public function testGetUpdateErrors()
  {
    $actual = Validator::getUpdateErrors(['name' => '$*@()SD))IJ']);

    $this->assertEquals('Keyword must only have letters, numbers, dashes', $actual['name']);
  }
}
