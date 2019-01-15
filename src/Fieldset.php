<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System;

use Cradle\Data\Registry;
use Cradle\Helper\InstanceTrait;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Model Fieldset Manager. This was made
 * take advantage of pass-by-ref
 *
 * @vendor   Cradle
 * @package  System
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Fieldset extends Registry
{
    /**
     * Sets up the fieldset
     *
     * @param string|array $name
     */
    public function __construct($name)
    {
        $this->data = $name;
        $global = cradle('global');
        if (!is_array($this->data)) {
            $this->data = $global->fieldset($name);
        }

        if (!$this->data || empty($this->data)) {
            $this->data = $global->schema($name);

            if (!$this->data || empty($this->data)) {
                throw Exception::forFieldsetNotFound($name);
            }
        }
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
            'fields' => $this->getFields(),
            'files' => $this->getFileFieldNames(),
            'json' => $this->getJsonFieldNames(),
            'listable' => $this->getListableFieldNames(),
            'detailable' => $this->getDetailableFieldNames(),
            'required' => $this->getRequiredFieldNames(),
            'slugable' => $this->getSlugableFieldNames(),
            'uuids' => $this->getUuidFieldNames(),
            'unique' => $this->getUniqueFieldNames()
        ]);

        return $results;
    }

    /**
     * Returns detailable fields
     *
     * @return string
     */
    public function getDetailableFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];

            if (isset($field['detail']['format'])
                && $field['detail']['format'] !== 'hide'
            ) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns All fields
     *
     * @return array
     */
    public function getFields()
    {
        $results = [];
        if (!isset($this->data['fields'])
            || empty($this->data['fields'])
        ) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            $results[$name] = $field;
        }

        return $results;
    }

    /**
     * Returns All files
     *
     * @return array
     */
    public function getFileFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields'])
            || empty($this->data['fields'])
        ) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];

            if (in_array(
                $field['field']['type'],
                [
                        'file',
                        'image',
                        'filelist',
                        'imagelist'
                    ]
            )
            ) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns JSON fields
     *
     * @return array
     */
    public function getJsonFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];

            if (in_array(
                $field['field']['type'],
                [
                        'filelist',
                        'imagelist',
                        'tag',
                        'textlist',
                        'textarealist',
                        'wysiwyglist',
                        'meta',
                        'checkboxes',
                        'multirange',
                        'rawjson',
                        'multiselect',
                        'fieldset',
                        'table'
                    ]
            )
            ) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns listable fields
     *
     * @return string
     */
    public function getListableFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];

            if (isset($field['list']['format'])
                && $field['list']['format'] !== 'hide'
            ) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns a list of required fields
     *
     * @return array
     */
    public function getRequiredFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if (!isset($field['validation'])) {
                continue;
            }

            foreach ($field['validation'] as $validation) {
                if ($validation['method'] === 'required') {
                    $results[] = $name;
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Returns slug fields
     *
     * @param string|false $primary
     *
     * @return array
     */
    public function getSlugableFieldNames($primary = false)
    {
        $results = [];
        if ($primary) {
            $results[] = $primary;
        }

        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if (isset($field['type'])) {
                if ($field['type'] === 'slug') {
                    $results[] = $name;
                }
            }
        }

        return $results;
    }

    /**
     * Returns a list of unique fields
     *
     * @return array
     */
    public function getUniqueFieldNames()
    {
        $results = [ $this->getPrimaryFieldName() ];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];

            if ($field['field']['type'] === 'uuid') {
                $results[] = $name;
                continue;
            }

            if (!isset($field['validation'])) {
                continue;
            }

            foreach ($field['validation'] as $validation) {
                if ($validation['method'] === 'unique') {
                    $results[] = $name;
                }
            }
        }

        return $results;
    }

    /**
     * Returns uuid fields
     *
     * @return string|false
     */
    public function getUuidFieldNames()
    {
        $results = [];
        if (!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach ($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if ($field['field']['type'] === 'uuid') {
                $results[] = $name;
            }
        }

        return $results;
    }
}
