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

use Cradle\Module\System\Service\SqlService;
use Cradle\Module\System\Service\RedisService;
use Cradle\Module\System\Service\ElasticService;
use Cradle\Module\System\Utility\Service\NoopService;

/**
 * Service layer test
 *
 * @vendor   Acme
 * @package  Schema
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_System_Schema_ServiceTest extends TestCase
{
    /**
     * @covers Cradle\Module\Role\Service::get
     */
    public function testGet()
    {
        $actual = Service::get('sql');
        $this->assertTrue($actual instanceof SqlService || $actual instanceof NoopService);

        $actual = Service::get('redis');
        $this->assertTrue($actual instanceof RedisService || $actual instanceof NoopService);

        $actual = Service::get('elastic');
        $this->assertTrue($actual instanceof ElasticService || $actual instanceof NoopService);
    }
}
