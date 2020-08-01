<?php

namespace Cradle\Package\System;

use PHPUnit\Framework\TestCase;

use Cradle\Package\PackageHandler;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Cradle_Package_System_Events_Fieldset_Test extends TestCase
{
  /**
   * @var Package
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    //this is the OOP version of cradle
    $this->object = new PackageHandler;
    $testRoot = dirname(__DIR__);
    $packageRoot = dirname($testRoot);

    //set the fieldset folder
    Fieldset::setFolder($testRoot . '/assets/config/schema');

    //now register system
    $this->object->register('cradlephp/cradle-system', $packageRoot);
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  /**
   * @covers Cradle\Package\System\Fieldset::save
   */
  public function testCreate()
  {
    $source = dirname(__DIR__) . '/assets/post.php';
    $destination = dirname(__DIR__) . '/assets/config/schema/post.php';

    if (file_exists($destination)) {
      unlink($destination);
    }

    $cradle = $this->object;
    $payload = $cradle('io')->makePayload(false);
    $payload['request']->setStage(include $source);

    $cradle('event')->emit(
      'system-fieldset-create',
      $payload['request'],
      $payload['response']
    );

    $this->assertFalse($payload['response']->isError());
    $this->assertTrue(file_exists($destination));
  }

  /**
   * @covers Cradle\Package\System\Fieldset::load
   */
  public function testDetail()
  {
    $cradle = $this->object;
    $payload = $cradle('io')->makePayload(false);
    $payload['request']->setStage('name', 'profile');

    $cradle('event')->emit(
      'system-fieldset-detail',
      $payload['request'],
      $payload['response']
    );

    $this->assertFalse($payload['response']->isError());
    $this->assertEquals('profile', $payload['response']->getResults('name'));
  }

  /**
   * @covers Cradle\Package\System\Fieldset::save
   */
  public function testUpdate()
  {
    $source = dirname(__DIR__) . '/assets/post.php';
    $destination = dirname(__DIR__) . '/assets/config/schema/post.php';

    $cradle = $this->object;
    $payload = $cradle('io')->makePayload(false);
    $payload['request']->setStage(include $source);

    $cradle('event')->emit(
      'system-fieldset-update',
      $payload['request'],
      $payload['response']
    );

    $this->assertFalse($payload['response']->isError());
    $this->assertEquals('post', $payload['response']->getResults('name'));
    $this->assertTrue(file_exists($destination));
  }

  /**
   * @covers Cradle\Package\System\Fieldset::search
   */
  public function testSearch()
  {
    $cradle = $this->object;
    $payload = $cradle('io')->makePayload(false);
    $payload['request']->setStage([
      'filter' => [
        'active' => 1,
        'name' => 'profile',
        'relation' => 'address,2'
      ]
    ]);

    $cradle('event')->emit(
      'system-fieldset-search',
      $payload['request'],
      $payload['response']
    );

    $this->assertFalse($payload['response']->isError());
    $this->assertEquals(1, $payload['response']->getResults('total'));
  }

  /**
   * @covers Cradle\Package\System\Fieldset::archive
   * @covers Cradle\Package\System\Fieldset::delete
   */
  public function testRemove()
  {
    $cradle = $this->object;
    $payload = $cradle('io')->makePayload(false);
    $payload['request']->setStage('name', 'profile');

    $cradle('event')->emit(
      'system-fieldset-remove',
      $payload['request'],
      $payload['response']
    );

    $this->assertFalse($payload['response']->isError());
    $this->assertEquals('profile', $payload['response']->getResults('name'));

    $source = dirname(__DIR__) . '/assets/config/schema/profile.php';
    $destination = dirname(__DIR__) . '/assets/config/schema/_profile.php';

    $this->assertFalse(file_exists($source));
    $this->assertTrue(file_exists($destination));

    $payload = $cradle('io')->makePayload(false);
    $payload['request']->setStage('name', 'post');
    $payload['request']->setStage('mode', 'permanent');

    $cradle('event')->emit(
      'system-fieldset-remove',
      $payload['request'],
      $payload['response']
    );

    $this->assertFalse($payload['response']->isError());
    $this->assertEquals('post', $payload['response']->getResults('name'));

    $source = dirname(__DIR__) . '/assets/config/schema/post.php';
    $destination = dirname(__DIR__) . '/assets/config/schema/_post.php';

    $this->assertFalse(file_exists($source));
    $this->assertFalse(file_exists($destination));
  }

  /**
   * @covers Cradle\Package\System\Fieldset::restore
   */
  public function testRestore()
  {
    $cradle = $this->object;
    $payload = $cradle('io')->makePayload(false);
    $payload['request']->setStage('name', 'profile');

    $cradle('event')->emit(
      'system-fieldset-restore',
      $payload['request'],
      $payload['response']
    );

    $this->assertFalse($payload['response']->isError());
    $this->assertEquals('profile', $payload['response']->getResults('name'));

    $source = dirname(__DIR__) . '/assets/config/schema/_profile.php';
    $destination = dirname(__DIR__) . '/assets/config/schema/profile.php';

    $this->assertFalse(file_exists($source));
    $this->assertTrue(file_exists($destination));
  }
}
