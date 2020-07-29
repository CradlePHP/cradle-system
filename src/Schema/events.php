<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Schema\Validator;

use Cradle\Package\System\Schema;
use Cradle\Package\System\SystemException;

/**
 * System Schema Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-create', function ($request, $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  $validColumns = [
    'singular',
    'plural',
    'name',
    'group',
    'icon',
    'detail',
    'fields',
    'relations',
    'suggestion',
    'disable',
    'placeholder'
  ];

  //remove unnecessary data
  foreach ($data as $key => $value) {
    if (!in_array($key, $validColumns)) {
      unset($data[$key]);
    }
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

  //----------------------------//
  // 4. Process Data
  //make a schema folder
  $path = $this('global')->path('schema');
  if (!is_dir($path)) {
    mkdir($path, 0777);
  }

  //save to file
  $results = Schema::i($data)->save()->get();

  //return response format
  $response->setError(false)->setResults($results);
});

/**
 * System Schema Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-detail', function ($request, $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

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
  try {
    $results = Schema::load($id)->get();
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
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
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

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

  $restorable = true;
  if ($request->getStage('mode') === 'permanent') {
    $restorable = false;
  }

  $path = $this->package('global')->path('schema') . '/' . $table . '.php';

  if (!$restorable) {
    unlink($path);
  } else if (file_exists($path)) {
    $new = $this->package('global')->path('schema') . '/_' . $table . '.php';
    rename($path, $new);
  }

  $response->setError(false)->setResults($schema->get());
});

/**
 * System Schema Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-restore', function ($request, $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get the system detail
  $this->method('system-schema-detail', [
    'name' => '_' . $request->getStage('name')
  ], $response);

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

  $path = $this->package('global')->path('schema') . '/_' . $table . '.php';

  if (file_exists($path)) {
    $new = $this->package('global')->path('schema') . '/' . $table . '.php';
    rename($path, $new);
  }

  $response->setError(false)->setResults($schema->get());
});

/**
 * System Schema Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-schema-search', function ($request, $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  // no need
  //----------------------------//
  // 2. Validate Data
  //no validation needed
  //----------------------------//
  // 3. Prepare Data
  $filters = [];
  if (is_array($request->getStage('filter'))) {
    $filters = $request->getStage('filter');
  }

  //----------------------------//
  // 4. Process Data
  $results = Schema::search($filters);

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
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get the system detail
  $this->trigger('system-schema-detail', $request, $response);

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

  $validColumns = [
    'singular',
    'plural',
    'name',
    'group',
    'icon',
    'detail',
    'fields',
    'relations',
    'suggestion',
    'disable',
    'placeholder'
  ];

  //remove unnecessary data
  foreach ($data as $key => $value) {
    if (!in_array($key, $validColumns)) {
      unset($data[$key]);
    }
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

  //----------------------------//
  // 4. Process Data
  $results = Schema::i($data)->save()->set('original', $original)->get();

  //return response format
  $response->setError(false)->setResults($results);
});
