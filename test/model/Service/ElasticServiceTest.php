<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Model\Service;

/**
 * Elastic service test
 *
 * @vendor   Cradle
 * @package  model
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Model_Service_ElasticServiceTest extends TestCase
{
    /**
     * @var ElasticService $model
     */
    protected $model;

    /**
     * @covers Cradle\Package\System\Model\Service\ElasticService::__construct
     */
    protected function setUp()
    {
        $this->model = Service::get('elastic');
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::remove
     */
    public function testRemove()
    {
        $actual = $this->model->remove(1);

        //if it's false, it's not enabled
        if($actual === false) {
            $this->assertTrue(!$actual);
            return;
        }

        $this->assertEquals('sample', $actual['_index']);
        $this->assertEquals('main', $actual['_type']);
        $this->assertEquals(1, $actual['_id']);
        $this->assertEquals('deleted', $actual['result']);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     */
    public function testCreate()
    {
        $actual = $this->model->create(1);

        //if it's false, it's not enabled
        if($actual === false) {
            $this->assertTrue(!$actual);
            return;
        }

        $this->assertEquals('sample', $actual['_index']);
        $this->assertEquals('main', $actual['_type']);
        $this->assertEquals(1, $actual['_id']);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     */
    public function testGet()
    {
        $actual = $this->model->get(1);

        //if it's false, it's not enabled
        if($actual === false) {
            $this->assertTrue(!$actual);
            return;
        }

        $this->assertEquals(1, $actual['sample_id']);
    }

    /**
     * @covers Cradle\Package\System\model\Service\ElasticService::search
     */
    public function testSearch()
    {
        $actual = $this->model->search();

        //if it's false, it's not enabled
        if($actual === false) {
            $this->assertTrue(!$actual);
            return;
        }

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['sample_id']);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     */
    public function testUpdate()
    {
        $this->model->create(1);

        $actual = $this->model->update(1);

        //if it's false, it's not enabled
        if($actual === false) {
            $this->assertTrue(!$actual);
            return;
        }

        // now, test it
        $this->assertEquals('sample', $actual['_index']);
        $this->assertEquals('main', $actual['_type']);
        $this->assertEquals(1, $actual['_id']);
        $this->assertEquals('noop', $actual['result']);
    }
}
