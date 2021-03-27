<?php

namespace Cradle\Package\System;

use PHPUnit\Framework\TestCase;

use Cradle\Framework\FrameworkHandler;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Cradle_Package_System_Events_Collection_Test extends TestCase
{
  /**
   * @var Package
   */
  protected $object;

  public function setUp()
  {
    //this is the OOP version of cradle
    $this->object = new FrameworkHandler;
    $testRoot = dirname(__DIR__);
    $packageRoot = dirname($testRoot);

    //set the schema folder
    Schema::setFolder($testRoot . '/assets/config/schema');

    //now register system
    $this->object->register('cradlephp/cradle-system', $packageRoot);

    $cradle = $this->object;

    $cradle('event')->on('system-store-insert', function($request, $response) {
      $response->setError(true, 'Insert stub defined');
      $response->setResults($request->getStage());
    });

    $cradle('event')->on('system-store-delete', function($request, $response) {
      $response->setError(true, 'Delete stub defined');
      $response->setResults($request->getStage());
    });

    $cradle('event')->on('system-store-search', function($request, $response) {
      $response->setError(true, 'Search stub defined');
      $response->setResults($request->getStage());
    });

    $cradle('event')->on('system-store-update', function($request, $response) {
      $response->setError(true, 'Update stub defined');
      $response->setResults($request->getStage());
    });
  }

  /**
   */
  public function testCreate()
  {
    $cradle = $this->object;
    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema' => 'profile',
      'rows' => [
        [
          'profile_name' => 'foo'
        ]
      ]
    ]);

    $cradle('event')->emit(
      'system-collection-create',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Insert stub defined', $payload['response']->getMessage());
    $this->assertEquals('profile', $payload['response']->getResults('table'));
  }

  /**
   */
  public function testLink()
  {
    $cradle = $this->object;
    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema1' => 'product',
      'schema2' => 'profile',
      'product_id' => 1,
      'profile_id' => 2
    ]);

    $cradle('event')->emit(
      'system-collection-link',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Insert stub defined', $payload['response']->getMessage());
    $this->assertEquals('product_profile', $payload['response']->getResults('table'));
    $this->assertEquals(1, $payload['response']->getResults('rows', 0, 'product_id'));
    $this->assertEquals(2, $payload['response']->getResults('rows', 0, 'profile_id'));

    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema1' => 'product',
      'schema2' => 'profile',
      'product_id' => [1, 2, 3],
      'profile_id' => [4, 5, 6]
    ]);

    $cradle('event')->emit(
      'system-collection-link',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Insert stub defined', $payload['response']->getMessage());
    $this->assertEquals('product_profile', $payload['response']->getResults('table'));

    $this->assertEquals(1, $payload['response']->getResults('rows', 0, 'product_id'));
    $this->assertEquals(4, $payload['response']->getResults('rows', 0, 'profile_id'));
    $this->assertEquals(1, $payload['response']->getResults('rows', 1, 'product_id'));
    $this->assertEquals(5, $payload['response']->getResults('rows', 1, 'profile_id'));
    $this->assertEquals(1, $payload['response']->getResults('rows', 2, 'product_id'));
    $this->assertEquals(6, $payload['response']->getResults('rows', 2, 'profile_id'));

    $this->assertEquals(2, $payload['response']->getResults('rows', 3, 'product_id'));
    $this->assertEquals(4, $payload['response']->getResults('rows', 3, 'profile_id'));
    $this->assertEquals(2, $payload['response']->getResults('rows', 4, 'product_id'));
    $this->assertEquals(5, $payload['response']->getResults('rows', 4, 'profile_id'));
    $this->assertEquals(2, $payload['response']->getResults('rows', 5, 'product_id'));
    $this->assertEquals(6, $payload['response']->getResults('rows', 5, 'profile_id'));

    $this->assertEquals(3, $payload['response']->getResults('rows', 6, 'product_id'));
    $this->assertEquals(4, $payload['response']->getResults('rows', 6, 'profile_id'));
    $this->assertEquals(3, $payload['response']->getResults('rows', 7, 'product_id'));
    $this->assertEquals(5, $payload['response']->getResults('rows', 7, 'profile_id'));
    $this->assertEquals(3, $payload['response']->getResults('rows', 8, 'product_id'));
    $this->assertEquals(6, $payload['response']->getResults('rows', 8, 'profile_id'));
  }

  /**
   */
  public function testRemove()
  {
    $cradle = $this->object;
    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema' => 'profile',
      'filter' => [
        'profile_id' => 2
      ]
    ]);

    $cradle('event')->emit(
      'system-collection-remove',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Update stub defined', $payload['response']->getMessage());
    $this->assertEquals('profile', $payload['response']->getResults('table'));
    $this->assertEquals('profile_id = %s', $payload['response']->getResults('filters', 0, 'where'));
    $this->assertEquals(2, $payload['response']->getResults('filters', 0, 'binds', 0));
    $this->assertEquals(0, $payload['response']->getResults('data', 'profile_active'));
  }

  /**
   */
  public function testRestore()
  {
    $cradle = $this->object;
    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema' => 'profile',
      'filter' => [
        'profile_id' => 2
      ]
    ]);

    $cradle('event')->emit(
      'system-collection-restore',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Update stub defined', $payload['response']->getMessage());
    $this->assertEquals('profile', $payload['response']->getResults('table'));
    $this->assertEquals('profile_id = %s', $payload['response']->getResults('filters', 0, 'where'));
    $this->assertEquals(2, $payload['response']->getResults('filters', 0, 'binds', 0));
    $this->assertEquals(1, $payload['response']->getResults('data', 'profile_active'));
  }

  /**
   */
  public function testSearch()
  {
    $cradle = $this->object;
    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema' => 'profile',
      'columns' => 'profile_id',
      'join' => ['file'],
      'filter' => [
        'profile_id' => 2
      ],
      'in' => [
        'profile_id' => [1, 2]
      ],
      'span' => [
        'profile_id' => [1, 2]
      ],
      'like' => [
        'profile_id' => 1
      ],
      'empty' => [
        'profile_id'
      ],
      'nempty' => [
        'profile_id'
      ],
    ]);

    $cradle('event')->emit(
      'system-collection-search',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Search stub defined', $payload['response']->getMessage());
    $this->assertEquals('profile', $payload['response']->getResults('table'));
    $this->assertEquals('inner', $payload['response']->getResults('joins', 0, 'type'));
    $this->assertEquals('profile_file', $payload['response']->getResults('joins', 0, 'table'));
    $this->assertEquals('profile_id', $payload['response']->getResults('joins', 0, 'where'));
    $this->assertEquals('inner', $payload['response']->getResults('joins', 1, 'type'));
    $this->assertEquals('file', $payload['response']->getResults('joins', 1, 'table'));
    $this->assertEquals('file_id', $payload['response']->getResults('joins', 1, 'where'));
    $this->assertEquals('profile_id = %s', $payload['response']->getResults('filters', 0, 'where'));
    $this->assertEquals(2, $payload['response']->getResults('filters', 0, 'binds', 0));
    $this->assertEquals('profile_id LIKE %s', $payload['response']->getResults('filters', 1, 'where'));
    $this->assertEquals(1, $payload['response']->getResults('filters', 1, 'binds', 0));
    $this->assertEquals('profile_id IN (%s, %s)', $payload['response']->getResults('filters', 2, 'where'));
    $this->assertEquals(1, $payload['response']->getResults('filters', 2, 'binds', 0));
    $this->assertEquals(2, $payload['response']->getResults('filters', 2, 'binds', 1));
    $this->assertEquals('profile_id >= %s', $payload['response']->getResults('filters', 3, 'where'));
    $this->assertEquals(1, $payload['response']->getResults('filters', 3, 'binds', 0));
    $this->assertEquals('profile_id <= %s', $payload['response']->getResults('filters', 4, 'where'));
    $this->assertEquals(2, $payload['response']->getResults('filters', 4, 'binds', 0));
    $this->assertEquals('profile_id IS NULL', $payload['response']->getResults('filters', 5, 'where'));
    $this->assertEquals('profile_id IS NOT NULL', $payload['response']->getResults('filters', 6, 'where'));
  }

  /**
   */
  public function testUnlink()
  {
    $cradle = $this->object;
    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema1' => 'product',
      'schema2' => 'profile',
      'product_id' => 1,
      'profile_id' => 2
    ]);

    $cradle('event')->emit(
      'system-collection-unlink',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Delete stub defined', $payload['response']->getMessage());
    $this->assertEquals('product_profile', $payload['response']->getResults('table'));
    $this->assertEquals('(product_id = 1 AND profile_id = 2)', $payload['response']->getResults('filter', 0, 'where'));

    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema1' => 'product',
      'schema2' => 'profile',
      'product_id' => [1, 2, 3],
      'profile_id' => [4, 5, 6]
    ]);

    $cradle('event')->emit(
      'system-collection-unlink',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Delete stub defined', $payload['response']->getMessage());
    $this->assertEquals('product_profile', $payload['response']->getResults('table'));
    $this->assertEquals('(product_id = 1 AND profile_id = 4) OR (product_id = 1 AND profile_id = 5) OR (product_id = 1 AND profile_id = 6) OR (product_id = 2 AND profile_id = 4) OR (product_id = 2 AND profile_id = 5) OR (product_id = 2 AND profile_id = 6) OR (product_id = 3 AND profile_id = 4) OR (product_id = 3 AND profile_id = 5) OR (product_id = 3 AND profile_id = 6)', $payload['response']->getResults('filter', 0, 'where'));
  }

  /**
   */
  public function testUpdate()
  {
    $cradle = $this->object;
    $payload = $cradle->makePayload(false);
    $payload['request']->setStage([
      'schema' => 'profile',
      'profile_name' => 'foo',
      'filter' => [
        'profile_id' => 2
      ]
    ]);

    $cradle('event')->emit(
      'system-collection-update',
      $payload['request'],
      $payload['response']
    );

    $this->assertTrue($payload['response']->isError());
    $this->assertEquals('Update stub defined', $payload['response']->getMessage());
    $this->assertEquals('profile', $payload['response']->getResults('table'));
    $this->assertEquals('profile_id = %s', $payload['response']->getResults('filters', 0, 'where'));
    $this->assertEquals(2, $payload['response']->getResults('filters', 0, 'binds', 0));
    $this->assertEquals('foo', $payload['response']->getResults('data', 'profile_name'));
    $this->assertTrue(isset($payload['response']->getResults('data')['profile_updated']));
    $this->assertFalse(isset($payload['response']->getResults('data')['profile_created']));
    $this->assertFalse(isset($payload['response']->getResults('data')['profile_gender']));
  }
}