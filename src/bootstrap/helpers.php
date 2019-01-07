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
use Cradle\Package\System\Helpers;

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

    $handlebars->registerHelper('format', function ($type, $schema, $row) {
        //get the options for later
        $arguments = func_get_args();
        $options = array_pop($arguments);

        //try to load the schema
        try {
            $schema = Helpers::getSchema($schema);
        } catch (Exception $e) {
            return '';
        }

        $fields = $schema->getFields();

        //we need to define a function so it can be recursively called
        $getFormats = function(
            $row,
            $type,
            $fields,
            $parent = []
        ) use (
            &$schema,
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

                if (!isset($parent['index'])) {
                    $parent['index'] = [];
                }

                //set easy reference parent variables
                $hasParent = isset($parent['name'], $parent['label']);
                $indexes = $parent['index'];

                //determine the key name
                $key = $name;
                if (isset($parent['name'])) {
                    $key = sprintf('%s[%s]', $parent['name'], $name);
                }

                $indexCount1 = substr_count($key, '{INDEX_');
                $indexPlaceholder1 = sprintf('{INDEX_%s}', $indexCount1);
                $indexCount2 = $indexCount1 + 1;
                $indexPlaceholder2 = sprintf('{INDEX_%s}', $indexCount2);

                //determine the value
                //for no field, but with a format
                $value = null;
                if (isset($row[$name])) {
                    $value = $row[$name];
                }

                //if field is a fieldset, set some more variables
                if ($field['field']['type'] === 'fieldset') {
                    $attributes = [];
                    if (isset($field['field']['attributes'])) {
                        $attributes = $field['field']['attributes'];
                    }

                    $multiple = !isset($attributes['data-multiple'])
                        || $attributes['data-multiple'];

                    $required = isset($attributes['data-required'])
                        && $attributes['data-required']
                        && empty($value);
                }

                //if its a fieldset and view is field/form
                if ($type === 'field' && $field[$type]['type'] === 'fieldset') {
                    //make sure value is an array by default
                    if(is_null($value)) {
                        $value = [];
                    }

                    //if not cached
                    $fieldset = Helpers::getFieldset(
                        $fields[$name]['field']['parameters']
                    );

                    $keyword = $fieldset->getName();
                    $singular = $fieldset->getSingular();
                    $plural = $fieldset->getPlural();
                    $fieldset = $fieldset->getFields();

                    //if this is a multiple occurence
                    if ($multiple) {
                        //determine the key and label
                        $key = sprintf(
                            '%s[%s]',
                            $name,
                            $indexPlaceholder1
                        );

                        $label = sprintf(
                            '%s %s',
                            $singular,
                            $indexPlaceholder1
                        );

                        if ($hasParent) {
                            $key = sprintf(
                                '%s[%s][%s]',
                                $parent['name'],
                                $name, $indexPlaceholder1
                            );

                            $label = sprintf(
                                '%s - %s %s',
                                $parent['label'],
                                $singular,
                                $indexPlaceholder1
                            );
                        }

                        if ($required) {
                            $value[] = array_fill_keys(
                                array_keys($fieldset),
                                null
                            );
                        }

                        //get the templates. This is needed and used
                        //to create mutiple fieldsets on the fly
                        $config = $getFormats([], 'field', $fieldset, [
                            'name' => $key,
                            'label' => $label
                        ]);

                        //get the values
                        $values = [];
                        foreach($value as $i => $row) {
                            $indexes[] = $i;

                            //resolve the label
                            $values[$i]['label'] = $label;
                            foreach ($indexes as $j => $index) {
                                $values[$i]['label'] = str_replace(
                                    '{INDEX_' . $j . '}',
                                    $index + 1,
                                    $values[$i]['label']
                                );
                            }

                            $values[$i]['rows'] = $getFormats(
                                $row,
                                'field',
                                $fieldset,
                                [
                                    'name' => $key,
                                    'label' => $label,
                                    'index' => $indexes
                                ]
                            );

                            array_pop($indexes);

                            foreach($values[$i]['rows'] as $key2 => $row2) {
                                //if there is a name template
                                if (isset($row2['name'])) {
                                    $row2['name'] = str_replace(
                                        $indexPlaceholder2,
                                        $i,
                                        $row2['name']
                                    );

                                    //set the dot notation. this is for error handling
                                    $row2['dot'] = Helpers::fieldNameToDotNotation(
                                        $row2['name']
                                    );

                                    $values[$i]['rows'][$key2] = $row2;
                                }
                            }
                        }
                    //this is a singular occurence
                    } else {
                        //determine the key and label
                        $key = $name;
                        $label = $singular;

                        if ($hasParent) {
                            $key = sprintf(
                                '%s[%s]',
                                $parent['name'],
                                $name
                            );

                            $label = sprintf(
                                '%s - %s',
                                $parent['label'],
                                $singular
                            );
                        }

                        //set as required
                        $value = array_merge(
                            array_fill_keys(
                                array_keys($fieldset),
                                null
                            ),
                            $value
                        );

                        //get the templates. This is needed and used
                        //to create mutiple fieldsets on the fly
                        $config = $getFormats([], 'field', $fieldset, [
                            'name' => $key,
                            'label' => $label
                        ]);

                        //get the values
                        $values = ['label' => $label];

                        $values['rows'] = $getFormats($value, 'field', $fieldset, [
                            'name' => $key,
                            'label' => $label,
                            'index' => $indexes
                        ]);

                        foreach($values['rows'] as $key2 => $row2) {
                            //if there is a name template
                            if (isset($row2['name'])) {
                                //set the dot notation. this is for error handling
                                $row2['dot'] = Helpers::fieldNameToDotNotation(
                                    $row2['name']
                                );

                                $values['rows'][$key2] = $row2;
                            }
                        }
                    }

                    $formats[$name] = [
                        '@key' => $name,
                        'type' => $type,
                        'label' => $fields[$name]['label'],
                        'name' => $key,
                        'dot' => $dot,
                        'config' => [
                            'field' => [
                                'type' => $fields[$name]['field']['type'],
                                'name' => $keyword,
                                'singular' => $singular,
                                'plural' => $plural,
                                'label' => $label,
                                'multiple' => $multiple,
                                'fields' => $config
                            ],
                            'list' => $fields[$name]['list'],
                            'detail' => $fields[$name]['detail']
                        ],
                        'value' => $values
                    ];

                    continue;
                }

                //if its a fieldset and view is detail/list
                //this will format nested tables formats
                //for fieldsets
                if (($type === 'list' || $type === 'detail')
                    && $field['field']['type'] === 'fieldset'
                    && $field['field']['type'] !== 'jsonpretty'
                    && is_array($value)
                ) {
                    //if not cached
                    $fieldset = Helpers::getFieldset(
                        $field['field']['parameters']
                    );

                    $fieldset = $fieldset->getFields();

                    //get the columns
                    $columns = [];
                    foreach($fieldset as $key => $fieldsetField) {
                        $columns[$key] = $fieldsetField['label'];
                    }

                    //set the columns
                    $fields[$name]['field']['columns'] = $columns;

                    if (!$multiple) {
                        $value = [ $value ];
                    }

                    //we need to fill columns that is not
                    //yet set to avoid broken table columns
                    foreach($value as $index => $row) {
                        foreach($columns as $key => $column) {
                            if (isset($row[$key])) {
                                continue;
                            }

                            $value[$index][$key] = null;
                            if ($fieldset[$key]['field']['type'] === 'fieldset') {
                                $value[$index][$key] = [];
                            }
                        }
                    }

                    //now we need to format the
                    //fieldset and fielset fields
                    foreach($value as $index => $row) {
                        //we should sort the rows based on column sorting
                        $value[$index] = array_merge(
                            array_flip(array_keys($columns)),
                            $value[$index]
                        );

                        $results = $getFormats($row, $type, $fieldset);

                        //on each value
                        foreach($row as $index2 => $value2) {
                            //get the formatted value
                            $value[$index][$index2] = $results[$index2]['value'];
                        }
                    }


                }

                //resolve the key
                $format = $key;
                foreach ($parent['index'] as $i => $index) {
                    $key = str_replace('{INDEX_' . $i . '}', $index, $key);
                }

                //set the dot notation. this is for error handling
                $dot = Helpers::fieldNameToDotNotation($key);

                //prepare the template
                $template = cradle('global')
                    ->handlebars()
                    ->compile(Helpers::getFormatTemplate($type));

                //get the default value in case it's empty
                if (is_null($value) || empty($value)) {
                    $value = $fields[$name]['default'];
                }

                //and prepare the results
                $formats[$name] = [
                    '@key' => $name,
                    'type' => $type,
                    'label' => $fields[$name]['label'],
                    'name' => $key,
                    'dot' => $dot,
                    'name_format' => $format,
                    'config' => [
                        'field' => $fields[$name]['field'],
                        'list' => $fields[$name]['list'],
                        'detail' => $fields[$name]['detail']
                    ],
                    'raw' => $value,
                    'value' => trim($template([
                        '@name' => $name,
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

        return $options['fn'](['formats' => $formats]);
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

    $handlebars->registerHelper('scope_dot', function ($array, $dot, $options) {
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
