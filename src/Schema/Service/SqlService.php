<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Schema\Service;

use PDO as Resource;
use Cradle\Storm\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

use Cradle\Package\System\Schema;
use Cradle\Package\System\Exception;

/**
 * Model SQL Service
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christan Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class SqlService
{
    /**
     * @var AbstractSql|null $resource
     */
    protected $resource = null;

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
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Creates a table
     *
     * @param array $data
     * @param bool  $formatted
     *
     * @return array
     */
    public function create()
    {
        //queries to run
        $queries = [];

        //translate model data to sql data
        if (is_null($this->schema)) {
            throw Exception::forNoSchema();
        }

        $data = $this->schema->toSql();

        //determine the create schema
        $query = $this
            ->resource
            ->getCreateQuery($data['name'])
            ->addPrimaryKey($data['primary'])
            ->addField($data['primary'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED',
                'auto_increment' => true,
            ]);

        foreach ($data['columns'] as $name => $column) {
            $attributes = ['type' => $column['type']];

            if (isset($column['length'])) {
                $attributes['type'] .= '(' . $column['length'] . ')';
            }

            if (isset($column['default']) && strlen($column['default'])) {
                $attributes['default'] = $column['default'];
            } else if (!isset($column['required']) || !$column['required']) {
                $attributes['null'] = true;
            }

            if (isset($column['required']) && $column['required']) {
                $attributes['null'] = false;
            }

            if (isset($column['attribute']) && $column['attribute']) {
                $attributes['attribute'] = $column['attribute'];
            }

            $query->addField($name, $attributes);

            if (isset($column['index']) && $column['index']) {
                $query->addKey($name, [$name]);
            }

            if (isset($column['unique']) && $column['unique']) {
                $query->addUniqueKey($name, [$name]);
            }

            if (isset($column['primary']) && $column['primary']) {
                $query->addPrimaryKey($name);
            }
        }

        $queries[] = 'DROP TABLE IF EXISTS `' . $data['name'] . '`;';
        $queries[] = (string) $query;

        //determine the relation schema
        foreach ($data['relations'] as $table => $relation) {
            $query = $this->resource->getCreateQuery($table);

            $query->addPrimaryKey($relation['primary1']);
            $query->addField($relation['primary1'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED'
            ]);

            $query->addPrimaryKey($relation['primary2']);
            $query->addField($relation['primary2'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED'
            ]);

            $queries[] = 'DROP TABLE IF EXISTS `'. $table . '`;';
            $queries[] = (string) $query;
        }

        //execute queries
        $results = [];
        foreach ($queries as $query) {
            $results[] = [
                'query' => $query,
                'results' => $this->resource->query($query)
            ];
        }

        return $results;
    }

    /**
     * Returns the SQL resource
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Removes a table
     *
     * @param array $data
     *
     * @return array
     */
    public function remove($restorable = true)
    {

        //translate model data to sql data
        if (is_null($this->schema)) {
            throw Exception::forNoSchema();
        }

        $data = $this->schema->toSql();

        //queries to run
        $queries = [];

        // check if table is already archived
        if ($this->exists(sprintf('_%s', $data['name']))) {
            throw Exception::forSchemaArchiveExists($data['name']);
        }

        //if system exists
        if ($this->exists($data['name'])) {
            if ($restorable) {
                $queries[] = 'RENAME TABLE `' . $data['name'] . '` TO `_' . $data['name'] . '`;';

                //determine the relation schema
                foreach ($data['relations'] as $table => $relation) {
                    $queries[] = 'RENAME TABLE `' . $table . '` TO `_' . $table . '`;';
                }
            } else {
                $queries[] = 'DROP TABLE IF EXISTS `' . $data['name'] . '`;';
                $queries[] = 'DROP TABLE IF EXISTS `_' . $data['name'] . '`;';

                //determine the relation schema
                foreach ($data['relations'] as $table => $relation) {
                    $queries[] = 'DROP TABLE IF EXISTS `'. $table . '`;';
                    $queries[] = 'DROP TABLE IF EXISTS `_'. $table . '`;';
                }
            }
        }

        //execute queries
        $results = [];
        foreach ($queries as $query) {
            $results[] = [
                'query' => $query,
                'results' => $this->resource->query($query)
            ];
        }

        return $results;
    }

    /**
     * Restores a table
     *
     * @param array $data
     *
     * @return array
     */
    public function restore()
    {
        //translate model data to sql data
        if (is_null($this->schema)) {
            throw Exception::forNoSchema();
        }

        $data = $this->schema->toSql();

        //queries to run
        $queries = [];

        // check if table is already archived
        if ($this->exists($data['name'])) {
            throw Exception::forSchemaAlreadyExists($data['name']);
        }

        //if there's no system
        if (!$this->exists('_' . $data['name'])) {
            //go to create mode
            return $this->create($data, true);
        }

        $queries[] = 'RENAME TABLE `_' . $data['name'] . '` TO `' . $data['name'] . '`;';

        //determine the relation schema
        foreach ($data['relations'] as $table => $relation) {
            $queries[] = 'RENAME TABLE `_' . $table . '` TO `' . $table . '`;';
        }

        //execute queries
        $results = [];
        foreach ($queries as $query) {
            $results[] = [
                'query' => $query,
                'results' => $this->resource->query($query)
            ];
        }

        return $results;
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

    /**
     * Updates a table
     *
     * @param array $data
     * @param bool  $formatted
     *
     * @return array
     */
    public function update()
    {
        //translate model data to sql data
        if (is_null($this->schema)) {
            throw Exception::forNoSchema();
        }

        $data = $this->schema->toSql();

        //queries to run
        $queries = [];
        //this is used to determine whether to add/alter/remove columns
        $exists = [];

        //if there's no system
        if (!$this->exists($data['name'])) {
            //go to create mode
            return $this->create($data, true);
        }

        $primary = $this->resource->getPrimaryKey($data['name']);
        $columns = $this->resource->getColumns($data['name']);
        $query = $this->resource->getAlterQuery($data['name']);

        //remove or change fields
        foreach ($columns as $current) {
            //don't do primary
            if ($primary === $current['Field']) {
                continue;
            }

            $exists[] = $name = $current['Field'];

            //if there is no field in the data
            if (!isset($data['columns'][$name])) {
                $query->removeField($name);
                continue;
            }

            $column = $data['columns'][$name];

            $attributes = ['type' => $column['type']];

            if (isset($column['length'])) {
                $attributes['type'] .= '(' . $column['length'] . ')';
            }

            if (isset($column['default']) && strlen($column['default'])) {
                $attributes['default'] = $column['default'];
            } else if (!isset($column['required']) || !$column['required']) {
                $attributes['null'] = true;
            }

            if (isset($column['required']) && $column['required']) {
                $attributes['null'] = false;
            }

            if (isset($column['attribute']) && $column['attribute']) {
                $attributes['attribute'] = $column['attribute'];
            }

            $default = null;
            if (isset($attributes['default'])) {
                $default = $attributes['default'];
            }

            //if all matches
            if ($attributes['type'] === $current['Type']
                && $attributes['null'] == ($current['Null'] === 'YES')
                && $default === $current['Default']
            ) {
                continue;
            }

            //do the alter
            $query->changeField($name, $attributes);
        }

        //add fields
        foreach ($data['columns'] as $name => $column) {
            if (in_array($name, $exists)) {
                continue;
            }

            $attributes = ['type' => $column['type']];

            if (isset($column['length'])) {
                $attributes['type'] .= '(' . $column['length'] . ')';
            }

            if (isset($column['default']) && strlen($column['default'])) {
                $attributes['default'] = $column['default'];
            } else if (!isset($column['required']) || !$column['required']) {
                $attributes['null'] = true;
            }

            if (isset($column['required']) && $column['required']) {
                $attributes['null'] = false;
            }

            if (isset($column['attribute']) && $column['attribute']) {
                $attributes['attribute'] = $column['attribute'];
            }

            $query->addField($name, $attributes);

            if (isset($column['index']) && $column['index']) {
                $query->addKey($name, [$name]);
            }

            if (isset($column['unique']) && $column['unique']) {
                $query->addUniqueKey($name, [$name]);
            }

            if (isset($column['primary']) && $column['primary']) {
                $query->addPrimaryKey($name);
            }
        }

        $query = (string) $query;
        if ($query !== 'ALTER TABLE `' . $data['name'] . '` ;') {
            $queries[] = $query;
        }

        $installed = $this->resource->getTables($data['name'] . '_%');
        $relations = array_keys($data['relations']);

        //determine the relation tables that need to be removed
        foreach ($installed as $relation) {
            //uninstall if it's not in the schema
            if (!in_array($relation, $relations)) {
                $queries[] = 'DROP TABLE IF EXISTS `' . $relation . '`;';
            }
        }

        //determine the relation tables that need to be added
        foreach ($data['relations'] as $table => $relation) {
            //install if it's installed
            if (in_array($table, $installed)) {
                continue;
            }

            $query = $this->resource->getCreateQuery($table);

            $query->addPrimaryKey($relation['primary1']);
            $query->addField($relation['primary1'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED'
            ]);

            $query->addPrimaryKey($relation['primary2']);
            $query->addField($relation['primary2'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED'
            ]);

            $queries[] = 'DROP TABLE IF EXISTS `'. $table . '`;';
            $queries[] = (string) $query;
        }

        //execute queries
        $results = [];
        foreach ($queries as $query) {
            $results[] = [
                'query' => $query,
                'results' => $this->resource->query($query)
            ];
        }

        return $results;
    }

    /**
     * Returns true if the system exists in the database
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists($name)
    {
        $system = $this->resource->getTables($name);
        return !empty($system);
    }

    /**
     * Returns all the table record count
     *
     * @return array
     */
    public function getSchemaTableRecordCount($schema = null)
    {
        // information schema query
        $query = sprintf(
            'SELECT table_name, table_rows FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = "%s"',
            $schema
        );

        // send query
        return $this->resource->query($query);
    }
}
