<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Schema;

use Cradle\Package\System\Schema\Service\SqlService;
use Cradle\Package\System\Schema\Service\RedisService;
use Cradle\Package\System\Schema\Service\ElasticService;

use Cradle\Module\Utility\Service\NoopService;
use Cradle\Module\Utility\ServiceInterface;

/**
 * Service layer
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christan Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Service implements ServiceInterface
{
    /**
     * Returns a service. To prevent having to define a method per
     * service, instead we roll everything into one function
     *
     * @param *string $name
     * @param string  $key
     *
     * @return object
     */
    public static function get($name, $key = 'main')
    {
        if (in_array($name, ['sql', 'redis', 'elastic'])) {
            $resource = cradle('global')->service($name . '-' . $key);

            if ($resource) {
                if ($name === 'sql') {
                    return new SqlService($resource);
                }

                if ($name === 'redis') {
                    return new RedisService($resource);
                }

                if ($name === 'elastic') {
                    return new ElasticService($resource);
                }
            }
        }

        return new NoopService();
    }
}
