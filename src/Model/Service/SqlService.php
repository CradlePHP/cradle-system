<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Model\Service;

use PDO as Resource;
use Cradle\Storm\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

use Cradle\Package\System\Schema;
use Cradle\Package\System\Exception as SystemException;

use Cradle\Storm\Search;

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
     * @const int DEFAULT_RANGE
     */
    const DEFAULT_RANGE = 50;

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
     * Create in database
     *
     * @param *array $object
     * @param *array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getName();
        $created = $this->schema->getCreatedFieldName();
        $updated = $this->schema->getUpdatedFieldName();
        $ipaddress = $this->schema->getIPAddressFieldName();

        if ($created) {
            $data[$created] = date('Y-m-d H:i:s');
        }

        if ($updated) {
            $data[$updated] = date('Y-m-d H:i:s');
        }

        if ($ipaddress && isset($_SERVER['REMOTE_ADDR'])) {
            $data[$ipaddress] = $_SERVER['REMOTE_ADDR'];
        }

        $uuids = $this->schema->getUuidFieldNames();

        foreach ($uuids as $uuid) {
            $data[$uuid] = sha1(uniqid());
        }

        return $this
            ->resource
            ->model($data)
            ->save($table)
            ->get();
    }

    /**
     * Checks to see if unique.0 already exists
     *
     * @param *string $objectKey
     *
     * @return bool
     */
    public function exists($key, $value)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $search = $this
            ->resource
            ->search($this->schema->getName())
            ->addFilter($key . ' = %s', $value);

        return !!$search->getRow();
    }

    /**
     * Get detail from database
     *
     * @param *array $object
     * @param *int   $id
     *
     * @return array
     */
    public function get($key, $id = null)
    {
        if ($key instanceof Search) {
            $search = $key;
        } else {
            $search = $this->getQuery($key, $id);
        }

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        //we need to update the real key and id
        $key = $this->schema->getPrimaryFieldName();
        $id = $results[$key];

        $jsonFields = $this->schema->getAllJsonFieldNames();
        foreach ($jsonFields as $field) {
            if (!isset($results[$field])) {
                continue;
            }

            if ($results[$field]) {
                $results[$field] = json_decode($results[$field], true);
            } else {
                $results[$field] = [];
            }
        }

        //get 1:0 relations
        $results = $this->getWithOptionalRelations($id, $results);

        //get 1:N, N:N relations
        $results = $this->getWithManyRelations($id, $results);

        //custom formats & formulas
        foreach ($this->schema->getFields() as $field) {
            if ($field['detail']['format'] === 'formula') {
                $helper = cradle('global')
                    ->handlebars()
                    ->getHelper('formula');

                $results[$field['name']] = $helper(
                    $field['detail']['parameters'],
                    $results,
                    false
                );
            }

            if ($field['detail']['format'] === 'custom') {
                $helper = cradle('global')
                    ->handlebars()
                    ->getHelper('compile');

                $results[$field['name']] = $helper(
                    $field['detail']['parameters'],
                    $results
                );
            }
        }

        return $results;
    }

    /**
     * Get detail from database
     *
     * @param *array $object
     * @param *int   $id
     *
     * @return Search
     */
    public function getDetailQuery($key, $id)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $search = $this
            ->resource
            ->search($this->schema->getName())
            ->addFilter($key . ' = %s', $id);

        //get 1:1 relations
        $relations = $this->schema->getRelations(1);

        foreach ($relations as $table => $relation) {
            $search
                ->innerJoinUsing(
                    $table,
                    $relation['primary1']
                )
                ->innerJoinUsing(
                    $relation['name'],
                    $relation['primary2']
                );
        }

        return $search;
    }

    /**
     * Search in database
     *
     * @param array  $data
     *
     * @return Search
     */
    public function getSearchQuery(array $data = [])
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getName();
        $search = $this
            ->resource
            ->search($table)
            ->setRange(self::DEFAULT_RANGE);

        if (isset($data['columns']) && $data['columns']) {
            $search->setColumns($data['columns']);
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $search->setRange($data['range']);
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $search->setStart($data['start']);
        }

        // collect searchable
        $searchable = $this->schema->getSearchableFieldNames();
        // collect jsons
        $jsons = $this->schema->getJsonFieldNames();

        //consider forward relations
        $this->searchWithForwardRelations($search, $data, $searchable);

        //consider reverse relations
        $this->searchWithReverseRelations($search, $data, $searchable);

        //now deal with post_post
        $this->searchWithCircularRelations($search, $data);

        //add filters
        $this->searchWhereFilter($search, $data, $jsons);

        //add like filters
        $this->searchWhereLike($search, $data, $jsons);

        // add Null filters
        $this->searchWhereNull($search, $data, $jsons);

        // add Not Null filters
        $this->searchWhereNotNull($search, $data, $jsons);

        // add in filters
        $this->searchWhereIn($search, $data, $jsons);

        //add spans
        $this->searchWhereSpan($search, $data, $jsons);

        //keyword?
        $this->searchWhereKeyword($search, $data, $searchable);

        //add sorting
        $this->searchWithSorting($search, $data, $jsons);

        //add grouping
        $this->searchWithGrouping($search, $data);

        return $search;
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
     * Links table to another table
     *
     * @param *string $relation
     * @param *int    $primary1
     * @param *int    $primary2
     *
     * @return array
     */
    public function link($relation, $primary1, $primary2)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $name = $this->schema->getName();
        $relations = $this->schema->getRelations();
        $table = $name . '_' . $relation;

        if (!isset($relations[$table])) {
            throw SystemException::forNoRelation($name, $relation);
        }

        $relation = $relations[$table];

        $model = $this->resource->model();
        $model[$relation['primary1']] = $primary1;
        $model[$relation['primary2']] = $primary2;

        return $model->insert($table);
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *array $object
     * @param *int $id
     */
    public function remove($id)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getName();
        $primary = $this->schema->getPrimaryFieldName();
        //please rely on SQL CASCADING ON DELETE
        $model = $this->resource->model();
        $model[$primary] = $id;
        return $model->remove($table);
    }

    /**
     * Search in database
     *
     * @param array  $data
     *
     * @return array
     */
    public function search($data = [])
    {
        if ($data instanceof Search) {
            $search = $data;
        } else {
            if (!is_array($data)) {
                $data = [];
            }

            $search = $this->searchQuery($data);
        }

        $rows = $search->getRows();
        $jsonFields = $this->schema->getAllJsonFieldNames();

        foreach ($rows as $i => $results) {
            foreach ($jsonFields as $field) {
                if (!isset($results[$field])) {
                    continue;
                }

                if ($results[$field]) {
                    $rows[$i][$field] = json_decode($results[$field], true);
                } else {
                    $rows[$i][$field] = [];
                }
            }

            //custom formats & formulas
            foreach ($this->schema->getFields() as $field) {
                if ($field['detail']['format'] === 'formula') {
                    $helper = cradle('global')
                        ->handlebars()
                        ->getHelper('formula');

                    $rows[$i][$field['name']] = $helper(
                        $field['detail']['parameters'],
                        $results,
                        false
                    );

                    continue;
                }

                if ($field['detail']['format'] === 'custom') {
                    $helper = cradle('global')
                        ->handlebars()
                        ->getHelper('compile');

                    $rows[$i][$field['name']] = $helper(
                        $field['detail']['parameters'],
                        $results
                    );

                    continue;
                }
            }
        }

        //return response format
        $response =  [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];

        return $response;
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
     * Unlinks table to another table
     *
     * @param *string $relation
     * @param *int    $primary1
     * @param *int    $primary2
     *
     * @return array
     */
    public function unlink($relation, $primary1, $primary2)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $name = $this->schema->getName();
        $relations = $this->schema->getRelations();
        $table = $name . '_' . $relation;

        if (!isset($relations[$table])) {
            throw SystemException::forNoRelation($name, $relation);
        }

        $relation = $relations[$table];

        $model = $this->resource->model();
        $model[$relation['primary1']] = $primary1;
        $model[$relation['primary2']] = $primary2;

        return $model->remove($table);
    }

    /**
     * Unlinks the parent from its children
     *
     * @param *string $relation
     * @param *int    $primary
     *
     * @return array
     */
    public function unlinkAllChildren($relation, $primary)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $name = $this->schema->getName();
        $relations = $this->schema->getRelations();
        $table = $name . '_' . $relation;

        if (!isset($relations[$table])) {
            throw SystemException::forNoRelation($name, $relation);
        }

        $relation = $relations[$table];

        $results = $this
            ->resource
            ->search($table)
            ->addFilter(sprintf('%s = %%s', $relation['primary1']), $primary)
            ->getCollection()
            ->get();

        $this->resource->deleteRows($table, sprintf(
            '%s = %s',
            $relation['primary1'],
            $primary
        ));

        return $results;
    }

    /**
     * Unlinks children from its parents
     *
     * @param *string $relation
     * @param *int    $primary
     *
     * @return array
     */
    public function unlinkAllParents($relation, $primary)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $name = $this->schema->getName();
        $relations = $this->schema->getRelations();
        $table = $name . '_' . $relation;

        if (!isset($relations[$table])) {
            throw SystemException::forNoRelation($name, $relation);
        }

        $relation = $relations[$table];

        $results = $this
            ->resource
            ->search($table)
            ->addFilter(sprintf('%s = %%s', $relation['primary2']), $primary)
            ->getCollection()
            ->get();

        $this->resource->deleteRows($table, sprintf(
            '%s = %s',
            $relation['primary2'],
            $primary
        ));

        return $results;
    }

    /**
     * Update to database
     *
     * @param *array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getName();
        $updated = $this->schema->getUpdatedFieldName();

        if ($updated) {
            $data[$updated] = date('Y-m-d H:i:s');
        }

        return $this
            ->resource
            ->model($data)
            ->save($table)
            ->get();
    }

    /**
     * Add Optional Relations to the Details
     *
     * @param *integer $id
     * @param array    $results
     *
     * @return array
     */
    protected function getWithOptionalRelations(int $id, array $results = []): array
    {
        $relations = $this->schema->getRelations(0);
        foreach ($relations as $table => $relation) {
            $relationSchema = Schema::i($relation['name']);
            $primaryName = $relationSchema->getPrimaryFieldName();
            $row = $this
                ->resource
                ->search($table)
                ->innerJoinOn($relation['name'], sprintf(
                    '%s.%s=%s.%s',
                    $relation['name'],
                    $primaryName,
                    $table,
                    $relation['primary2']
                ))
                ->addFilter($relation['primary1'] . ' = %s', $id)
                ->getRow();

            $jsonFields = Schema::i($relation['name'])->getJsonFieldNames();
            foreach ($jsonFields as $field) {
                if (!isset($row[$field])) {
                    continue;
                }

                if ($row[$field]) {
                    $row[$field] = json_decode($row[$field], true);
                } else {
                    $row[$field] = [];
                }
            }

            $results[$relation['name']] = $row;
        }

        return $results;
    }

    /**
     * Add Many Relations to the Details
     *
     * @param *integer $id
     * @param array    $results
     *
     * @return array
     */
    protected function getWithManyRelations(int $id, array $results = []): array
    {
        //get 1:N, N:N relations
        $relations = array_merge(
            $this->schema->getRelations(2),
            $this->schema->getRelations(3)
        );

        foreach ($relations as $table => $relation) {
            $schema = $this->schema;
            //we need to case for post_post for example
            $isCircular = $relation['name'] === $this->schema->getName();

            //this is the normal way
            $manyJoin = function () use (&$relation) {
                $this->innerJoinUsing($relation['name'], $relation['primary2']);

                $relationSchema = Schema::i($relation['name']);
                $relationRelations = $relationSchema->getRelations(1);

                foreach ($relationRelations as $table2 => $relation2) {
                    $this
                        ->innerJoinUsing(
                            $table2,
                            $relation2['primary1']
                        )
                        ->innerJoinUsing(
                            $relation2['name'],
                            $relation2['primary2']
                        );
                }
            };

            //this is the post_post way
            $circularJoin = function () use (&$schema, &$relation) {
                $on = sprintf(
                    '%s = %s',
                    $schema->getPrimaryFieldName(),
                    $relation['primary2']
                );
                $this->innerJoinOn($relation['name'], $on);
            };

            $rows = $this
                ->resource
                ->search($table)
                ->when($isCircular, $circularJoin, $manyJoin)
                ->addFilter($relation['primary1'] . ' = %s', $id)
                ->getRows();

            $jsonFields = Schema::i($relation['name'])->getJsonFieldNames();

            foreach ($rows as $i => $row) {
                foreach ($jsonFields as $field) {
                    if (!isset($row[$field])) {
                        continue;
                    }

                    if ($row[$field]) {
                        $row[$field] = json_decode($row[$field], true);
                    } else {
                        $row[$field] = [];
                    }
                }

                //custom formats & formulas
                $fields = Schema::i($relation['name'])->getFields();

                foreach ($fields as $field) {
                    if ($field['detail']['format'] === 'formula') {
                        $helper = cradle('global')
                            ->handlebars()
                            ->getHelper('formula');

                        $row[$field['name']] = $helper(
                            $field['detail']['parameters'],
                            $row,
                            false
                        );

                        continue;
                    }

                    if ($field['detail']['format'] === 'custom') {
                        $helper = cradle('global')
                            ->handlebars()
                            ->getHelper('compile');

                        $row[$field['name']] = $helper(
                            $field['detail']['parameters'],
                            $row
                        );

                        continue;
                    }
                }

                $rows[$i] = $row;
            }

            $results[$relation['name']] = $rows;
        }

        return $results;
    }

    /**
     * Adds filters to the where clause
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $jsons
     *
     * @return SqlService
     */
    protected function searchWhereFilter(Search $search, array $data, array $jsons): SqlService
    {
        $active = $this->schema->getActiveFieldName();
        if ($active && !isset($data['filter'][$active])) {
            $data['filter'][$active] = 1;
        }

        //if they want both the active and inactive
        if (isset($data['filter'][$active]) && $data['filter'][$active] == -1) {
            unset($data['filter'][$active]);
        }

        $circular = $this->schema->getRelations($this->schema->getName());
        //if there is a post_post and they are trying to filter by it
        if (!empty($circular) && isset($data['filter'][$circular['primary']])) {
            //they mean to filter by the parent
            $data['filter'][$circular['primary1']] = $data['filter'][$circular['primary']];

            //remove the old
            unset($data['filter'][$circular['primary']]);
        }

        if (!isset($data['filter']) || !is_array($data['filter'])) {
            return $this;
        }

        //add filters
        foreach ($data['filter'] as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
                continue;
            }

            //by chance is it a json filter?
            if (strpos($column, '.') === false) {
                continue;
            }

            //get the name
            $name = substr($column, 0, strpos($column, '.'));
            //name should be a json column type
            if (!in_array($name, $jsons)) {
               continue;
            }

            //this is like product_attributes.HDD
            $path = substr($column, strpos($column, '.'));
            $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
            $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
            $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
            $search->addFilter($column . ' = %s', $value);
        }

        return $this;
    }

    /**
     * Adds LIKE to the where clause
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $jsons
     *
     * @return SqlService
     */
    protected function searchWhereLike(Search $search, array $data, array $jsons): SqlService
    {
        if (!isset($data['like']) || !is_array($data['like'])) {
            return $this;
        }

        foreach ($data['like'] as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' LIKE %s', '%' . $value . '%');
                continue;
            }

            //by chance is it a json filter?
            if (strpos($column, '.') === false) {
                continue;
            }

            //get the name
            $name = substr($column, 0, strpos($column, '.'));
            //name should be a json column type
            if (!in_array($name, $jsons)) {
               continue;
            }

            //this is like product_attributes.HDD
            $path = substr($column, strpos($column, '.'));
            $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
            $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
            $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
            $search->addFilter($column . ' LIKE %s', '%' . $value . '%');
        }

        return $this;
    }

    /**
     * Adds IS NULL to the where clause
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $jsons
     *
     * @return SqlService
     */
    protected function searchWhereNull(Search $search, array $data, array $jsons): SqlService
    {
        if (!isset($data['null']) || !is_array($data['null'])) {
            return $this;
        }

        foreach ($data['null'] as $column) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' IS NULL');
                continue;
            }

            //by chance is it a json filter?
            if (strpos($column, '.') === false) {
                continue;
            }

            //get the name
            $name = substr($column, 0, strpos($column, '.'));
            //name should be a json column type
            if (!in_array($name, $jsons)) {
               continue;
            }

            //this is like product_attributes.HDD
            $path = substr($column, strpos($column, '.'));
            $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
            $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
            $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
            $search->addFilter($column . ' IS NULL');
        }

        return $this;
    }

    /**
     * Adds IS NOT NULL to the where clause
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $jsons
     *
     * @return SqlService
     */
    protected function searchWhereNotNull(Search $search, array $data, array $jsons): SqlService
    {
        if (!isset($data['notnull']) || !is_array($data['notnull'])) {
            return $this;
        }

        foreach ($data['notnull'] as $column) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' IS NOT NULL');
                continue;
            }

            //by chance is it a json filter?
            if (strpos($column, '.') === false) {
                continue;
            }

            //get the name
            $name = substr($column, 0, strpos($column, '.'));
            //name should be a json column type
            if (!in_array($name, $jsons)) {
               continue;
            }

            //this is like product_attributes.HDD
            $path = substr($column, strpos($column, '.'));
            $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
            $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
            $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
            $search->addFilter($column . ' IS NOT NULL');
        }

        return $this;
    }

    /**
     * Adds in array to the where clause
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $jsons
     *
     * @return SqlService
     */
    protected function searchWhereIn(Search $search, array $data, array $jsons): SqlService
    {
        if (!isset($data['in']) || !is_array($data['in'])) {
            return $this;
        }

        foreach ($data['in'] as $column => $values) {
            // values should be array
            if (!is_array($values)) {
                $values = [$values];
            }

            //this is like if an array has one of items in another array
            // eg. if product_tags has one of these values [foo, bar, etc.]
            if (in_array($column, $jsons)) {
                $or = [];
                $where = [];
                foreach ($values as $value) {
                    $where[] = "JSON_SEARCH(LOWER($column), 'one', %s) IS NOT NULL";
                    $or[] = '%' . strtolower($value) . '%';
                }

                array_unshift($or, '(' . implode(' OR ', $where) . ')');
                call_user_func([$search, 'addFilter'], ...$or);
                continue;
            }

            //this is the normal
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' IN ("' . implode('", "', $values) . '")');
                continue;
            }

            //by chance is it a json filter?
            if (strpos($column, '.') === false) {
                continue;
            }

            //get the name
            $name = substr($column, 0, strpos($column, '.'));
            //name should be a json column type
            if (!in_array($name, $jsons)) {
               continue;
            }

            //this is like product_attributes.HDD has
            //one of these values [foo, bar, etc.]
            $path = substr($column, strpos($column, '.'));
            $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
            $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
            $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);

            $or = [];
            $where = [];
            foreach ($values as $value) {
                $where[] = $column . ' = %s';
                $or[] = $value;
            }

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        return $this;
    }

    /**
     * Adds spans to the where clause
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $jsons
     *
     * @return SqlService
     */
    protected function searchWhereSpan(Search $search, array $data, array $jsons): SqlService
    {
        if (!isset($data['span']) || !is_array($data['span'])) {
            return $this;
        }

        //add spans
        foreach ($data['span'] as $column => $value) {
            if (!is_array($value) || empty($value)) {
                continue;
            }

            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                // minimum?
                if (isset($value[0]) && !empty($value[0])) {
                    $search->addFilter($column . ' >= %s', $value[0]);
                }

                // maximum?
                if (isset($value[1]) && !empty($value[1])) {
                    $search->addFilter($column . ' <= %s', $value[1]);
                }

                continue;
            }

            //by chance is it a json filter?
            if (strpos($column, '.') === false) {
                continue;
            }

            //get the name
            $name = substr($column, 0, strpos($column, '.'));
            //name should be a json column type
            if (!in_array($name, $jsons)) {
               continue;
            }

            //this is like product_attributes.HDD
            $path = substr($column, strpos($column, '.'));
            $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
            $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
            $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);

            // minimum?
            if (isset($value[0]) && !empty($value[0])) {
                $search->addFilter($column . ' >= %s', $value[0]);
            }

            // maximum?
            if (isset($value[1]) && !empty($value[1])) {
                $search->addFilter($column . ' <= %s', $value[1]);
            }
        }

        return $this;
    }

    /**
     * Adds keywords to the where clause
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $searchable
     *
     * @return SqlService
     */
    protected function searchWhereKeyword(Search $search, array $data, array $searchable): SqlService
    {
        if (!isset($data['q'])) {
            return $this;
        }

        if (empty($searchable)) {
            return $this;
        }

        if (!is_array($data['q'])) {
            $data['q'] = [$data['q']];
        }

        foreach ($data['q'] as $keyword) {
            $or = [];
            $where = [];
            foreach ($searchable as $name) {
                $where[] = 'LOWER(' . $name . ') LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
            }

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        return $this;

    }

    /**
     * Adds sorting to the search
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $jsons
     *
     * @return SqlService
     */
    protected function searchWithSorting(Search $search, array $data, array $jsons): SqlService
    {
        if (!isset($data['order']) || !is_array($data['order'])) {
            return $this;
        }

        foreach ($data['order'] as $column => $direction) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addSort($column, $direction);
                continue;
            }

            //by chance is it a json filter?
            if (strpos($column, '.') === false) {
                continue;
            }

            //get the name
            $name = substr($column, 0, strpos($column, '.'));
            //name should be a json column type
            if (!in_array($name, $jsons)) {
               continue;
            }

            //this is like product_attributes.HDD
            $path = substr($column, strpos($column, '.'));
            $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
            $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);

            $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
            $search->addSort($column, $direction);
        }

        return $this;
    }

    /**
     * Adds grouping to the search
     *
     * @param *Search $data
     * @param *array  $data
     *
     * @return SqlService
     */
    protected function searchWithGrouping(Search $search, array $data): SqlService
    {
        if (isset($data['group'])) {
            if (!is_array($data['group'])) {
                $data['group'] = [$data['group']];
            }

            // TODO: add the regex specified in order BUT
            // allow insert of mysql defined functions like
            // DATE_FORMAT(column, '%Y') etc
            foreach ($data['group'] as $group) {
                $search->groupBy($group);
            }
        }

        $columns = ['*'];
        if (isset($data['columns'])) {
            if (is_array($data['columns'])) {
                $columns = $data['columns'];
            } else if (is_string($data['columns']) && trim($data['columns'])) {
                $columns = [ $data['columns'] ];
            }
        }

        if (isset($data['sum']) && preg_match('/^[a-zA-Z0-9-_]+$/', $data['sum'])) {
            $columns = [sprintf('sum(%s) as total', $data['sum'])];
        }

        if (isset($data['count']) && preg_match('/^[a-zA-Z0-9-_]+$/', $data['count'])) {
            $columns = [sprintf('count(%s) as count', $data['count'])];
        }

        // if there is grouping and there is a sum or count field,
        // we will assume that this is not a global thing

        $search->setColumns($columns);

        return $this;
    }

    /**
     * Adds forward relations to the search
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $searchable
     *
     * @return SqlService
     */
    protected function searchWithForwardRelations(Search $search, array $data, array &$searchable): SqlService
    {
        //consider forward relations
        $table = $this->schema->getName();
        $relations = $this->schema->getRelations();
        foreach ($relations as $joinTable => $relation) {
            //deal with post_post at a later time
            if ($table === $relation['name']) {
                continue;
            }

            //1:1
            if ($relation['many'] == 1) {
                $search
                    ->innerJoinUsing($joinTable, $relation['primary1'])
                    ->innerJoinUsing($relation['name'], $relation['primary2']);

                //add to searchable
                $searchable = array_merge($searchable, $relation['searchable']);
                continue;
            }

            //needs to have a filter to add the other kinds of joins
            $hasFilters = isset($data['filter'][$relation['primary2']])
                || isset($data['in'][$relation['primary2']]);

            if (!$hasFilters) {
                continue;
            }

            //1:0, 1:N, N:N
            $search
                ->innerJoinUsing($joinTable, $relation['primary1'])
                ->innerJoinUsing($relation['name'], $relation['primary2']);

            //add to searchable
            $searchable = array_merge($searchable, $relation['searchable']);
        }

        return $this;
    }

    /**
     * Adds reverse relations to the search
     *
     * @param *Search $data
     * @param *array  $data
     * @param *array  $searchable
     *
     * @return SqlService
     */
    protected function searchWithReverseRelations(Search $search, array $data, array &$searchable): SqlService
    {
        $relations = $this->schema->getReverseRelations();
        foreach ($relations as $joinTable => $relation) {
            //deal with post_post at a later time
            if ($relation['source']['name'] === $relation['name']) {
                continue;
            }

            $hasFilters = isset($data['filter'][$relation['primary1']])
                || isset($data['in'][$relation['primary1']]);

            //if filter primary is not set
            if (!$hasFilters) {
                continue;
            }

            $search
                ->innerJoinUsing($joinTable, $relation['primary2'])
                ->innerJoinUsing($relation['source']['name'], $relation['primary1']);

            //add to searchable
            $searchable = array_merge($searchable, $relation['searchable']);
        }

        return $this;
    }

    /**
     * Adds circular relations to the search
     *
     * @param *Search $data
     * @param *array  $data
     *
     * @return SqlService
     */
    protected function searchWithCircularRelations(Search $search, array $data): SqlService
    {
        $circular = $this->schema->getRelations($this->schema->getName());
        //if there is a post_post and they are trying to filter by it
        if (empty($circular) || !isset($data['filter'][$circular['primary']])) {
            return $this;
        }

        //now add the join
        $search->innerJoinOn(
            $circular['table'],
            sprintf(
                '%s = %s',
                $circular['primary'],
                $circular['primary2']
            )
        );
    }

    /**
     * Deprecation Notice: This will be removed by 3.0.0
     *
     * @param array $data
     *
     * @return Search
     */
    public function searchQuery(array $data = [])
    {
        return $this->getSearchQuery($data);
    }

    /**
     * Deprecation Notice: This will be removed by 3.0.0
     *
     * @param *array $object
     * @param *int   $id
     *
     * @return Search
     */
    public function getQuery($key, $id)
    {
        return $this->getDetailQuery($key, $id);
    }
}
