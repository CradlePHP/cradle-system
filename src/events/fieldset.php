<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Fieldset\Validator;

use Cradle\Package\System\Fieldset;
use Cradle\Package\System\SystemException;

use Cradle\IO\Request\RequestInterface;
use Cradle\IO\Response\ResponseInterface;

/**
 * System Fieldset Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-fieldset-create', function (RequestInterface $request, ResponseInterface $response) {
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
    'fields',
    'disable'
  ];

  //remove unnecessary data
  foreach ($data as $key => $value) {
    if (!in_array($key, $validColumns)) {
      unset($data[$key]);
    }
  }

  //----------------------------//
  // 2. Validate Data
  $errors = Validator::getCreateErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($errors);
  }

  //----------------------------//
  // 3. Prepare Data
  //save to file
  $fieldset = Fieldset::i($data);

  //----------------------------//
  // 4. Process Data
  try {
    $fieldset->save();
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $results = $fieldset->get();

  //return response format
  $response->setError(false)->setResults($results);
});

/**
 * System Fieldset Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-fieldset-detail', function (RequestInterface $request, ResponseInterface $response) {
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
  try {
    $results = Fieldset::load($id)->get();
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $response->setError(false)->setResults($results);
});

/**
 * System Fieldset Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-fieldset-remove', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //load the emitter
  $emitter = $this('event');

  //get the system detail
  $emitter->emit('system-fieldset-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 3. Prepare Data
  //get data from results
  $data = $response->getResults();
  //load fieldset
  $fieldset = Fieldset::i($data);
  //get table
  $table = $fieldset->getName();
  //set restorable
  $restorable = $request->getStage('mode') !== 'permanent';

  //----------------------------//
  // 4. Process Data
  try {
    if (!$restorable) {
      $fieldset->delete();
    } else {
      $fieldset->archive();
    }
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $response->setError(false)->setResults($fieldset->get());
});

/**
 * System Fieldset Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-fieldset-restore', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //load the emitter
  $emitter = $this('event');

  //get the system detail
  $emitter->method('system-fieldset-detail', [
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
  //load fieldset
  $fieldset = Fieldset::i($data);
  //get table
  $table = $fieldset->getName();

  //----------------------------//
  // 4. Process Data
  try {
    $fieldset->restore();
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $response->setError(false)->setResults($fieldset->get());
});

/**
 * System Fieldset Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-fieldset-search', function (RequestInterface $request, ResponseInterface $response) {
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
  $results = Fieldset::search($filters);

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
$this('event')->on('system-fieldset-update', function (RequestInterface $request, ResponseInterface $response) {
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
    'fields',
    'disable'
  ];

  //remove unnecessary data
  foreach ($data as $key => $value) {
    if (!in_array($key, $validColumns)) {
      unset($data[$key]);
    }
  }

  //load the emitter
  $emitter = $this('event');

  //get the system detail
  $emitter->emit('system-fieldset-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  //if there's an error
  if ($response->isError()) {
    return;
  }

  $errors = Validator::getUpdateErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($errors);
  }

  //----------------------------//
  // 3. Prepare Data
  //get the original for later
  $original = Fieldset::i($response->getResults());

  //----------------------------//
  // 4. Process Data
  //load fieldset
  $fieldset = Fieldset::i($data);

  try {
    $fieldset->save();
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $results = $fieldset->set('original', $original->get())->get();

  //return response format
  $response->setError(false)->setResults($results);
});
