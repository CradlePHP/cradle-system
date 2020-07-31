<?php //-->

use Cradle\Package\System\Schema;
use Cradle\Package\System\SystemException;

use Cradle\IO\Request\RequestInterface;
use Cradle\IO\Response\ResponseInterface;

/**
 * System Collection Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-create', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 2. Validate Data
  //must have schema
  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  //must have rows
  if (!isset($data['rows'])
    || !is_array($data['rows'])
    || empty($data['rows'])
  ) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('rows', 'Missing rows.');
  }

  try { //to load schema
    $schema = Schema::load($data['schema']);
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', $e->getMessage());
  }

  $errors = [];
  //get errors per row
  foreach ($data['rows'] as $i => $row) {
    $error = $schema->getErrors($row);
    //if there is an error
    if (!empty($error)) {
      //add it to the errors list
      $errors[$i] = $error;
    }
  }

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('rows', $errors);
  }

  //----------------------------//
  // 3. Prepare Data
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  //get the primary name
  $primary = $schema->getPrimaryName();

  //for each row
  foreach ($data['rows'] as $i => $row) {
    //prepare the data
    $data['rows'][$i] = $schema->prepare($row);
    //dont allow to insert the primary id
    unset($data['rows'][$i][$primary]);
  }

  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'rows' => $data['rows']
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->method('system-store-insert', $payload, $response);

  if ($response->isError() || !$response->hasResults()) {
    return;
  }

  //get the last id
  $lastId = $response->getResults();

  foreach ($data['rows'] as $i => $row) {
    //re insert the id into the rows
    //ex. 10 is the last id and there are 3 rows
    // 1st = 10 - (3 - (0 + 1)) = 8
    // 2nd = 10 - (3 - (1 + 1)) = 9
    // 3rd = 10 - (3 - (2 + 1)) = 10
    if (!isset($data['rows'][$i][$primary])) {
      $data['rows'][$i][$primary] = $lastId - (count($data['rows']) - ($i + 1));
    }

    $row[$primary] = $data['rows'][$i][$primary];

    //next we need to consider all the relations

    //loop through all forward relations
    foreach ($schema->getRelations() as $table => $relation) {
      //set the 2nd primary
      $primary2 = $relation['primary2'];
      //if id is invalid
      if (!isset($row[$primary2]) || !is_numeric($row[$primary2])) {
        //skip
        continue;
      }

      //link relations
      //NOTE: PONS (loop in loop db call)
      $emitter->method('system-relation-link', [
        'schema1' => $data['schema'],
        'schema2' => $relation['name'],
        $primary => $row[$primary],
        $primary2 => $row[$primary2],
      ]);
    }

    //loop through all reverse relations
    foreach ($schema->getReverseRelations() as $table => $relation) {
      //set the 2nd primary
      $primary2 = $relation['primary2'];
      //if id is invalid
      if (!isset($row[$primary2]) || !is_numeric($row[$primary2])) {
        //skip
        continue;
      }

      //link relations
      $emitter->method('system-relation-link', [
        'schema1' => $relation['name'],
        'schema2' => $data['schema'],
        $primary => $row[$primary],
        $primary2 => $row[$primary2],
      ]);
    }
  }

  $response->setError(false)->setResults('rows', $data['rows']);
});

/**
 * System Collection Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-remove', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //compute joins
  if (!isset($data['join'])) {
    $data['join'] = [];
  }

  //----------------------------//
  // 2. Validate Data
  //must have schema
  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  try { //to load schema
    $schema = Schema::load($data['schema']);
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', $e->getMessage());
  }

  //load system package now
  $system = $this('cradlephp/cradle-system');
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = $system->mapQuery($data);
  if (empty($filters)) {
    return $response->setError(true, 'Missing Filters');
  }

  //----------------------------//
  // 3. Prepare Data
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  //we need active to determine if we should update or delete
  $active = $schema->getFields('active');
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $system->getInnerJoins($schema, $data['join']);

  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'joins' => $joins,
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  //if there's an active field
  if (!empty($active)) {
    //get the active field name
    $active = array_keys($active)[0];
    $payload->setStage('data', $active, 0);
    //update
    $emitter->method('system-store-update', $payload, $response);
  } else {
    //delete
    $emitter->method('system-store-delete', $payload, $response);
  }
});

/**
 * System Collection Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-restore', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //compute joins
  if (!isset($data['join'])) {
    $data['join'] = [];
  }

  //----------------------------//
  // 2. Validate Data
  //must have schema
  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  try { //to load schema
    $schema = Schema::load($data['schema']);
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', $e->getMessage());
  }

  //get active
  $active = $schema->getFields('active');
  if (empty($active)) {
    return $response->setError(true, 'Cannot be restored');
  }

  //----------------------------//
  // 3. Prepare Data
  //load system package
  $system = $this('cradlephp/cradle-system');
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  //get the active field name
  $active = array_keys($active)[0];
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $system->getInnerJoins($schema, $data['join']);
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = $system->mapQuery($data);

  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'data' => [ $active => 1 ],
    'joins' => $joins,
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->method('system-store-update', $payload, $response);
});

/**
 * System Collection Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-search', function (RequestInterface $request, ResponseInterface $response) {
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

  //allow columns
  if (!isset($data['columns'])) {
    $data['columns'] = '*';
  }

  //compute joins
  if (!isset($data['join'])) {
    $data['join'] = [];
  }

  //----------------------------//
  // 2. Validate Data
  //must have schema
  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  try { //to load schema
    $schema = Schema::load($data['schema']);
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', $e->getMessage());
  }

  //----------------------------//
  // 3. Prepare Data
  //load system package now
  $system = $this('cradlephp/cradle-system');
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  //get columns
  $columns = $data['columns'];
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $system->getInnerJoins($schema, $data['join']);
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = $system->mapQuery($data);

  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'columns' => $columns,
    'joins' => $joins,
    'filters' => $filters
  ]);

  if (isset($data['start']) && is_numeric($data['start'])) {
    $payload->setStage('start', $data['start']);
  }

  if (isset($data['range']) && is_numeric($data['range'])) {
    $payload->setStage('range', $data['range']);
  }

  if (isset($data['with_total'])) {
    $payload->setStage('with_total', $data['with_total']);
  }

  //----------------------------//
  // 4. Process Data
  $emitter->method('system-store-search', $payload, $response);
});

/**
 * System Collection Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-update', function (RequestInterface $request, ResponseInterface $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //compute joins
  if (!isset($data['join'])) {
    $data['join'] = [];
  }

  //----------------------------//
  // 2. Validate Data
  //must have schema
  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  try { //to load schema
    $schema = Schema::load($data['schema']);
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', $e->getMessage());
  }

  //----------------------------//
  // 3. Prepare Data
  //load system package now
  $system = $this('cradlephp/cradle-system');
  //make a new payload
  $payload = $request->clone(true);
  //load the emitter
  $emitter = $this('event');

  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $system->getInnerJoins($schema, $data['join']);
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = $system->mapQuery($data);

  //prepare data
  $prepared = $schema->prepare($data);
  //dont allow to update the primary id
  unset($prepared[$schema->getPrimaryName()]);

  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'data' => $prepared,
    'joins' => $joins,
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->method('system-store-update', $payload, $response);
});

/**
 * System Collection [Schema] Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-%s-create', function (RequestInterface $request, ResponseInterface $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-create', $request, $response);
  }
});

/**
 * System Collection [Schema] Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-%s-remove', function (RequestInterface $request, ResponseInterface $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-remove', $request, $response);
  }
});

/**
 * System Collection [Schema] Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-%s-restore', function (RequestInterface $request, ResponseInterface $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-restore', $request, $response);
  }
});

/**
 * System Collection [Schema] Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-%s-search', function (RequestInterface $request, ResponseInterface $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-search', $request, $response);
  }
});

/**
 * System Collection [Schema] Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-%s-update', function (RequestInterface $request, ResponseInterface $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-update', $request, $response);
  }
});
