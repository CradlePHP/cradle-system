<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Model\Service;

use Cradle\Package\System\Schema as SystemSchema;

/**
 * SQL service test
 * System Model Test
 *
 * @vendor   Cradle
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Model_Service_SqlServiceTest extends TestCase
{
    /**
     * @var SqlService $model
     */
    protected $model;

    /**
     * @covers Cradle\Package\System\Model\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->model = Service::get('sql');

        $this->model->setSchema(SystemSchema::i('sample'));
    }

    /**
     * @covers Cradle\Package\System\Model\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->model->create([
            'sample_name' => 'Test',
            'sample_active' => 1,
        ]);

        $id = $this->model->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['sample_id']);
    }

    /**
     * @covers Cradle\Package\System\Model\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->model->get('sample_id', 1);

        $this->assertEquals(1, $actual['sample_id']);
    }

    /**
     * @covers Cradle\Package\System\Model\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->model->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['sample_id']);
    }

    /**
     * @covers Cradle\Package\System\model\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->model->getResource()->getLastInsertedId();
        $actual = $this->model->update([
            'sample_id' => $id,
            'sample_name' => 'Edited Name',
        ]);

        $this->assertEquals($id, $actual['sample_id']);
    }

    /**
     * @covers Cradle\Package\System\model\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->model->getResource()->getLastInsertedId();
        $actual = $this->model->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['sample_id']);
    }
}
