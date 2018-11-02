<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Schema\Validator;
use Cradle\Package\System\Schema;

/**
 * System Schema Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = Validator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    // filter relations
    if (isset($data['relations'])) {
        // filter out empty relations
        $data['relations'] = array_filter(
            $data['relations'],
            function ($relation) {
                // make sure we have relation name
                return $relation['name'] !== '' ? true : false;
            }
        );

        foreach ($data['relations'] as $key => $relation) {
            $data['relations'][$key]['name'] = strtolower($relation['name']);
        }
    }

    $this->trigger('system-schema-search', $request, $response);
    $schemas = $response->getResults('rows');

    foreach ($schemas as $key => $schema) {
        foreach ($schema['fields'] as $fkey => $field) {
            $schema['fields'][$field['name']] = $field;
            unset($schema['fields'][$fkey]);
        }

        $schemas[$schema['name']] = $schema;
        unset($schemas[$key]);
    }

    foreach ($data['fields'] as $key => $field) {
        if ($field['field']['type'] == 'multifield'
            && isset($field['field']['schema'])
            && isset($schemas[$field['field']['schema']['name']])
        ) {
            $detail = [];
            $fields = $field['field']['schema']['fields'];
            $multifield = $schemas[$field['field']['schema']['name']];
            foreach ($fields as $fkey => $fname) {
                if (isset($multifield['fields'][$fname])) {
                    $detail[$fname] = $multifield['fields'][$fname];
                }
            }

            $data['fields'][$key]['field']['schema']['detail'] = $detail;
        }
    }

    //----------------------------//
    // 4. Process Data
    $schema = Schema::i($data);
    $table = $schema->getName();

    //create table
    $schema->service('sql')->create($data);

    $path = $this->package('global')->path('schema');

    if (!is_dir($path)) {
        mkdir($path, 0777);
    }

    $this->package('global')->schema($table, $data);

    //return response format
    $response->setError(false)->setResults($data);
});

/**
 * System Schema Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['schema'])) {
        $id = $data['schema'];
    } else if (isset($data['name'])) {
        $id = $data['name'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    $results = $this->package('global')->schema($id);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Schema Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the system detail
    $this->trigger('system-schema-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    $schema = Schema::i($data);
    $table = $schema->getName();
    //this/these will be used a lot
    $systemSql = $schema->service('sql');

    $restorable = true;
    if($request->getStage('mode') === 'permanent') {
        $restorable = false;
    }

    try {
        //remove table
        $results = $systemSql->remove($restorable);
    } catch (\Exception $e) {
        return $response->setError(true, $e->getMessage());
    }

    $path = $this->package('global')->path('schema') . '/' . $table . '.php';

    if(!$restorable) {
        unlink($path);
    } else if (file_exists($path)) {
        $new = $this->package('global')->path('schema') . '/_' . $table . '.php';
        rename($path, $new);
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Schema Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $request->setStage('name', '_' . $request->getStage('name'));
    //get the system detail
    $this->trigger('system-schema-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    $schema = Schema::i($data);
    $table = $schema->getName();
    //this/these will be used a lot
    $systemSql = $schema->service('sql');

    try {
        //remove table
        $results = $systemSql->restore($data);
    } catch (\Exception $e) {
        return $response->setError(true, $e->getMessage());
    }

    $path = $this->package('global')->path('schema') . '/_' . $table . '.php';

    if (file_exists($path)) {
        $new = $this->package('global')->path('schema') . '/' . $table . '.php';

        rename($path, $new);
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Schema Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    $path = $this->package('global')->path('schema');

    if (!is_dir($path)) {
        mkdir($path, 0777);
    }

    $files = scandir($path);

    $active = 1;
    if (isset($data['filter']['active'])) {
        $active = $data['filter']['active'];
    }

    $results = [];
    foreach ($files as $file) {
        if (//if this is not a php file
            (strpos($file, '.php') === false)
            //or active and this is not active
            || ($active && strpos($file, '_') === 0)
            //or not active and active
            || (!$active && strpos($file, '_') !== 0)
        ) {
            continue;
        }

        $results[] = $this->package('global')->schema(substr($file, 0, -4));
    }

    //set response format
    $response->setError(false)->setResults([
        'rows' => $results,
        'total' => count($results)
    ]);
});

/**
 * System Schema Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the system detail
    $this->trigger('system-schema-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = Validator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    // filter relations
    if (isset($data['relations'])) {
        // filter out empty relations
        $data['relations'] = array_filter(
            $data['relations'],
            function ($relation) {
                // make sure we have relation name
                return $relation['name'] !== '' ? true : false;
            }
        );

        foreach ($data['relations'] as $key => $relation) {
            $data['relations'][$key]['name'] = strtolower($relation['name']);
        }
    }

    $this->trigger('system-schema-search', $request, $response);
    $schemas = $response->getResults('rows');

    foreach ($schemas as $key => $schema) {
        foreach ($schema['fields'] as $fkey => $field) {
            $schema['fields'][$field['name']] = $field;
            unset($schema['fields'][$fkey]);
        }

        $schemas[$schema['name']] = $schema;
        unset($schemas[$key]);
    }

    foreach ($data['fields'] as $key => $field) {
        if ($field['field']['type'] == 'multifield'
            && isset($field['field']['schema'])
            && isset($schemas[$field['field']['schema']['name']])
        ) {
            $detail = [];
            $fields = $field['field']['schema']['fields'];
            $multifield = $schemas[$field['field']['schema']['name']];
            foreach ($fields as $fkey => $fname) {
                if (isset($multifield['fields'][$fname])) {
                    $detail[$fname] = $multifield['fields'][$fname];
                }
            }

            $data['fields'][$key]['field']['schema']['detail'] = $detail;
        }
    }

    //----------------------------//
    // 4. Process Data

    $schema = Schema::i($data);
    $table = $schema->getName();
    //this/these will be used a lot
    $systemSql = $schema->service('sql');

    //update table
    $systemSql->update($data);

    $this->package('global')->schema($table, $data);

    //return response format
    $response->setError(false)->setResults($data);
});
