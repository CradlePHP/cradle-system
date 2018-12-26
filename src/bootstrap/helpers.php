<?php
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Data\Registry;
use Cradle\Package\System\Fieldset;
use Cradle\Package\System\Schema;

return function($request, $response) {
    //add helpers
    $handlebars = $this->package('global')->handlebars();

    $handlebars->registerHelper('relations', function (...$args) {
        //resolve the arguments
        $options = array_pop($args);
        $schema = array_shift($args);
        $many = -1;

        if (isset($args[0])) {
            $many = $args[0];
        }

        if (isset($args[1]) && $args[1]) {
            $relations = Schema::i($schema)->getReverseRelations($many);
        } else {
            $relations = Schema::i($schema)->getRelations($many);
        }

        if (!is_numeric($many) && count($relations)) {
            $table = $relations['table'];
            $relations = [$table => $relations];
        }

        //pass suggestion title field for each relation to the template
        foreach ($relations as $name => $relation) {
            $relations[$name]['suggestion_name'] = '_' . $relation['primary2'];
        }

        if (empty($relations)) {
            return $options['inverse']();
        }

        $each = cradle('global')->handlebars()->getHelper('each');

        return $each($relations, $options);
    });

    $handlebars->registerHelper('suggest', function ($schema, $row) {
        return Schema::i($schema)->getSuggestionFormat($row);
    });

    $handlebars->registerHelper('format', function (
        $type,
        $schema,
        $row,
        $name = null,
        $options = null
    ) {
        static $schemas = [];
        static $fieldsets = [];
        static $templates = [];

        //cache the templates
        if (empty($templates)) {
            $templates['detail'] = file_get_contents(
                sprintf(
                    '%s/Model/template/format/detail.html',
                    dirname(__DIR__)
                )
            );

            $templates['field'] = file_get_contents(
                sprintf(
                    '%s/Model/template/format/field.html',
                    dirname(__DIR__)
                )
            );

            $templates['list'] = file_get_contents(
                sprintf(
                    '%s/Model/template/format/list.html',
                    dirname(__DIR__)
                )
            );
        }

        //if not cached
        if (!is_array($schema) && !isset($schemas[$schema])) {
            try { //cache the schema
                $schemas[$schema] = Schema::i($schema);
            } catch (Exception $e) {
                return '';
            }
        }

        //if its an array
        if (is_array($schema)) {
            try { //load the schema
                $schema = Schema::i($schema);
            } catch (Exception $e) {
                return '';
            }
        } else {
            //otherwise, get it from the cache
            $schema = $schemas[$schema];
        }

        if (func_num_args() === 4) {
            $options = $name;
            $name = null;
        }

        $fields = $schema->getFields();

        //if the just want a specific value
        if (!is_null($name) && isset($fields[$name])) {
            $fields = [ $name => $fields[$name] ];
        }

        //we need to define a function so it can be recursively called
        $getFormats = function(
            $row,
            $type,
            $fields,
            $blank = false,
            $root = null,
            $index = null,
            $parent = null
        ) use (
            &$schema,
            &$templates,
            &$fieldsets,
            &$getFormats
        ) {
            $formats = [];

            foreach ($fields as $name => $field) {
                if (!isset($field[$type])
                    || ($type === 'field' && $field[$type]['type'] === 'none')
                    || ($type === 'field' && $field[$type]['type'] === 'active')
                    || ($type === 'field' && $field[$type]['type'] === 'created')
                    || ($type === 'field' && $field[$type]['type'] === 'updated')
                    || ($type === 'field' && $field[$type]['type'] === 'uuid')
                    || ($type === 'list' && $field[$type]['format'] === 'hide')
                    || ($type === 'detail' && $field[$type]['format'] === 'hide')
                ) {
                    continue;
                }

                //determine the key name
                $key = $name;

                if (!is_null($root)) {
                    $key = sprintf('%s[%s][%s]', $root, $index, $name);
                }

                // if blank template
                if (!is_null($root) && $blank) {
                    $key = sprintf('{ROOT}[%s]', $name);
                }

                //determine the value
                //for no field, but with a format
                $value = null;
                if (isset($row[$name])) {
                    $value = $row[$name];
                }

                //if its a fieldset
                if ($type === 'field' && $field[$type]['type'] === 'fieldset') {
                    //make sure value is an array by default
                    if(is_null($value)) {
                        $value = [];
                    }

                    //if not cached
                    $fieldset = $fields[$name]['field']['parameters'];
                    if (!isset($fieldsets[$fieldset])) {
                        try { //cache the schema
                            $fieldsets[$fieldset] = Fieldset::i($fieldset);
                        } catch (Exception $e) {
                            return [];
                        }
                    }

                    //get the fields
                    $fieldset = $fieldsets[$fieldset]->getFields();

                    //map each rows and get format
                    $map = function($row, $index) use (
                        $getFormats,
                        $type,
                        $fieldset,
                        $key,
                        $blank,
                        $name
                    ) {
                        return [
                            'formats' => $getFormats($row, $type, $fieldset, $blank, $key, $index, $name)
                        ];
                    };

                    $formats[$name] = [
                        '@key' => $name,
                        'fieldset' => true,
                        'label' => $fields[$name]['label'],
                        'type' => $field['field']['type'],
                        'raw' => $value,
                        'config' => $fields[$name][$type],
                        //recursive call
                        'formats' => $blank ?
                            $getFormats($row, $type, $fieldset, $blank, $key, $index + 1, 'ROOT') :
                            array_map($map, $value, array_keys($value))
                    ];

                    continue;
                }

                if (($type === 'list' || $type === 'detail')
                    && $field['field']['type'] === 'fieldset'
                    && $field[$type]['format'] !== 'jsonpretty'
                ) {
                    //make sure value is an array by default
                    if(is_null($value)) {
                        $value = [];
                    }

                    //if not cached
                    $fieldset = $fields[$name]['field']['parameters'];
                    if (!isset($fieldsets[$fieldset])) {
                        try { //cache the schema
                            $fieldsets[$fieldset] = Fieldset::i($fieldset);
                        } catch (Exception $e) {
                            return [];
                        }
                    }

                    //get the fields
                    $fieldset = $fieldsets[$fieldset]->getFields();

                    //get the columns
                    $columns = $fields[$name]['field']['columns'] = array_map(function($field) {
                        return $field['label'];
                    }, $fieldset);

                    //get the rows
                    $rows = $value;

                    //need to do this for table format
                    foreach($columns as $key => $column) {
                        //on each row
                        foreach($rows as $index => $inner) {
                            //if it's set
                            if (isset($inner[$key])) {
                                continue;
                            }

                            //if it's not set and it's a fieldset
                            if ($fields[$name]['field']['type'] === 'fieldset') {
                                $rows[$index][$key] = [];

                            //set default value
                            } else {
                                $rows[$index][$key] = null;
                            }
                        }
                    }

                    //on each row
                    foreach($rows as $index => $inner) {
                        //we should sort the rows based on column sorting
                        $rows[$index] = array_merge(
                            array_flip(array_keys($columns)),
                            $rows[$index]
                        );

                        //get the formats
                        $results = $getFormats($inner, $type, $fieldset, false);

                        //on each value
                        foreach($inner as $key => $value) {
                            //get the formatted value
                            $rows[$index][$key] = $results[$key]['value'];
                        }
                    }

                    //update the value
                    $value = $rows;
                }

                //FIX: for table columns need to sort out
                //the values based on column order
                if ($field['field']['type'] === 'table' && !empty($value)) {
                    $columns = $field['field']['columns'];

                    foreach($value as $index => $inner) {
                        //we should sort the rows based on column sorting
                        $value[$index] = array_merge(
                            array_flip(array_values($columns)),
                            $value[$index]
                        );
                    }
                }

                //prepare the template
                $template = cradle('global')
                    ->handlebars()
                    ->compile($templates[$type]);

                //create validation path, since fieldsets are
                //recursively compiled template, we kind of loose
                //the scope of the errors data. We can solve that by
                //fetching the validation from response object.
                $validation = trim(str_replace(['][', ']', '['], '.', $key), '.');

                //get the default value in case it's empty
                if (is_null($value) || empty($value)) {
                    $value = $fields[$name]['default'];
                }

                //and prepare the results
                $formats[$name] = [
                    '@path' => $key,
                    //need this to access recursive errors from response
                    '@validation' => $validation,
                    'label' => $fields[$name]['label'],
                    'type' => $field['field']['type'],
                    'raw' => $value,
                    'config' => $fields[$name][$type],
                    'value' => trim($template([
                        '@key' => $key,
                        'this' => $value,
                        'row' => $row,
                        'config' => $fields[$name][$type],
                        //need this for table columns
                        'field' => $fields[$name]['field'],
                        'schema' => $schema['name']
                    ]))
                ];
            }

            return $formats;
        };

        //get the formats
        $formats = $getFormats($row, $type, $fields);

        //get the templates
        $fieldsetTemplates = [];

        //if it's a field
        if ($type === 'field') {
            //instead of recursively fetching the templates which
            //will cost more and will make it hard to debug, we
            //separate it so we can output and re-use each template
            //on the client side
            $fieldsetTemplates = $getFormats($row, $type, $fields, true);

            //filter fieldsets
            $fieldsetTemplates = array_filter($fieldsetTemplates, function($template) {
                return isset($template['fieldset']);
            });

            //recursively flatten out the templates
            $flatten = function($data, &$output = []) use (&$flatten) {
                //on each field
                foreach($data as $name => $field) {
                    if (!isset($field['fieldset'])) {
                        continue;
                    }

                    //set the fieldset to root
                    $output[$name] = $field;

                    //on each formats
                    foreach($output[$name]['formats'] as $index => $format) {
                        if (!isset($format['fieldset'])) {
                            continue;
                        }

                        //clear out the formats to avoid the formats showing upon initial rendering of the template
                        unset($output[$name]['formats'][$index]['formats']);
                    }

                    //recursively put the formats on the root
                    $flatten($field['formats'], $output);
                }

                return $output;
            };

            //get the flat templates
            $fieldsetTemplates = [ 'formats' => $flatten($fieldsetTemplates) ];
        }

        //if only one format
        if (isset($formats[$name])) {
            return $options['fn']($formats[$name]);
        }

        return $options['fn']([ 'formats' => $formats, 'templates' => $fieldsetTemplates ]);
    });

    $handlebars->registerHelper('schema_row', function ($schema, $row, $key) {
        $schema = Schema::i($schema);

        switch ($key) {
            case 'id':
                return $row[$schema->getPrimaryFieldName()];
            case 'active':
                $key = $schema->getActiveFieldName();

                if ($key === false) {
                    return true;
                }

                if (isset($row[$key])) {
                    return $row[$key];
                }

                break;
            case 'created':
                $key = $schema->getCreatedFieldName();
                if (isset($row[$key])) {
                    return $row[$key];
                }
                break;
            case 'updated':
                $key = $schema->getUpdatedFieldName();
                if (isset($row[$key])) {
                    return $row[$key];
                }
                break;
        }

        return false;
    });

    $handlebars->registerHelper('active', function ($schema, $row, $options) {
        $schemaKey = cradle('global')->handlebars()->getHelper('schema_row');

        if ($schemaKey($schema, $row, 'active')) {
            return $options['fn']();
        }

        return $options['inverse']();
    });

    $handlebars->registerHelper('json_encode', function (...$args) {
        $options = array_pop($args);
        $value = array_shift($args);
        foreach ($args as $arg) {
            if (!isset($value[$arg])) {
                $value = null;
                break;
            }

            $value = $value[$arg];
        }

        if (!$value) {
            return '';
        }

        if (!is_array($value) && !is_object($value)) {
            return $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    });

    $handlebars->registerHelper('json_pretty', function ($value, $options) {
        return nl2br(str_replace(' ', '&nbsp;', json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)));
    });

    $handlebars->registerHelper('scopedot', function ($array, $dot, $options) {
        if (!is_array($array)) {
            return $options['inverse']();
        }

        $scope = Registry::i($array)->getDot($dot);

        if (is_null($scope)) {
            return $options['inverse']();
        }

        if (!is_array($scope)) {
            $scope = ['this' => $scope];
            $results = $options['fn']($scope);
            if (!$results) {
                return $scope['this'];
            }

            return $results;
        }

        $scope['this'] = $scope;
        return $options['fn']($scope);
    });
};
