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

use Elasticsearch\Client as Resource;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;

use Cradle\Module\Utility\Service\ElasticServiceInterface;
use Cradle\Module\Utility\Service\AbstractElasticService;
use Cradle\Package\System\Schema as SystemSchema;
/**
 * Model ElasticSearch Service
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christan Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class ElasticService extends AbstractElasticService implements ElasticServiceInterface
{
    /**
     * @const INDEX_NAME Index name
     */
    const INDEX_NAME = 'model';

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
    }

    /**
     * Create in index
     *
     * @param *int $id
     *
     * @return array
     */
    public function create($id)
    {
        $exists = false;
        try {
            // check if index exist
            $exists = $this->resource->indices()->exists(['index' => $this->schema->getName()]);
        } catch (\Throwable $e) {
            // return false if something went wrong
            return false;
        }

        // if index doesnt exist
        if (!$exists) {
            // do nothing
            return false;
        }
        
        // set schema to sql
        $this->sql->setSchema($this->schema);
        // get data from sql
        $body = $this->sql->get($this->schema->getPrimaryFieldName(), $id);
        
        if (!is_array($body) || empty($body)) {
            return false;
        }

        try {
            return $this->resource->index([
                'index' => $this->schema->getName(),
                'type' => static::INDEX_TYPE,
                'id' => $id,
                'body' => $body
            ]);
        } catch (NoNodesAvailableException $e) {
            return false;
        } catch (BadRequest400Exception $e) {
            return false;
        } catch (\Throwable $e) {
            // catch all throwable
            // if something went wrong, dont panic, just return false
            return false;
        }
    }

    /**
     * Search in index
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        // schema should be set
        if (!isset($data['schema'])) {
            return false;
        }

        $model = $data['schema'];

        // 4. Process Data
        $schema = SystemSchema::i($model);
        
        if(is_null($schema)) {
            throw SystemException::forNoSchema();
        }

        $searchable = $schema->getSearchableFieldNames();
        $primary = $schema->getPrimaryFieldName();
        
        //set the defaults
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [$primary => 'asc'];
        $count = 0;

        //merge passed data with default data
        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        //prepare the search model
        $search = [];

        //keyword search
        if (isset($data['q'])) {
            if (!is_array($data['q'])) {
                $data['q'] = [$data['q']];
            }

            foreach ($data['q'] as $keyword) {
                $search['query']['bool']['filter'][]['query_string'] = [
                    'query' => $keyword . '*',
                    'fields' => $searchable,
                    'default_operator' => 'AND'
                ];
            }
        }


        //generic full match filters

        //model_active
        $activeField = $schema->getActiveFieldName();
        if ($activeField) {
            if (!isset($filter[$activeField])) {
                $filter[$activeField] = 1;
            }
        }

        foreach ($filter as $key => $value) {
            $search['query']['bool']['filter'][]['term'][$key] = $value;
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search['sort'] = [$sort => $direction];
        }
        
        try {
            $results = $this->resource->search([
                'index' => $model,
                'type' => static::INDEX_TYPE,
                'body' => $search,
                'size' => $range,
                'from' => $start
            ]);
        } catch (NoNodesAvailableException $e) {
            return false;
        } catch (\Throwable $e) {
            // catch throwable,
            // this means it will use sql as source of data
            return false;
        }

        // fix it
        $rows = array();

        foreach ($results['hits']['hits'] as $item) {
            $rows[] = $item['_source'];
        }
        
        //return response format
        return [
            'rows' => $rows,
            'total' => $results['hits']['total']
        ];
    }

    /**
     * Adds System Schema
     *
     * @param SystemSchema $schema
     *
     * @return SqlService
     */
    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
        return $this;
    }

    
    /**
     * Remove from index
     *
     * @param *int $id
     */
    public function remove($id)
    {
        try {
            return $this->resource->delete([
                'index' => $this->schema->getName(),
                'type' => static::INDEX_TYPE,
                'id' => $id
            ]);
        } catch (NoNodesAvailableException $e) {
            return false;
        }
    }

    /**
     * Update to index
     *
     * @param *int $id
     *
     * @return array
     */
    public function update($id)
    {
        // set schema to sql
        $this->sql->setSchema($this->schema);
        // get data from sql
        $body = $this->sql->get($this->schema->getPrimaryFieldName(), $id);

        if (!is_array($body) || empty($body)) {
            return false;
        }

        try {
            return $this->resource->update(
                [
                    'index' => $this->schema->getName(),
                    'type' => static::INDEX_TYPE,
                    'id' => $id,
                    'body' => [
                        'doc' => $body
                    ]
                ]
            );
        } catch (Missing404Exception $e) {
            return false;
        } catch (NoNodesAvailableException $e) {
            return false;
        } catch (\Throwable $e) {
            // catch all throwable
            // if something went wrong, dont panic, just return false
            return false;
        }
    }
}
