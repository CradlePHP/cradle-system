<?php

use Cradle\Module\System\Schema;

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

$handlebars->registerHelper('format', function ($schema, $row, $type, $options) {
    $schema = Schema::i($schema);
    $fields = $schema->getFields();

    if ($type !== 'list') {
        $type = 'detail';
    }

    $formats = [];
    foreach ($fields as $name => $field) {
        $format = $field[$type];
        $format['name'] = $name;
        $format['label'] = $field['label'];

        if (!isset($row[$name])) {
            $format['value'] = null;
        } else {
            $format['value'] = $row[$name];
        }

        $format['this'] = $format;

        $formats[] = $options['fn']($format);
    }

    return implode('', $formats);
});

$handlebars->registerHelper('schema_row', function ($schema, $row, $key) {
    $schema = Schema::i($schema);

    switch ($key) {
        case 'id':
            return $row[$schema->getPrimaryFieldName()];
        case 'active':
            $key = $schema->getActiveFieldName();
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
