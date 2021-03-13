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

use Cradle\IO\Request\RequestInterface;
use Cradle\IO\Response\ResponseInterface;

/**
 * System Schema Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-schema-create', function (RequestInterface $request, ResponseInterface $response) {
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

  // filter relations
  if (isset($data['relations'])) {
    // filter out empty relations
    $data['relations'] = array_filter($data['relations'], function ($relation) {
      // make sure we have relation name
      return isset($relation['name']) && trim($relation['name']);
    });

    foreach ($data['relations'] as $key => $relation) {
      $data['relations'][$key]['name'] = strtolower($relation['name']);
    }
  }

  //----------------------------//
  // 2. Validate Data
  $errors = Validator::getCreateErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response->invalidate($errors);
  }

  //----------------------------//
  // 3. Prepare Data
  //make a new payload
  $payload = $request->clone(true);

  //----------------------------//
  // 4. Process Data
  //save to file
  $schema = Schema::i($data);

  try {
    $schema->save();
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $results = $schema->get();

  //add the results
  $payload->setStage($results);
  //set the primary name
  $payload->setStage('primary', $schema->getPrimaryName());
  //re-add the fields with all possible types
  $payload->setStage('fields', $schema->getFields());
  //re-add the relations
  $payload->setStage('relations', $schema->getRelations());

  //trigger the store create
  $this('event')->emit('system-store-create', $payload, $response);

  if ($response->isError()) {
    return;
  }

  //return response format
  $response->setError(false)->setResults($results);
});

/**
 * System Schema Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-schema-detail', function (RequestInterface $request, ResponseInterface $response) {
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
$this('event')->on('system-schema-remove', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get the system detail
  $this('event')->emit('system-schema-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 3. Prepare Data
  //get data from results
  $data = $response->getResults();
  //load schema
  $schema = Schema::i($data);
  //get table
  $table = $schema->getName();
  //set restorable
  $restorable = $request->getStage('mode') !== 'permanent';

  //----------------------------//
  // 4. Process Data
  try {
    if (!$restorable) {
      $schema->delete();
    } else {
      $schema->archive();
    }
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }


  //make sure schema is set
  $request->setStage('schema', $schema->getName());
  //make sure restorable is set
  $request->setStage('restorable', $restorable);
  //trigger the store drop
  $this('event')->emit('system-store-drop', $request, $response);

  if ($response->isError()) {
    return;
  }

  $response->setError(false)->setResults($schema->get());
});

/**
 * System Schema Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-schema-restore', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get the system detail
  $this('event')->method('system-schema-detail', [
    'name' => '_' . $request->getStage('name')
  ], $response);

  //----------------------------//
  // 2. Validate Data
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 3. Prepare Data
  //get data from results
  $data = $response->getResults();
  //load schema
  $schema = Schema::i($data);
  //get table
  $table = $schema->getName();

  //----------------------------//
  // 4. Process Data
  try {
    $schema->restore();
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  //make sure schema is set
  $request->setStage('schema', $schema->getName());
  //trigger the store recover
  $this('event')->emit('system-store-recover', $request, $response);

  if ($response->isError()) {
    return;
  }

  $response->setError(false)->setResults($schema->get());
});

/**
 * System Schema Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-schema-search', function (RequestInterface $request, ResponseInterface $response) {
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
  $rows = Schema::search($filters);

  foreach ($rows as $i => $row) {
    $rows[$i] = $row->get();
  }

  //set response format
  $response->setError(false)->setResults([
    'rows' => $rows,
    'total' => count($rows)
  ]);
});

/**
 * System Schema Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-schema-update', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
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

  // filter relations
  if (isset($data['relations'])) {
    // filter out empty relations
    $data['relations'] = array_filter($data['relations'], function ($relation) {
      // make sure we have relation name
      return isset($relation['name']) && trim($relation['name']);
    });

    foreach ($data['relations'] as $key => $relation) {
      $data['relations'][$key]['name'] = strtolower($relation['name']);
    }
  }

  //get the system detail
  $this('event')->emit('system-schema-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  //if there's an error
  if ($response->isError()) {
    return;
  }

  $errors = Validator::getUpdateErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response->invalidate($errors);
  }

  //----------------------------//
  // 3. Prepare Data
  //get the original for later
  $original = Schema::i($response->getResults());
  $response->remove('json', 'results');

  //----------------------------//
  // 4. Process Data
  //make a new payload
  $payload = $request->clone(true);
  //load schema
  $schema = Schema::i($data);

  try {
    $schema->save();
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $results = $schema
    ->set('original', $original->get())
    ->get();

  //add the results
  $payload->setStage($results);
  //make sure schema is set
  $payload->setStage('schema', $schema->getName());
  //set the primary name
  $payload->setStage('primary', $schema->getPrimaryName());
  //re-add the fields with all possible types
  $payload->setStage('fields', $schema->getFields());
  //re-add the relations
  $payload->setStage('relations', $schema->getRelations());

  //re-add original field types
  $payload->setStage('original', 'fields', $original->getFields());
  //re-add original relations
  $payload->setStage('original', 'relations', $original->getRelations());

  //trigger the store create
  $this('event')->emit('system-store-alter', $payload, $response);

  if ($response->isError()) {
    return;
  }

  //return response format
  $response->setError(false)->setResults($results);
});
