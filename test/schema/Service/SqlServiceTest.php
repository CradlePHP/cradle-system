<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Module\System\Service;
use Cradle\Module\System\Schema as SystemSchema;

/**
 * SQL service test
 * Role Model Test
 *
 * @vendor   Acme
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_System_Schema_Service_SqlServiceTest extends TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\System\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\System\Service\SqlService::create
     */
    public function testCreate()
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
            'relations' => [
                [
                    'many' => '0',
                    'name' => 'profile',
                ]
            ],
            'suggestion' => ''
        ];

        $schema = SystemSchema::i($data);
        $this->object->setSchema($schema);
        $actual = $this->object->create($data);

        $query = 'DROP TABLE IF EXISTS `sample`;';

        $this->assertArrayHasKey(0, $actual);
        $this->assertArrayHasKey('query', $actual[0]);
        $this->assertArrayHasKey('results', $actual[0]);
        $this->assertEquals($query, $actual[0]['query']);
    }

    /**
     * @covers Cradle\Module\System\Service\SqlService::update
     */
    public function testUpdate()
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
            'relations' => [
                [
                    'many' => '0',
                    'name' => 'profile',
                ]
            ],
            'suggestion' => ''
        ];

        $schema = SystemSchema::i('sample');
        $this->object->setSchema($schema);
        $actual = $this->object->update($data);

        $this->assertArrayHasKey(0, $actual);
        $this->assertArrayHasKey('query', $actual[0]);
        $this->assertArrayHasKey('results', $actual[0]);
    }

    /**
     * @covers Cradle\Module\System\Service\SqlService::remove
     */
    public function testRemove()
    {
        $schema = SystemSchema::i('sample');
        $this->object->setSchema($schema);
        $actual = $this->object->remove('sample');

        $query = 'RENAME TABLE `sample` TO `_sample`;';

        $this->assertArrayHasKey(0, $actual);
        $this->assertArrayHasKey('query', $actual[0]);
        $this->assertArrayHasKey('results', $actual[0]);
        $this->assertEquals($query, $actual[0]['query']);
    }

    /**
     * @covers Cradle\Module\System\Service\SqlService::restore
     */
    public function testRestore()
    {
        $schema = SystemSchema::i('sample');
        $this->object->setSchema($schema);
        $actual = $this->object->restore('sample');

        $query = 'RENAME TABLE `_sample` TO `sample`;';

        $this->assertArrayHasKey(0, $actual);
        $this->assertArrayHasKey('query', $actual[0]);
        $this->assertArrayHasKey('results', $actual[0]);
        $this->assertEquals($query, $actual[0]['query']);
    }
}
