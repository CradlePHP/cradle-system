<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Schema\Service;

use Cradle\Package\System\Schema\Service;
use Cradle\Package\System\Schema;

use Elasticsearch\Client as Resource;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;

use Cradle\Module\Utility\Service\ElasticServiceInterface;
use Cradle\Module\Utility\Service\AbstractElasticService;

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

    /*
     * Create elastic map
     *
     * @param array $data
    */
    public function createMap() {
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }
        
        // translate data first to sql
        $data = $this->schema->toSql();
        
        // then translate it to elastic mapping
        $mapping = $this->schema->toElastic($data);
        // get schema path
        $path = cradle()->package('global')->path('config') . '/schema/elastic';
        // if elastic dir doesn't exists
        // create elastic folder
        if(!is_dir($path)) {
            mkdir($path, 0777);
        }

        // if elastic schema dir doesn't exist
        // create elastic schema dir
        mkdir ($path . '/' . ucwords($data['name']));
        
        // save mapping
        file_put_contents(
            $path . '/' . ucwords($data['name']) . '/elastic.php',
            '<?php //-->' . "\n return " .
            var_export($mapping, true) . ';'
        );
        

    }

    /*
     * Map elastic
     *
     */
    public function map() {
        // no schema validation
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getName();
        $path = cradle()->package('global')->path('config')
              . sprintf('/schema/elastic/%s/elastic.php', ucwords($table));
        
        // if mapped file doesn't exist,
        // do nothing
        if (!file_exists($path)) {
            return false;
        }

        $data = include_once($path);
        
        // try mapping 
        try {
            $this->resource->indices()->create(['index' => $table]);
            $this->resource->indices()->putMapping([
                'index' => $table,
                'type' => 'main',
                'body' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => $data[$table]
                ]
            ]);
        } catch (NoNodesAvailableException $e) {
            //because there is no reason to continue;
            return false;
        } catch (BadRequest400Exception $e) {
            //already mapped
            return false;
        } catch (\Throwable $e) {
            // something is not right
            return false;
        }

        return true;
    }

    /**
     * Populate elastic
     *
     * @params array $data
     */
    public function populate(array $data = []) {
        // no schema validation
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }
        
        $exists = false;
        try {
            // check if index exist
            $exists = $this->resource
                ->indices()
                ->exists(
                    ['index' => $this->schema
                        ->getName()]
                );
            
        } catch (\Throwable $e) {
            // return false if something went wrong
            return false;
        }

        // no index available for this schema
        if (!$exists) {
            // just return false
            return false;
        }

        // get model services
        $modelSql = $this->schema->model()->service('sql');
        $modelElastic = $this->schema->model()->service('elastic');
        $modelRedis = $this->schema->model()->service('redis');
        
        // primary field
        $primary = $this->schema->getPrimaryFieldName();
        
        // get data from sql
        // set range to 1 so we dont have to exhaus sql server by pulling just the total entry
        $data = $modelSql->search(['range' => 1]);
        // get total entry
        $total = 0;
        if (isset ($data['total']) && is_numeric ($data['total'])) {
            $total = $data['total'];
        }
        
        // set current to 0 if current is not set
        $current = 0;
        if (isset ($data['current']) && is_numeric ($data['current'])) {
            $current = $data['current'];
        }
        
        $range = 10; // do 10 at a time
        for ($i = 0; $i < $total; $i++) {
            if ($i + $current > $total) {
                // this is the end :'(
                break;
            }

            // if end is set
            if (isset($data['end']) && is_numeric($data['end'])) {
                if($current + $i > $data['end']) {
                    // end this
                    break;
                }
                
            }
            
            // set request params
            $stage = ['start' => $current, 'range' => $current + $range];
            // get entries
            $entries = $modelSql->search($stage);
            $entries = $entries['rows'];
            
            // loop thru entries
            foreach ($entries as $entry) {
                $create = $modelElastic->create($entry[$primary]);
                if (!$create) {
                    // nothing to do
                    return false;
                }
                
            }

            // increment current
            $current = $current + $range;
        }

        // dont forget to flush redis
        
        $modelRedis->removeSearch();
        return true;
    }
    
    /*
     * Populate elastic
     *
     *
     */
    public function flush() {
        // no schema validation
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        // flush elastic schema
        try {
            $this->resource
                ->indices()
                ->delete(
                    ['index' => $this->schema
                        ->getName()
                    ]
                );
            
            return true;
        } catch(\Throwable $e) {
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
        //set the defaults
        $filter = [];
        $range = 50;
        $start = 0;
        $order = ['model_id' => 'asc'];
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

        //prepare the search object
        $search = [];


        //keyword search
        if (isset($data['q'])) {
            if (!is_array($data['q'])) {
                $data['q'] = [$data['q']];
            }

            foreach ($data['q'] as $keyword) {
                $search['query']['bool']['filter'][]['query_string'] = [
                    'query' => $keyword . '*',
                    'fields' => [
                        'model_singular','model_plural','model_detail',
                    ],
                    'default_operator' => 'AND'
                ];
            }
        }


        //generic full match filters

        //model_active
        if (!isset($filter['model_active'])) {
            $filter['model_active'] = 1;
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
                'index' => static::INDEX_NAME,
                'type' => static::INDEX_TYPE,
                'body' => $search,
                'size' => $range,
                'from' => $start
            ]);
        } catch (NoNodesAvailableException $e) {
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
`     *
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
