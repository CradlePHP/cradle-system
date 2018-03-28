<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
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
 * @vendor   Cradle
 * @package  Model
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Model_EventsTest extends TestCase
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
     * system-model-create
     *
     * @covers Cradle\Package\System\Model\Validator::getCreateErrors
     * @covers Cradle\Package\System\Model\Validator::getOptionalErrors
     * @covers Cradle\Package\System\Model\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testModelCreate()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_name' => 'sample'
        ]);

        cradle()->trigger('system-model-create', $this->request, $this->response);

        $this->assertEquals('sample', $this->response->getResults('sample_name'));
        self::$id = $this->response->getResults('sample_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * system-model-create
     *
     * @covers Cradle\Package\System\Model\Validator::getCreateErrors
     * @covers Cradle\Package\System\Model\Validator::getOptionalErrors
     * @covers Cradle\Package\System\Model\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testModelDetail()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_id' => '1'
        ]);

        cradle()->trigger('system-model-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('sample_id'));
    }

    /**
     * system-model-remove
     *
     * @covers Cradle\Package\System\Model\Service\SqlService::get
     * @covers Cradle\Package\System\Model\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::remove
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     */
    public function testModelRemove()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_id' => self::$id
        ]);

        cradle()->trigger('system-model-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('sample_id'));
    }

    /**
     * system-model-restore
     *
     * @covers Cradle\Package\System\Model\Service\SqlService::get
     * @covers Cradle\Package\System\Model\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::remove
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     */
    public function testModelRestore()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_id' => self::$id
        ]);

        cradle()->trigger('system-model-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('sample_id'));
    }

    /**
     * system-model-search
     *
     * @covers Cradle\Package\System\Model\Service\SqlService::search
     * @covers Cradle\Package\System\Model\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testModelSearch()
    {
        $this->request->setStage([
            'schema' => 'sample'
        ]);

        cradle()->trigger('system-model-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'sample_id'));
    }

    /**
     * system-model-update
     *
     * @covers Cradle\Package\System\Model\Service\SqlService::get
     * @covers Cradle\Package\System\Model\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::remove
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     */
    public function testModelUpdate()
    {
        $this->request->setStage([
            'schema' => 'sample',
            'sample_id' => 1,
            'sample_name' => 'New Sample Name'
        ]);

        cradle()->trigger('system-model-update', $this->request, $this->response);
        $this->assertEquals('New Sample Name', $this->response->getResults('sample_name'));
        $this->assertEquals(self::$id, $this->response->getResults('sample_id'));
    }
}
