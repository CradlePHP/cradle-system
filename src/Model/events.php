<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Schema;

/**
 * System Model [Schema] Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-%s-create', function ($request, $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-model-create', $request, $response);
  }
});

/**
 * System Model [Schema] Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-%s-detail', function ($request, $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-model-detail', $request, $response);
  }
});

/**
 * System Model [Schema] Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-%s-remove', function ($request, $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-model-remove', $request, $response);
  }
});

/**
 * System Model [Schema] Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-%s-restore', function ($request, $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-model-restore', $request, $response);
  }
});

/**
 * System Model [Schema] Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-%s-update', function ($request, $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-model-update', $request, $response);
  }
});

/**
 * System Model Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-create', function ($request, $response) {
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

  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  $schema = Schema::i($data['schema']);

  //----------------------------//
  // 2. Validate Data
  $errors = $schema->getErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->set('json', 'validation', $errors);
  }

  //----------------------------//
  // 3. Prepare Data
  $data = $schema->prepare($data);

  //----------------------------//
  // 4. Process Data
  $payload = $this('io')->makePayload(false);
  $emitter = $this('event');

  if ($request->meta('mysql')) {
    $payload['request']->meta('mysql', $request->meta('mysql'));
  }

  if ($request->meta('storm')) {
    $payload['request']->meta('storm', $request->meta('storm'));
  }

  if ($request->meta('storm-insert')) {
    $payload['request']->meta('storm-insert', $request->meta('storm-insert'));
  }

  $payload['request']->setStage([
    'table' => $data['schema'],
    'data' => $data
  ]);

  $emitter->method('storm-insert', $payload['request'], $response);

  if ($response->isError()) {
    return;
  }

  //get the results
  $results = $response->getResults();
  //get the primary name
  $primary = $schema->getPrimaryName();

  //loop through all forward relations
  foreach ($schema->getRelations() as $table => $relation) {
    //set the 2nd primary
    $primary2 = $relation['primary2'];
    //if id is invalid
    if (!isset($data[$primary2]) || !is_numeric($data[$primary2])) {
      //skip
      continue;
    }

    //allow linking an array of IDs
    if (!is_array($data[$primary2])) {
      $data[$primary2] = [$data[$primary2]];
    }

    //link relations
    $emitter->method('system-relation-link', [
      'schema1' => $data['schema'],
      'schema2' => $relation['name'],
      $primary => $results[$primary],
      //should consider array of ids
      $primary2 => $data[$primary2],
    ]);
  }

  //loop through all reverse relations
  foreach ($schema->getReverseRelations() as $table => $relation) {
    //set the 2nd primary
    $primary2 = $relation['primary2'];
    //if id is invalid
    if (!isset($data[$primary2]) || !is_numeric($data[$primary2])) {
      //skip
      continue;
    }

    //allow linking an array of IDs
    if (!is_array($data[$primary2])) {
      $data[$primary2] = [$data[$primary2]];
    }

    //link relations
    $emitter->method('system-relation-link', [
      'schema1' => $data['schema'],
      'schema2' => $relation['name'],
      $primary => $results[$primary],
      //should consider array of ids
      $primary2 => $data[$primary2],
    ]);
  }

  //lastly return the detail
  $emitter->method('system-model-detail', [
    $primary => $results[$primary]
  ], $response);
});

/**
 * System Model Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-detail', function ($request, $response) {
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

  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  $schema = Schema::i($data['schema']);
  //get the primary name
  $primary = $schema->getPrimaryName();

  //determine key and value
  $key = $value = null;
  //the obvious thing is primary
  if (array_key_exists($primary, $data)) {
    $key = $primary;
    $value = $data[$primary];
  } else {
    //look for any unique keys
    foreach ($schema->getFields('unique') as $name => $field) {
      if (array_key_exists($name, $data)) {
        $key = $name;
        $value = $data[$name];
        break;
      }
    }
  }

  //----------------------------//
  // 2. Validate Data
  //we need an id
  if (!$value) {
    return $response->setError(true, 'Invalid ID');
  }

  //----------------------------//
  // 3. Prepare Data
  $system = $this->package('/module/cradle-system');

  //allow columns
  if (!isset($data['columns'])) {
    $data['columns'] = '*';
  }

  //compute joins
  if (!isset($data['join'])) {
    $data['join'] = [];
  }

  $columns = $data['columns'];
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $system->getInnerJoins($schema, $data['join']);
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = [['where' => $key . ' =%s', 'binds' => [$value]]];

  //----------------------------//
  // 4. Process Data
  $payload = $this('io')->makePayload(false);
  $emitter = $this('event');

  if ($request->meta('mysql')) {
    $payload['request']->meta('mysql', $request->meta('mysql'));
  }

  if ($request->meta('storm')) {
    $payload['request']->meta('storm', $request->meta('storm'));
  }

  if ($request->meta('storm-search')) {
    $payload['request']->meta('storm-search', $request->meta('storm-search'));
  }

  $payload['request']->setStage([
    'table' => $data['schema'],
    'columns' => $columns,
    'joins' => $joins,
    'filters' => $filters,
    'start' => 0,
    'range' => 1
  ]);

  $results = $emitter->method('storm-search', $payload['request']);

  if (!isset($results[0])) {
    return $response->setError(true, 'Not Found');
  }

  //organize all the results
  $results = $system->organizeRow($results[0]);
  $id = $results[$data['schema']][$primary];

  //next, attach all the joins
  $joins = $system->getJoinFilters($schema, $data['join']);
  //attach forward joins
  foreach ($schema->getRelations() as $relationTable => $relation) {
    $name = $group = $relation->getName();
    $primary2 = $relation->getPrimaryName();
    //we already joined 1:1, dont do it again
    //if it's not on the join list
    if ($relation['many'] == 1 || !in_array($name, $joins)) {
      continue;
    }

    //case for post_post
    if ($name === $data['schema']) {
      $group = '_children';
    }

    //make a default
    $results[$group] = null;

    //make a separate payload
    $payload = $this('io')->makePayload(false);

    if ($request->meta('mysql')) {
      $payload['request']->meta('mysql', $request->meta('mysql'));
    }

    if ($request->meta('storm')) {
      $payload['request']->meta('storm', $request->meta('storm'));
    }

    //filter settings
    $payload['request']->setStage([
      'table' => $name,
      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      'joins' => [
        ['type' => 'inner', 'table' => $relationTable, 'where' => $primary2],
        ['type' => 'inner', 'table' => $data['schema'], 'where' => $primary]
      ],
      //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
      'filters' => [
        ['where' => $primary . ' =%s', 'binds' => [$id]]
      ],
      'range' => 0
    ]);

    //if 1:0
    if ($relation['many'] == 0) {
      //we only need one
      $payload['request']->setStage('range', 1);
    }

    $child = $emitter->method('storm-search', $payload['request']);

    //if 1:0
    if ($relation['many'] == 0 && isset($child[0])) {
      //we only need one
      $results[$group] = $child[0];
      continue;
    }

    $results[$group] = $child;
  }

  //attach reverse joins
  foreach ($schema->getReverseRelations() as $relation) {
    $name = $relation->getName();
    $primary2 = $relation->getPrimaryName();
    //only join 1:N and N:N
    //if it's not on the join list
    if ($relation['many'] < 2 || !in_array($name, $joins)) {
      continue;
    }

    //make a separate payload
    $payload = $this('io')->makePayload(false);

    if ($request->meta('mysql')) {
      $payload['request']->meta('mysql', $request->meta('mysql'));
    }

    if ($request->meta('storm')) {
      $payload['request']->meta('storm', $request->meta('storm'));
    }

    //filter settings
    $payload['request']->setStage([
      'table' => $name,
      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      'joins' => [
        ['type' => 'inner', 'table' => $relationTable, 'where' => $primary2],
        ['type' => 'inner', 'table' => $data['schema'], 'where' => $primary]
      ],
      //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
      'filters' => [
        ['where' => $primary . ' =%s', 'binds' => [$id]]
      ],
      'range' => 0
    ]);

    $results[$name] = $emitter->method('storm-search', $payload['request']);
  }

  $response->setError(false)->setResults($results);
});

/**
 * System Model Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-remove', function ($request, $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get the object detail
  $this('event')->trigger('system-model-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 3. Prepare Data
  $data = $response->getResults();

  if (!$request->hasStage('schema')) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  $schema = Schema::i($request->getStage('schema'));

  $primary = $schema->getPrimaryName();
  $active = $schema->getFields('active');

  //----------------------------//
  // 4. Process Data
  //this/these will be used a lot
  $modelSql = $schema->model()->service('sql');
  $modelRedis = $schema->model()->service('redis');
  $modelElastic = $schema->model()->service('elastic');

  //save to database
  if ($active) {
    $payload = [];
    $payload[$primary] = $data[$primary];
    $payload[$active] = 0;

    $results = $modelSql->update($payload);
  } else {
    $results = $modelSql->remove($data[$primary]);
  }

  //remove from index
  $modelElastic->remove($data[$primary]);

  //invalidate cache
  $uniques = $schema->getUniqueFieldNames();
  foreach ($uniques as $unique) {
    if (isset($data[$unique])) {
      $modelRedis->removeDetail($unique . '-' . $data[$unique]);
    }
  }

  $modelRedis->removeSearch();

  //add the schema to the results, so we know which table was changed
  $results['schema'] = $request->getStage('schema');
  $response->setError(false)->setResults($results);
});

/**
 * System Model Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-restore', function ($request, $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get the object detail
  $this->trigger('system-model-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 3. Prepare Data
  $data = $response->getResults();

  if (!$request->hasStage('schema')) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  $schema = Schema::i($request->getStage('schema'));

  $primary = $schema->getPrimaryName();
  $active = $schema->getActiveFieldName();

  //----------------------------//
  // 4. Process Data
  //this/these will be used a lot
  $modelSql = $schema->model()->service('sql');
  $modelRedis = $schema->model()->service('redis');
  $modelElastic = $schema->model()->service('elastic');

  //save to database
  $payload = [];
  $payload[$primary] = $data[$primary];
  $payload[$active] = 1;

  $results = $modelSql->update($payload);

  //create index
  $modelElastic->create($data[$primary]);

  //invalidate cache
  $modelRedis->removeSearch();

  $results['schema'] = $request->getStage('schema');
  $response->setError(false)->setResults($results);
});

/**
 * System Model Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-model-update', function ($request, $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get the object detail
  $this->trigger('system-model-detail', $request, $response);

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

  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  $schema = Schema::i($data['schema']);

  //----------------------------//
  // 2. Validate Data
  $errors = $schema
    ->model()
    ->validator()
    ->getUpdateErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->set('json', 'validation', $errors);
  }

  //----------------------------//
  // 3. Prepare Data
  $data = $schema
    ->model()
    ->formatter()
    ->formatData($data);

  //----------------------------//
  // 4. Process Data
  //this/these will be used a lot
  $modelSql = $schema->model()->service('sql');
  $modelRedis = $schema->model()->service('redis');
  $modelElastic = $schema->model()->service('elastic');

  //save object to database
  $results = $modelSql->update($data);

  //get the primary value
  $primary = $schema->getPrimaryName();
  $relations = $schema->getRelations();
  $reverseRelations = $schema->getReverseRelations();

  //loop through relations
  foreach ($relations as $table => $relation) {
    //if 1:N, skip
    if ($relation['many'] > 1) {
      continue;
    }

    $current = $response->getResults();
    $lastId = null;

    // is the relation array?
    if (isset($current[$relation['name']])
    && is_array($current[$relation['name']])
    && isset($current[$relation['name']][$relation['primary2']])) {
      // get the primary id from the array
      $lastId = $current[$relation['name']][$relation['primary2']];

    // relation already merged with the primary?
    } else if (isset($current[$relation['primary2']])) {
      $lastId = $current[$relation['primary2']];
    }

    //if 0:1 and no primary
    if ($relation['many'] === 0
      && (
        !isset($data[$relation['primary2']])
        || !is_numeric($data[$relation['primary2']])
      )
    ) {
      //remove last id
      $modelSql->unlink(
        $relation['name'],
        $primary,
        $lastId
      );

      continue;
    }

    if (isset($data[$relation['primary2']])
      && is_numeric($data[$relation['primary2']])
      && $lastId != $data[$relation['primary2']]
    ) {
      //remove last id
      $modelSql->unlink(
        $relation['name'],
        $results[$primary],
        $lastId
      );

      //link current id
      $modelSql->link(
        $relation['name'],
        $results[$primary],
        $data[$relation['primary2']]
      );
    }
  }

  //only for root reverse relation
  if (!isset($data['relation_recursive'])) {
    //loop through reverse relations
    foreach ($reverseRelations as $table => $relation) {
      //deal with same table name
      if ($relation['source']['name'] === $relation['name']
        //skip history
        || $relation['source']['name'] === 'history'
        ) {
        continue;
      }
      //get primmary id
      $primaryId = $results['schema']. '_id';
      //get dynamic schema
      $schema = Schema::i($relation['source']['name']);
      //set schema sql
      $schemaSql = $schema->model()->service('sql');
      //filter by primary id
      $filter['filter'][$primaryId] =  $results[$results['schema']. '_id'];
      //set range to 0
      $filter['range'] = 0;
      //get rows
      $rows = $schemaSql->search($filter);

      //loop elastic update
      if ($rows) {
        foreach ($rows['rows'] as $key => $row) {
          $payload = $this->makePayload();

          //set dynamic column id
          $columnId = $relation['source']['name']. '_id';
          $payload['request']
            ->setStage('schema', $relation['source']['name'])
            ->setStage('relation_recursive', true)
            ->setStage($columnId, $row[$columnId]);

          //set queue data
          $queueData = $payload['request']->getStage();
          $queuePackage = $this->package('cradlephp/cradle-queue');
          if (!$queuePackage->queue('system-model-update', $queueData)) {
            //update manually after the connection
            $this->trigger('system-model-update', $payload['request'], $payload['response']);
          }
        }
      }
    }
  }

  //index object
  $modelElastic->update($results[$primary]);

  //invalidate cache
  $uniques = $schema->getUniqueFieldNames();
  foreach ($uniques as $unique) {
    if (isset($data[$unique])) {
      $modelRedis->removeDetail($unique . '-' . $data[$unique]);
    }
  }

  $modelRedis->removeSearch();

  //fix the results and put back the arrays
  $results = $schema
    ->model()
    ->formatter()
    ->expandData($results);

  //add the original
  $results['original'] = $original;

  //return response format
  $response->setError(false)->setResults($results);
});
