<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System;

use Cradle\Package\System\Schema\Service;
use Cradle\Module\Utility\Service\NoopService;

use Cradle\Data\Registry;
use Cradle\Helper\InstanceTrait;

use Cradle\Http\Request;
use Cradle\Http\Response;

use Cradle\Package\System\Helpers;

/**
 * Model Schema Manager. This was made
 * take advantage of pass-by-ref
 *
 * @vendor   Cradle
 * @package  System
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Schema extends Fieldset
{
    /**
     * Sets up the schema
     *
     * @param string|array $name
     */
    public function __construct($name)
    {
        $this->data = $name;
        if (!is_array($this->data)) {
            $this->data = cradle('global')->schema($name);
        }

        if (!$this->data || empty($this->data)) {
            throw Exception::forSchemaNotFound($name);
        }
    }

    /**
     * Returns active field
     *
     * @param *array $this->data
     *
     * @return string|false
     */
    public function getActiveFieldName()
    {
        if (!isset($this->data['fields'])
            || empty($this->data['fields'])
        ) {
            return false;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            if ($field['name'] === 'active') {
                return $table . '_' . $field['name'];
            }
        }

        return false;
    }

    /**
     * Returns all processed functions
     *
     * @param *array
     *
     * @return string|false
     */
    public function getAll($relations = true)
    {
        $results = array_merge($this->data, [
            'active' => $this->getActiveFieldName(),
            'created' => $this->getCreatedFieldName(),
            'filterable' => $this->getFilterableFieldNames(),
            'fields' => $this->getFields(),
            'files' => $this->getFileFieldNames(),
            'json' => $this->getJsonFieldNames(),
            'listable' => $this->getListableFieldNames(),
            'detailable' => $this->getDetailableFieldNames(),
            'primary' => $this->getPrimaryFieldName(),
            'required' => $this->getRequiredFieldNames(),
            'searchable' => $this->getSearchableFieldNames(),
            'slugable' => $this->getSlugableFieldNames(),
            'sortable' => $this->getSortableFieldNames(),
            'uuids' => $this->getUuidFieldNames(),
            'unique' => $this->getUniqueFieldNames(),
            'updated' => $this->getUpdatedFieldName()
        ]);

        if ($relations) {
            $results['relations'] = $this->getRelations();
        }

        return $results;
    }

    /**
     * Returns created field
     *
     * @return string|false
     */
    public function getCreatedFieldName()
    {
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return false;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            if ($field['name'] === 'created') {
                return $table . '_' . $field['name'];
            }
        }

        return false;
    }

    /**
     * Returns filterable fields
     *
     * @return array
     */
    public function getFilterableFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if (isset($field['filterable']) && $field['filterable']) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns primary
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return $this->getName() . '_id';
    }

    /**
     * Returns relational data
     *
     * @param int $many
     *
     * @return array
     */
    public function getRelations($many = -1)
    {
        $results = [];
        if (!isset($this->data['relations'])
            || empty($this->data['relations'])
        ) {
            return $results;
        }

        $table = $this->getName();
        $primary = $this->getPrimaryFieldName();

        foreach ($this->data['relations'] as $relation) {
            if (is_numeric($many) && $many != -1 && $many != $relation['many']) {
                continue;
            }

            //case for getting a specific relation
            if (!is_numeric($many) && $relation['name'] !== $many) {
                continue;
            }

            $name = $table . '_' . $relation['name'];

            $results[$name] = [];

            try {
                $results[$name] = Schema::i($relation['name'])->getAll(false);
            } catch (Exception $e) {
                //this is not a registered schema
                //lets make a best guess
                $results[$name]['name'] = $relation['name'];
                $results[$name]['singular'] = ucfirst($relation['name']);
                $results[$name]['plural'] = $results[$name]['singular'] . 's';
                $results[$name]['primary'] =  $relation['name'] . '_id';
            }

            $results[$name]['table'] = $name;
            $results[$name]['primary1'] = $primary;
            $results[$name]['primary2'] = $results[$name]['primary'];
            $results[$name]['many'] = $relation['many'];
            $results[$name]['source'] = $this->getAll(false);

            //case for relating to itself ie. post_post
            if ($table === $relation['name']) {
                $results[$name]['primary1'] .= '_1';
                $results[$name]['primary2'] .= '_2';
            }

            //case for getting a specific relation
            if (!is_numeric($many) && $relation['name'] === $many) {
                return $results[$name];
            }
        }

        return $results;
    }

    /**
     * Returns reverse relational data
     *
     * @param int $many
     *
     * @return array
     */
    public function getReverseRelations($many = -1)
    {
        $results = [];
        $name = $this->getName();

        $payload = cradle()->makePayload();

        cradle()->trigger(
            'system-schema-search',
            $payload['request'],
            $payload['response']
        );

        $rows = $payload['response']->getResults('rows');

        if (empty($rows)) {
            return $results;
        }

        foreach ($rows as $row) {
            $schema = Schema::i($row['name']);
            $table = $row['name'] . '_' . $name;

            $relations = $schema->getRelations();
            if (!isset($relations[$table]['many'])
                || (
                    $many != -1
                    && $many != $relations[$table]['many']
                )
            ) {
                continue;
            }

            $results[$table] = $relations[$table];
            $results[$table]['source'] = $schema->getAll(false);
        }

        return $results;
    }

    /**
     * Returns searchable fields
     *
     * @return array
     */
    public function getSearchableFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if (isset($field['searchable']) && $field['searchable']) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Based on the data will generate a suggestion format
     *
     * @param array
     *
     * @return string
     */
    public function getSuggestionFormat(array $data)
    {
        //if no suggestion format
        if (!isset($this->data['suggestion']) || !trim($this->data['suggestion'])) {
            //use best guess
            $suggestion = null;
            foreach ($data as $key => $value) {
                if (is_numeric($value)
                    || (
                        isset($value[0])
                        && is_numeric($value[0])
                    )
                ) {
                    continue;
                }

                $suggestion = $value;
                break;
            }

            //if still no suggestion
            if (is_null($suggestion)) {
                //just get the first one, i guess.
                foreach ($data as $key => $value) {
                    $suggestion = $value;
                    break;
                }
            }

            return $suggestion;
        }

        $template = cradle('global')->handlebars()->compile($this->data['suggestion']);

        return $template($data);
    }

    /**
     * Returns sortable fields
     *
     * @return array
     */
    public function getSortableFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if (isset($field['sortable']) && $field['sortable']) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns updated field
     *
     * @return string|false
     */
    public function getUpdatedFieldName()
    {
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return false;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            if ($field['name'] === 'updated') {
                return $table . '_' . $field['name'];
            }
        }

        return false;
    }

    /**
     * Returns an Model
     *
     * @return Model
     */
    public function model()
    {
        return Model::i($this);
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

        return $service->setSchema($this);
    }

    /**
     * Transforms to SQL data
     *
     * @return array
     */
    public function toSql()
    {
        $data = [
            'name' => $this->getName(),
            'primary' => $this->getPrimaryFieldName(),
            'columns' => [],
            'relations' => $this->getRelations()
        ];

        foreach ($this->data['fields'] as $field) {
            if (!isset(self::$fieldTypes[$field['field']['type']])) {
                continue;
            }

            $name = $data['name'] . '_' . $field['name'];
            $format = self::$fieldTypes[$field['field']['type']];

            if (// if type is int or float
                ($format['type'] === 'INT' || $format['type'] === 'FLOAT')
                // and there's a min attribute
                && isset($field['field']['attributes']['min'])
                // and its a number
                && is_numeric($field['field']['attributes']['min'])
                // and its a positive number
                && $field['field']['attributes']['min'] >= 0
            ) {
                //it should be unsigned
                $format['attribute'] = 'unsigned';
            }

            //if no length was defined
            if (!isset($format['length'])) {
                //if type is int
                if ($format['type'] === 'INT') {
                    //by default it's 10
                    $format['length'] = 10;
                    //if there is a max
                    if (isset($field['field']['attributes']['max'])
                        && is_numeric($field['field']['attributes']['max'])
                    ) {
                        //get the length from the max
                        $numbers = explode('.', '' . $field['field']['attributes']['max']);
                        $format['length'] = strlen($numbers[0]);
                    }

                    //if there's a step and there's a decimal
                    if (isset($field['field']['attributes']['step'])
                        && strpos('' . $field['field']['attributes']['step'], '.') !== false
                    ) {
                        $format['type'] = 'FLOAT';
                    }
                }

                //if it's a float
                if ($format['type'] === 'FLOAT') {
                    $integers = $decimals = 0;
                    //if there's a max
                    if (isset($field['field']['attributes']['max'])
                        && is_numeric($field['field']['attributes']['max'])
                    ) {
                        //determine the initial integer and decimal
                        $numbers = explode('.', '' . $field['field']['attributes']['max']);
                        $integers = strlen($numbers[0]);

                        if (isset($numbers[1])) {
                            $decimals = strlen($numbers[1]);
                        }
                    }

                    //if there's a step
                    if (isset($field['field']['attributes']['step'])
                        && is_numeric($field['field']['attributes']['step'])
                    ) {
                        $numbers = explode('.', '' . $field['field']['attributes']['step']);
                        //choose the larger of each integer and decimal
                        $integers = max($integers, strlen($numbers[0]));

                        if (isset($numbers[1])) {
                            $decimals = max($decimals, strlen($numbers[1]));
                        }
                    }

                    //if decimals is still 0
                    if (!$decimals) {
                        //make it 10
                        $decimals = 10;
                    }

                    $length = $integers + $decimals;

                    //finalize the length
                    $format['length'] = $length . ',' . $decimals;
                }
            }

            //if theres a reason to index
            if ($format['type'] !== 'TEXT'
                && (
                    (isset($field['searchable']) && $field['searchable'])
                    || (isset($field['filterable']) && $field['filterable'])
                    || (isset($field['sortable']) && $field['sortable'])
                )
            ) {
                //index it
                $format['index'] = true;
            }

            //determine the default
            if (isset($field['default'])
                && strpos($field['default'], '()') === false
            ) {
                $format['default'] = $field['default'];
            }

            //determine unique and required
            $format['required'] = false;
            $format['unique'] = false;

            if (isset($field['validation'])) {
                foreach ($field['validation'] as $validation) {
                    if ($validation === 'required') {
                        $format['required'] = true;
                    }

                    if ($validation === 'unique') {
                        $format['unique'] = true;
                        $format['index'] = false;
                    }
                }
            }

            $data['columns'][$name] = $format;
        }

        return $data;
    }

    /**
     * Transforms to Elastic data
     *
     * @return array
     */
    public function toElastic()
    {   
        //call recursive map fields
        $maps = $this->getMapFields($this->data['name']);

        return $maps;
    }

    /**
     * Get Map Fields
     *
     * @return array
     */
    public function getMapFields($name) 
    {
        //get fields recursive
        $getFields = function($name) use (
            &$fieldset,
            &$getFields
        ) {
            //get fieldset data            
            $fieldset = Helpers::getFieldset($name);

            //set default map
            $maps = [
                $name. '_id' => 
                    ['type' => 'integer']
            ];

            if ($fieldset) {
                foreach ($fieldset['fields'] as $field) {
                    if (!isset(self::$fieldTypes[$field['field']['type']])) {
                        continue;
                    }

                    $fieldName = $name . '_' . $field['name'];
                    $format = self::$fieldTypes[$field['field']['type']];

                    //set short datatype
                    if ($format['type'] === 'INT') {
                        $map['type'] = 'integer';
                    }

                    //set short datatype
                    if ($format['type'] === 'INT' 
                        && (isset($format['length']) 
                            && $format['length'] === 1)) {
                        $map['type'] = 'short';
                    }

                    //set keyword datatype
                    if ($format['type'] === 'VARCHAR') {
                        $map = [
                            'type' => 'text',
                        ];
                    }

                    //set date datatype
                    if ($format['type'] === 'datetime') {
                        $map = [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss'
                        ];
                    }  

                    //set time datatype
                    if ($format['type'] === 'time') {
                        $map = [
                            'type' => 'date',
                            'format' => 'hour_minute_second'
                        ];
                    }

                    //set text datatype
                    if ($format['type'] === 'TEXT') {
                        $map = [
                            'type' => 'text'
                        ];
                    }

                    //set float datatype
                    if ($format['type'] === 'FLOAT') {
                        $map = [
                            'type' => 'float'
                        ];
                    }

                    //set json to text (single array)
                    if ($format['type'] === 'JSON') {
                        $map = [
                            'type' => 'text'
                        ];
                    }

                    //dynamic object
                    if ($field['field']['type'] === 'rawjson'
                        || $field['field']['type'] === 'meta'
                    ) {
                        $map = [
                            'type' => 'object',
                            'dynamic' => true
                        ];
                    }

                    //set ojbect datatype 
                    if ($field['field']['type'] === 'fieldset' 
                        && (isset($field['field']['attributes']['data-multiple'])
                            && $field['field']['attributes']['data-multiple'] === 'false'
                            )
                    ) {
                        $map = [
                            'type' => 'text'
                        ];
                    } else if($field['field']['type'] === 'fieldset') {
                        $map = $getFields($field['name']);
                        $map = [
                            'type' => 'nested',
                            'properties' => $map
                        ];
                    }
                    
                    //set custom field (multirange) eg: 10;50 
                    if ($field['field']['type'] === 'multirange') {
                        $map = [
                            'type' => 'keyword',
                        ];
                    }

                    //map fields
                    $maps[$fieldName] = $map;
                }
            }

            return $maps;
        };


        //call get fields recursive
        $data = $getFields($name);

        //map relations fields
        foreach ($this->data['relations'] as $schema) {
            $data = array_merge($data, $getFields($schema['name']));
        }

        return $data;
    }

    /**
     * @var array $fieldTyles
     */
    protected static $fieldTypes = [
        'text' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'email' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'password' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'search' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'url' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'color' => [
            'type' => 'VARCHAR',
            'length' => 7
        ],
        'mask' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'slug' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'textarea' => [
            'type' => 'TEXT'
        ],
        'wysiwyg' => [
            'type' => 'TEXT'
        ],
        'markdown' => [
            'type' => 'TEXT'
        ],
        'code' => [
            'type' => 'TEXT'
        ],
        'number' => [
            'type' => 'INT'
        ],
        'small' => [
            'type' => 'INT',
            'length' => 1,
            'attribute' => 'unsigned'
        ],
        'range' => [
            'type' => 'INT'
        ],
        'float' => [
            'type' => 'FLOAT'
        ],
        'price' => [
            'type' => 'FLOAT',
            'length' => '10,2'
        ],
        'stars' => [
            'type' => 'FLOAT',
            'length' => '10,1'
        ],
        'date' => [
            'type' => 'date'
        ],
        'time' => [
            'type' => 'time'
        ],
        'datetime' => [
            'type' => 'datetime'
        ],
        'week' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'month' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'checkbox' => [
            'type' => 'INT',
            'length' => 1,
            'attribute' => 'unsigned'
        ],
        'switch' => [
            'type' => 'INT',
            'length' => 1,
            'attribute' => 'unsigned'
        ],
        'select' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'multiselect' => [
            'type' => 'JSON'
        ],
        'checkboxes' => [
            'type' => 'JSON'
        ],
        'radios' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'file' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'image' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'filelist' => [
            'type' => 'JSON'
        ],
        'imagelist' => [
            'type' => 'JSON'
        ],
        'tag' => [
            'type' => 'JSON'
        ],
        'textlist' => [
            'type' => 'JSON'
        ],
        'textarealist' => [
            'type' => 'JSON'
        ],
        'wysiwyglist' => [
            'type' => 'JSON'
        ],
        'meta' => [
            'type' => 'JSON'
        ],
        'multirange' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'rawjson' => [
            'type' => 'JSON'
        ],
        'fieldset' => [
            'type' => 'JSON'
        ],
        'table' => [
            'type' => 'JSON'
        ],
        'uuid' => [
            'type' => 'VARCHAR',
            'length' => 255
        ],
        'active' => [
            'type' => 'INT',
            'length' => 1,
            'null' => false,
            'default' => 1,
            'attribute' => 'UNSIGNED'
        ],
        'created' => [
            'type' => 'datetime',
            'null' => false
        ],
        'updated' => [
            'type' => 'datetime',
            'null' => false
        ]
    ];
}
