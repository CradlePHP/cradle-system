<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Object\Validator;

use Cradle\Package\System\Schema;

/**
 * Validator layer test
 *
 * @vendor   Cradle
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Object_ValidatorTest extends TestCase
{
    /**
     * @covers Cradle\Package\System\Object\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $schema = Schema::i('sample');

        $actual = $schema
            ->object()
            ->validator()
            ->getCreateErrors([]);

        $this->assertEquals('Name is required', $actual['sample_name']);
    }

    /**
     * @covers Cradle\Package\System\Object\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $schema = Schema::i('sample');

        $actual = $schema
            ->object()
            ->validator()
            ->getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['sample_id']);
    }
}
