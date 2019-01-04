<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use PHPUnit\Framework\TestCase;

use Cradle\Package\System\Model\Service;

use Cradle\Package\System\Model\Service\SqlService;
use Cradle\Package\System\Model\Service\RedisService;
use Cradle\Package\System\Model\Service\ElasticService;
use Cradle\Module\Utility\Service\NoopService;

/**
 * Service layer test
 *
 * @vendor   Cradle
 * @package  Model
 * @author   John Doe <john@acme.com>
 */
class Cradle_Package_System_Model_ServiceTest extends TestCase
{
    /**
     * @covers Cradle\Package\Role\Service::get
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
