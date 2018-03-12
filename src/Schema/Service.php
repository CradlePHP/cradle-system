<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System\Schema;

use Cradle\Package\System\Schema\Service\SqlService;

use Cradle\Module\Utility\Service\NoopService;
use Cradle\Module\Utility\ServiceInterface;

/**
 * Service layer
 *
 * @vendor   Acme
 * @package  object
 * @author   John Doe <john@acme.com>
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
        //for now only sql
        if (in_array($name, ['sql'])) {
            $resource = cradle()->package('global')->service($name . '-' . $key);

            if ($resource) {
                if ($name === 'sql') {
                    return new SqlService($resource);
                }
            }
        }

        return new NoopService();
    }
}
