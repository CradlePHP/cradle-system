<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Module\System\Object\Service;

use Cradle\Module\System\Schema as SystemSchema;

/**
 * SQL service test
 * Role Model Test
 *
 * @vendor   Acme
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_System_Object_Service_SqlServiceTest extends TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\System\Object\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');

        $this->object->setSchema(SystemSchema::i('sample'));
    }

    /**
     * @covers Cradle\Module\System\Object\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'sample_name' => 'Testdd',
            'sample_active' => 1,
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['sample_id']);
    }

    /**
     * @covers Cradle\Module\System\Object\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get('sample_id', 1);

        $this->assertEquals(1, $actual['sample_id']);
    }

    /**
     * @covers Cradle\Module\System\Object\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['sample_id']);
    }

    /**
     * @covers Cradle\Module\System\Object\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'sample_id' => $id,
            'sample_name' => 'Edited Name',
        ]);

        $this->assertEquals($id, $actual['sample_id']);
    }

    /**
     * @covers Cradle\Module\System\Object\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['sample_id']);
    }
}
