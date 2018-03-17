<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Event test
 *
 * @vendor   Acme
 * @package  Object
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_System_Object_EventsTest extends TestCase
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var Request $response
     */
    protected $response;

    /**
     * @var int $id
     */
    protected static $id;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();

        $this->request->load();
        $this->response->load();
    }

    /**
     * system-object-create
     *
     * @covers Cradle\Module\System\Object\Validator::getCreateErrors
     * @covers Cradle\Module\System\Object\Validator::getOptionalErrors
     * @covers Cradle\Module\System\Object\Service\SqlService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::createDetail
     */
    public function testObjectCreate()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_name' => 'sample'
        ]);

        cradle()->trigger('system-object-create', $this->request, $this->response);

        $this->assertEquals('sample', $this->response->getResults('sample_name'));
        self::$id = $this->response->getResults('sample_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * system-object-create
     *
     * @covers Cradle\Module\System\Object\Validator::getCreateErrors
     * @covers Cradle\Module\System\Object\Validator::getOptionalErrors
     * @covers Cradle\Module\System\Object\Service\SqlService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::createDetail
     */
    public function testObjectDetail()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_id' => '1'
        ]);

        cradle()->trigger('system-object-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('sample_id'));
    }

    /**
     * system-object-remove
     *
     * @covers Cradle\Module\System\Object\Service\SqlService::get
     * @covers Cradle\Module\System\Object\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::remove
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     */
    public function testObjectRemove()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_id' => self::$id
        ]);

        cradle()->trigger('system-object-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('sample_id'));
    }

    /**
     * system-object-restore
     *
     * @covers Cradle\Module\System\Object\Service\SqlService::get
     * @covers Cradle\Module\System\Object\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::remove
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     */
    public function testObjectRestore()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_id' => self::$id
        ]);

        cradle()->trigger('system-object-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('sample_id'));
    }

    /**
     * system-object-search
     *
     * @covers Cradle\Module\System\Object\Service\SqlService::search
     * @covers Cradle\Module\System\Object\Service\ElasticService::search
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::getSearch
     */
    public function testObjectSearch()
    {
        $this->request->setStage([
            'schema' => 'sample'
        ]);

        cradle()->trigger('system-object-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'sample_id'));
    }

    /**
     * system-object-update
     *
     * @covers Cradle\Module\System\Object\Service\SqlService::get
     * @covers Cradle\Module\System\Object\Service\SqlService::update
     * @covers Cradle\Module\System\Utility\Service\AbstractElasticService::remove
     * @covers Cradle\Module\System\Utility\Service\AbstractRedisService::removeDetail
     */
    public function testObjectUpdate()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_id' => 1,
            'sample_name' => 'New Sample Name'
        ]);

        cradle()->trigger('system-object-update', $this->request, $this->response);
        $this->assertEquals('New Sample Name', $this->response->getResults('sample_name'));
        $this->assertEquals(self::$id, $this->response->getResults('sample_id'));
    }
}
