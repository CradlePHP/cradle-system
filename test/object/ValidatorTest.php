<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Module\System\Object\Validator;

use Cradle\Module\System\Schema as SystemSchema;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_System_Object_ValidatorTest extends TestCase
{
    /**
     * @covers Cradle\Module\System\Object\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $schema = SystemSchema::i('sample');

        $actual = $schema
            ->model()
            ->validator()
            ->getCreateErrors([]);

        $this->assertEquals('Name is required', $actual['sample_name']);
    }

    /**
     * @covers Cradle\Module\System\Object\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $schema = SystemSchema::i('sample');

        $actual = $schema
            ->model()
            ->validator()
            ->getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['sample_id']);
    }
}
