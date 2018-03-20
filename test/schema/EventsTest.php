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
 * @package  Object
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Schema_EventsTest extends TestCase
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
     * schema-create
     *
     * @covers Cradle\Package\System\Schema\Validator::getCreateErrors
     * @covers Cradle\Package\System\Schema\Validator::getOptionalErrors
     * @covers Cradle\Package\System\Schema\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testSchemaCreate()
    {
        $data = [
            'singular'  => 'sample',
            'plural'    => 'samples',
            'name'      => 'sample',
            'icon'      => 'fas fa-user',
            'detail'    => 'sample detail',
            'fields'    => [
                [
                    'label'     => 'name',
                    'name'      => 'name',
                    'field'     => [
                        'type'          => 'text',
                        'attributes'    => [
                            'placeholder'   => 'name'
                        ]
                    ],
                    'validation' => [
                        [
                            'method' => 'required',
                            'message' => 'Name is required'
                        ],
                    ],
                    'list'      => [
                        'format'    => 'lower'
                    ],
                    'detail'        => 'none',
                    'default'       => '',
                    'searchable'    => 1,
                    'filterable'    => 1
                ],
                [
                    'label'     => 'Active',
                    'name'      => 'active',
                    'field'     => [
                        'type'          => 'active'
                    ],
                    'list'      => [
                        'format'    => 'hide'
                    ],
                    'detail'        => [
                        'format' => 'hide'
                    ],
                    'default'       => '',
                    'filterable'    => 1,
                    'sortable'      => 1
                ]
            ],
            'suggestion' => ''
        ];

        $this->request->setStage($data);

        cradle()->trigger('system-schema-create', $this->request, $this->response);

        $this->assertEquals('sample', $this->response->getResults('name'));
    }

    /**
     * schema-update
     *
     * @covers Cradle\Package\System\Schema\Validator::getUpdateErrors
     * @covers Cradle\Package\System\Schema\Validator::getOptionalErrors
     * @covers Cradle\Package\System\Schema\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::updateDetail
     */
    public function testSchemaUpdate()
    {
        $data = [
            'singular'  => 'test',
            'plural'    => 'tests',
            'name'      => 'test',
            'icon'      => 'fas fa-user',
            'detail'    => 'test detail',
            'fields'    => [
                [
                    'label'     => 'name',
                    'name'      => 'name',
                    'field'     => [
                        'type'          => 'text',
                        'attributes'    => [
                            'placeholder'   => 'name'
                        ]
                    ],
                    'validation' => [
                        [
                            'method' => 'required',
                            'message' => 'Name is required'
                        ],
                    ],
                    'list'      => [
                        'format'    => 'lower'
                    ],
                    'detail'        => 'none',
                    'default'       => '',
                    'searchable'    => 1,
                    'filterable'    => 1
                ],
                [
                    'label'     => 'Active',
                    'name'      => 'active',
                    'field'     => [
                        'type'          => 'active'
                    ],
                    'list'      => [
                        'format'    => 'hide'
                    ],
                    'detail'        => [
                        'format' => 'hide'
                    ],
                    'default'       => '',
                    'filterable'    => 1,
                    'sortable'      => 1
                ]
            ],
            'suggestion' => ''
        ];

        $this->request->setStage($data);

        cradle()->trigger('system-schema-update', $this->request, $this->response);
        $this->assertEquals('Not Found', $this->response->getMessage());

        $data = [
            'singular'  => 'sample',
            'plural'    => 'samples',
            'name'      => 'sample',
            'icon'      => 'fas fa-user',
            'detail'    => 'sample detail',
            'fields'    => [
                [
                    'label'     => 'name',
                    'name'      => 'name',
                    'field'     => [
                        'type'          => 'text',
                        'attributes'    => [
                            'placeholder'   => 'name'
                        ]
                    ],
                    'validation' => [
                        [
                            'method' => 'required',
                            'message' => 'Name is required'
                        ],
                    ],
                    'list'      => [
                        'format'    => 'lower'
                    ],
                    'detail'        => 'none',
                    'default'       => '',
                    'searchable'    => 1,
                    'filterable'    => 1
                ],
                [
                    'label'     => 'like',
                    'name'      => 'like',
                    'field'     => [
                        'type'          => 'number'
                    ],
                    'list'      => [
                        'format'    => 'none'
                    ],
                    'detail'        => [
                        'format' => 'none'
                    ],
                    'default'       => '',
                    'searchable'    => 1,
                    'filterable'    => 1,
                    'sortable'      => 1
                ],
                [
                    'label'     => 'Active',
                    'name'      => 'active',
                    'field'     => [
                        'type'          => 'active'
                    ],
                    'list'      => [
                        'format'    => 'hide'
                    ],
                    'detail'        => [
                        'format' => 'hide'
                    ],
                    'default'       => '',
                    'filterable'    => 1,
                    'sortable'      => 1
                ]
            ],
            'suggestion' => 'aa'
        ];

        $this->request->setStage($data);

        cradle()->trigger('system-schema-update', $this->request, $this->response);

        $this->assertEquals('sample', $this->response->getResults('name'));
    }

    /**
     * schema-detail
     *
     * @covers Cradle\Package\System\Schema\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testSchemaDetail()
    {
        $this->request->setStage('schema', 'sample');

        cradle()->trigger('system-schema-detail', $this->request, $this->response);

        $this->assertEquals('sample', $this->response->getResults('name'));
    }

    /**
     * schema-create
     *
     * @covers Cradle\Package\System\Schema\Service\SqlService::remove
     * @covers Cradle\Package\System\Schema\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testSchemaRemove()
    {
        $this->request->setStage('name', 'sample');

        cradle()->trigger('system-schema-remove', $this->request, $this->response);

        $this->assertTrue(!empty($this->response->getResults()));
        $this->assertEquals('RENAME TABLE `sample` TO `_sample`;', $this->response->getResults(0, 'query'));
    }

    /**
     * schema-create
     *
     * @covers Cradle\Package\System\Schema\Service\SqlService::restore
     * @covers Cradle\Package\System\Schema\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testSchemaRestore()
    {
        $this->request->setStage('name', 'sample');

        cradle()->trigger('system-schema-restore', $this->request, $this->response);

        $this->assertTrue(!empty($this->response->getResults()));
        $this->assertEquals('RENAME TABLE `_sample` TO `sample`;', $this->response->getResults(0, 'query'));
    }
}
