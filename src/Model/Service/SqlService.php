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

        if ($search->jsonFields) {
            foreach ($search->jsonFields as $field) {
                if (isset($results[$field]) && $results[$field]) {
                    $results[$field] = json_decode($results[$field], true);
                } else {
                    $results[$field] = [];
                }
            }
        }

        //get 1:0 relations
        $relations = $this->schema->getRelations(0);
        foreach ($relations as $table => $relation) {
            $row = $this
                ->resource
                ->search($table)
                ->innerJoinUsing($relation['name'], $relation['primary2'])
                ->addFilter($relation['primary1'] . ' = %s', $id)
                ->getRow();

            $fields = Schema::i($relation['name'])->getJsonFieldNames();

            foreach ($fields as $field) {
                if (isset($row[$field]) && trim($row[$field])) {
                    $row[$field] = json_decode($row[$field], true);
                } else {
                    $row[$field] = [];
                }
            }

            $results[$relation['name']] = $row;
        }

        //get 1:N, N:N relations
        $relations = array_merge(
            $this->schema->getRelations(2),
            $this->schema->getRelations(3)
        );

        foreach ($relations as $table => $relation) {
            $schema = $this->schema;
            $rows = $this
                ->resource
                ->search($table)
                ->when(
                    //we need to case for post_post for example
                    $relation['name'] === $this->schema->getName(),
                    //this is the post_post way
                    function () use (&$schema, &$relation) {
                        $on = sprintf(
                            '%s = %s',
                            $schema->getPrimaryFieldName(),
                            $relation['primary2']
                        );
                        $this->innerJoinOn($relation['name'], $on);
                    },
                    //this is the normal way
                    function () use (&$relation, &$fields) {
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

                            $relatedJson = $relationSchema->getJsonFieldNames();
                            $fields = array_merge($fields, $relatedJson);
                        }
                    }
                )
                ->addFilter($relation['primary1'] . ' = %s', $id)
                ->getRows();

            $fields = Schema::i($relation['name'])->getJsonFieldNames();

            foreach ($rows as $i => $row) {
                foreach ($fields as $field) {
                    if (isset($row[$field]) && trim($row[$field])) {
                        $row[$field] = json_decode($row[$field], true);
                    } else {
                        $row[$field] = [];
                    }
                }

                //custom formats & formulas
                foreach (Schema::i($relation['name'])->getFields() as $field) {
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
    public function getQuery($key, $id)
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $search = $this
            ->resource
            ->search($this->schema->getName())
            ->addFilter($key . ' = %s', $id);

        // get json fields
        $fields = $this->schema->getJsonFieldNames();

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

            $relatedJson = Schema::i($relation['name'])->getJsonFieldNames();
            $fields = array_merge($fields, $relatedJson);
        }

        //add JSON fields
        $search->jsonFields = $fields;

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

        foreach ($rows as $i => $results) {
            if (isset($search->jsonFields)) {
                foreach ($search->jsonFields as $field) {
                    if (isset($results[$field]) && $results[$field]) {
                        $rows[$i][$field] = json_decode($results[$field], true);
                    } else {
                        $rows[$i][$field] = [];
                    }
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

        // if there's no grouping, then sum it all up
        if ($sum && !$groups) {
            $total = $search
                ->setColumns($sum)
                ->getRow();

            $response['sum_field'] = $total['total'] ? $total['total'] : 0;
        }

        return $response;
    }

    /**
     * Search in database
     *
     * @param array  $data
     *
     * @return Search
     */
    public function searchQuery(array $data = [])
    {
        if (is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $sum = null;
        $count = null;
        $columns = [];
        $groups = [];
        $filter = [];
        $like = [];
        $in = [];
        $json = [];
        $span  = [];
        $range = 50;
        $start = 0;
        $order = [];

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['like']) && is_array($data['like'])) {
            $like = $data['like'];
        }

        if (isset($data['in']) && is_array($data['in'])) {
            $in = $data['in'];
        }

        if (isset($data['span']) && is_array($data['span'])) {
            $span = $data['span'];
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

        if (isset($data['sum']) && !empty($data['sum'])) {
            $sum = sprintf('sum(%s) as total', $data['sum']);
        }

        if (isset($data['count']) && !empty($data['count'])) {
            $count = sprintf('count(%s) as count', $data['count']);
        }

        if (isset($data['group']) && !is_array($data['group'])) {
            $groups = [$data['group']];
        }

        if (isset($data['group']) && is_array($data['group'])) {
            $groups = $data['group'];
        }

        if (isset($data['columns']) && is_array($data['columns'])) {
            $columns = $data['columns'];
        }

        $active = $this->schema->getActiveFieldName();
        if ($active && !isset($filter[$active])) {
            $filter[$active] = 1;
        }

        //if they want both the active and inactive
        if (isset($filter[$active]) && $filter[$active] == -1) {
            unset($filter[$active]);
        }

        $table = $this->schema->getName();
        $search = $this->resource
            ->search($table)
            ->setStart($start);

        if ($range) {
            $search->setRange($range);
        }

        // collect searchable
        $searchable = $this->schema->getSearchableFieldNames();

        // collect json fields
        $fields = $this->schema->getJsonFieldNames();

        //consider forward relations
        $relations = $this->schema->getRelations();
        foreach ($relations as $joinTable => $relation) {
            //deal with post_post at a later time
            if ($table === $relation['name']) {
                continue;
            }

            //1:1
            if ($relation['many'] == 1) {
                $search
                    ->innerJoinUsing(
                        $joinTable,
                        $relation['primary1']
                    )
                    ->innerJoinUsing(
                        $relation['name'],
                        $relation['primary2']
                    );

                //add to searchable
                $searchable = array_merge($searchable, $relation['searchable']);
            //needs to have a filter to add the other kinds of joins
            } else if (!isset($filter[$relation['primary2']])
                && !isset($in[$relation['primary2']])
            ) {
                continue;
            //1:0, 1:N, N:N
            } else {
                $search
                    ->innerJoinUsing(
                        $joinTable,
                        $relation['primary1']
                    )
                    ->innerJoinUsing(
                        $relation['name'],
                        $relation['primary2']
                    );
            }

            $relatedJson = Schema::i($relation['name'])->getJsonFieldNames();
            $fields = array_merge($fields, $relatedJson);
        }

        //consider reverse relations
        $relations = $this->schema->getReverseRelations();
        foreach ($relations as $joinTable => $relation) {
            //deal with post_post at a later time
            if ($relation['source']['name'] === $relation['name']) {
                continue;
            }

            //if filter primary is not set
            if (!isset($filter[$relation['primary1']])
                && !isset($in[$relation['primary1']])
            ) {
                continue;
            }

            $search
                ->innerJoinUsing(
                    $joinTable,
                    $relation['primary2']
                )
                ->innerJoinUsing(
                    $relation['source']['name'],
                    $relation['primary1']
                );

            $relatedJson = Schema::i($relation['source']['name'])->getJsonFieldNames();
            $fields = array_merge($fields, $relatedJson);
        }

        //now deal with post_post
        $circular = $this->schema->getRelations($this->schema->getName());
        //if there is a post_post and they are trying to filter by it
        if (!empty($circular) && isset($filter[$circular['primary']])) {
            //they mean to filter by the parent
            $filter[$circular['primary1']] = $filter[$circular['primary']];

            //remove the old
            unset($filter[$circular['primary']]);

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

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
                continue;
            }

            //by chance is it a json filter?
            if (preg_match('/^[a-zA-Z0-9-_\.]+$/', $column)) {
                $name = substr($column, 0, strpos($column, '.'));
                $path = substr($column, strpos($column, '.'));
                $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);

                //it should be a json column type
                if (!in_array($name, $this->schema->getJsonFieldNames())) {
                    continue;
                }

                $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);

                $search->addFilter($column . ' = %s', $value);
                continue;
            }
        }

        //add like filters
        foreach ($like as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' LIKE %s', '%' . $value . '%');
                continue;
            }

            //by chance is it a json filter?
            if (preg_match('/^[a-zA-Z0-9-_\.]+$/', $column)) {
                $name = substr($column, 0, strpos($column, '.'));
                $path = substr($column, strpos($column, '.'));
                $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);

                 //it should be a json column type
                if (!in_array($name, $this->schema->getJsonFieldNames())) {
                    continue;
                }

                $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
                $search->addFilter($column . ' = %s', $value);
                continue;
            }
        }

        // add in filters
        foreach ($in as $column => $values) {
            // values should be array
            if (!is_array($values)) {
                $values = [$values];
            }

            //if it's a json column type
            if (in_array($column, $this->schema->getJsonFieldNames())) {
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

            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' IN ("' . implode('", "', $values) . '")');
            }
        }

        //add spans
        foreach ($span as $column => $value) {
            if (!empty($value)) {
                if (!preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                    continue;
                }

                // minimum?
                if (isset($value[0]) && !empty($value[0])) {
                    $search
                        ->addFilter($column . ' >= %s', $value[0]);
                }

                // maximum?
                if (isset($value[1]) && !empty($value[1])) {
                    $search
                        ->addFilter($column . ' <= %s', $value[1]);
                }
            }
        }

        //keyword?
        if (!empty($searchable)) {
            $keywords = [];

            if (isset($data['q'])) {
                $keywords = $data['q'];

                if (!is_array($keywords)) {
                    $keywords = [$keywords];
                }
            }

            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                foreach ($searchable as $name) {
                    $where[] = 'LOWER(' . $name . ') LIKE %s';
                    $or[] = '%' . strtolower($keyword) . '%';
                }

                array_unshift($or, '(' . implode(' OR ', $where) . ')');
                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $sort)) {
                $search->addSort($sort, $direction);
                continue;
            }

            //by chance is it a json filter?
            if (preg_match('/^[a-zA-Z0-9-_\.]+$/', $sort)) {
                $name = substr($sort, 0, strpos($sort, '.'));
                $path = substr($sort, strpos($sort, '.'));
                $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);

                //it should be a json column type
                if (!in_array($name, $this->schema->getJsonFieldNames())) {
                    continue;
                }

                $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
                $search->addSort($column, $direction);
                continue;
            }
        }

        //add grouping
        // TODO: add the regex specified in order BUT
        // allow insert of mysql defined functions like
        // DATE_FORMAT(column, '%Y') etc
        foreach ($groups as $group) {
            $search->groupBy($group);
        }

        // if there is grouping and there is a sum or count field,
        // we will assume that this is not a global thing
        if ($groups && ($sum || $count)) {
            if (!$columns) {
                $columns[] = '*';
            }

            if ($count) {
                $columns[] = $count;
            }

            if ($sum) {
                $columns[] = $sum;
            }
        }

        if ($columns) {
            $search->setColumns($columns);
        }

        //manually add fields
        $search->jsonFields = $fields;

        return $search;
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
     * Unlinks all references in a table from another table
     *
     * @param *string $relation
     * @param *int    $primary
     *
     * @return array
     */
    public function unlinkAll($relation, $primary)
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

        $filter = sprintf('%s = %%s', $relation['primary1']);

        return $this
            ->resource
            ->search($table)
            ->addFilter($filter, $primary)
            ->getCollection()
            ->each(function ($i, $model) use (&$table) {
                $model->remove($table);
            })
            ->get();
    }

    /**
     * Update to database
     *
     * @param array $data
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
}
