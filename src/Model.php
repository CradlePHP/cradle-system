<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System;

use Cradle\Package\System\Schema;
use Cradle\Package\System\Model\Service;
use Cradle\Package\System\Model\Validator;
use Cradle\Package\System\Model\Formatter;

use Cradle\Module\Utility\Service\NoopService;

use Cradle\Helper\InstanceTrait;

/**
 * Formatter layer
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christan Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Model
{
    use InstanceTrait;

    /**
     * @var Schema|null $schema
     */
    protected $schema = null;

    /**
     * Adds System Schema
     *
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Returns a service. To prevent having to define a method per
     * service, instead we roll everything into one function
     *
     * @param *string $name
     * @param string  $key
     *
     * @return object
     */
    public function service($name, $key = 'main')
    {
        $service = Service::get($name, $key);

        if ($service instanceof NoopService) {
            return $service;
        }

        return $service->setSchema($this->schema);
    }

    /**
     * Returns the formatter
     *
     * @return Formatter
     */
    public function formatter()
    {
        return Formatter::i($this->schema);
    }

    /**
     * Returns the validator
     *
     * @return Validator
     */
    public function validator()
    {
        return Validator::i($this->schema);
    }
}
