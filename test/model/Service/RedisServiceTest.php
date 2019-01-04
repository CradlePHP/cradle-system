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
 * Redis service test
 *
 * @vendor   Cradle
 * @package  System Model
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Model_Service_RedisServiceTest extends TestCase
{
    /**
     * @var RedisService $model
     */
    protected $model;

    /**
     * @covers Cradle\Package\System\Model\Service\RedisService::__construct
     */
    protected function setUp()
    {
        $this->model = Service::get('redis');
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testCreateDetail()
    {
        $actual = $this->model->createDetail(1, 1, 'sample');

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertEquals(1, $actual);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createSearch
     */
    public function testCreateSearch()
    {
        $actual = $this->model->createSearch([], 'sample');

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertEquals(1, $actual);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testGetDetail()
    {
        $actual = $this->model->getDetail(1);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertEquals(1, $actual['sample_id']);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::hasDetail
     */
    public function testHasDetail()
    {
        $actual = $this->model->hasDetail(1);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertTrue($actual);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testGetSearch()
    {
        $actual = $this->model->getSearch([]);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['sample_id']);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::hasSearch
     */
    public function testHasSearch()
    {
        $actual = $this->model->hasSearch([]);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertTrue($actual);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     */
    public function testRemoveDetail()
    {
        $actual = $this->model->removeDetail(1);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertEquals(1, $actual);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testRemoveSearch()
    {
        $actual = $this->model->removeSearch([]);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertEquals(1, $actual);
    }
}
