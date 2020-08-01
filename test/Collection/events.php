<?php

namespace Cradle\Package\System;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Cradle_Package_System_Collection_Events_Test extends TestCase
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
    $this->object = cradle('global')
      ->path('schema', dirname(__DIR__) . '/assets/schema')
      ->register('pdo');

    $this->object
      ->package('pdo')
      ->mapPackageMethods(include __DIR__.'/assets/mysql.php');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {
  }

  /**
   */
  public function testCreate()
  {
    $cradle = $this->object;
    //$cradle->emit('system-collection-create', [

    //]);
  }
}