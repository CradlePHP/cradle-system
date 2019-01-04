<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Model\Service;

use Cradle\Package\System\Model\Service;
use Cradle\Package\System\Schema;

use Predis\Client as Resource;

use Cradle\Module\Utility\Service\RedisServiceInterface;
use Cradle\Module\Utility\Service\AbstractRedisService;

/**
 * Model Redis Service
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christan Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class RedisService extends AbstractRedisService implements RedisServiceInterface
{
    /**
     * @const CACHE_SEARCH Cache search key
     */
    const CACHE_SEARCH = 'core-model-search';

    /**
     * @const CACHE_DETAIL Cache detail key
     */
    const CACHE_DETAIL = 'core-model-detail';

    /**
     * @var Schema|null $schema
     */
    protected $schema = null;

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
        $this->sql = Service::get('sql');
        $this->elastic = Service::get('elastic');
    }

    /**
     * Adds System Schema
     *
     * @param Schema $schema
     *
     * @return SqlService
     */
    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
        return $this;
    }
}
