<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Fieldset\Validator;
use Cradle\Package\System\Fieldset;

/**
 * System Fieldset Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-fieldset-create', function ($request, $response) {
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

    $this->trigger('system-fieldset-search', $request, $response);
    $fieldsets = $response->getResults('rows');

    foreach ($fieldsets as $key => $fieldset) {
        foreach ($fieldset['fields'] as $fkey => $field) {
            $fieldset['fields'][$field['name']] = $field;
            unset($fieldset['fields'][$fkey]);
        }

        $fieldsets[$fieldset['name']] = $fieldset;
        unset($fieldsets[$key]);
    }

    foreach ($data['fields'] as $key => $field) {
        if ($field['field']['type'] == 'multifield'
            && isset($field['field']['fieldset'])
            && isset($fieldsets[$field['field']['fieldset']['name']])
        ) {
            $detail = [];
            $fields = $field['field']['fieldset']['fields'];
            $multifield = $fieldsets[$field['field']['fieldset']['name']];
            foreach ($fields as $fkey => $fname) {
                if (isset($multifield['fields'][$fname])) {
                    $detail[$fname] = $multifield['fields'][$fname];
                }
            }

            $data['fields'][$key]['field']['fieldset']['detail'] = $detail;
        }
    }

    //----------------------------//
    // 4. Process Data
    $fieldset = Fieldset::i($data);
    $table = $fieldset->getName();

    $path = $this->package('global')->path('fieldset');

    if (!is_dir($path)) {
        mkdir($path, 0777);
    }

    $this->package('global')->fieldset($table, $data);

    //return response format
    $response->setError(false)->setResults($data);
});

/**
 * System Fieldset Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-fieldset-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['fieldset'])) {
        $id = $data['fieldset'];
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
    $results = $this->package('global')->fieldset($id);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Fieldset Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-fieldset-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the system detail
    $this->trigger('system-fieldset-detail', $request, $response);

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
    $fieldset = Fieldset::i($data);
    $table = $fieldset->getName();

    $restorable = true;
    if($request->getStage('mode') === 'permanent') {
        $restorable = false;
    }

    $path = $this->package('global')->path('fieldset') . '/' . $table . '.php';

    if(!$restorable) {
        unlink($path);
    } else if (file_exists($path)) {
        $new = $this->package('global')->path('fieldset') . '/_' . $table . '.php';
        rename($path, $new);
    }

    $response->setError(false)->setResults($data);
});

/**
 * System Fieldset Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-fieldset-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $request->setStage('name', '_' . $request->getStage('name'));
    //get the system detail
    $this->trigger('system-fieldset-detail', $request, $response);

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
    $fieldset = Fieldset::i($data);
    $table = $fieldset->getName();

    $path = $this->package('global')->path('fieldset') . '/_' . $table . '.php';

    if (file_exists($path)) {
        $new = $this->package('global')->path('fieldset') . '/' . $table . '.php';

        rename($path, $new);
    }

    $response->setError(false)->setResults($data);
});

/**
 * System Fieldset Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-fieldset-search', function ($request, $response) {
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
    $path = $this->package('global')->path('fieldset');

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

        $results[] = $this->package('global')->fieldset(substr($file, 0, -4));
    }

    //set response format
    $response->setError(false)->setResults([
        'rows' => $results,
        'total' => count($results)
    ]);
});

/**
 * System Fieldset Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-fieldset-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the system detail
    $this->trigger('system-fieldset-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get the original for later
    $original = $response->getResults();

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

    $this->trigger('system-fieldset-search', $request, $response);
    $fieldsets = $response->getResults('rows');

    foreach ($fieldsets as $key => $fieldset) {
        foreach ($fieldset['fields'] as $fkey => $field) {
            $fieldset['fields'][$field['name']] = $field;
            unset($fieldset['fields'][$fkey]);
        }

        $fieldsets[$fieldset['name']] = $fieldset;
        unset($fieldsets[$key]);
    }

    foreach ($data['fields'] as $key => $field) {
        if ($field['field']['type'] == 'multifield'
            && isset($field['field']['fieldset'])
            && isset($fieldsets[$field['field']['fieldset']['name']])
        ) {
            $detail = [];
            $fields = $field['field']['fieldset']['fields'];
            $multifield = $fieldsets[$field['field']['fieldset']['name']];
            foreach ($fields as $fkey => $fname) {
                if (isset($multifield['fields'][$fname])) {
                    $detail[$fname] = $multifield['fields'][$fname];
                }
            }

            $data['fields'][$key]['field']['fieldset']['detail'] = $detail;
        }
    }

    //----------------------------//
    // 4. Process Data

    $fieldset = Fieldset::i($data);
    $table = $fieldset->getName();

    //reset the cache
    $this->package('global')->fieldset($table, $data);

    //add the original
    $data['original'] = $original;

    //return response format
    $response->setError(false)->setResults($data);
});
