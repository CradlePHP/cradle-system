<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Schema;
use Cradle\Package\System\Model\Validator;

/**
 * System Model Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-model-create', function ($request, $response) {
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
  $errors = Validator::i($schema)->getCreateErrors($data);

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
  //trigger store create
  $results = $this->method('system-model-create-store', $data, $response);
  //if there's an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //get the primary name
  $primary = $schema->getPrimaryFieldName();

  //loop through all forward relations
  $relations = $schema->getRelations();
  foreach ($relations as $table => $relation) {
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

    //loop through yje IDs
    foreach ($data[$primary2] as $id) {
      //if id is not a number
      if (!is_numeric($id)) {
        //skip
        continue;
      }

      //link relations
      $this->method('system-relation-link', [
        'schema1' => $data['schema'],
        'schema2' => $relation['name'],
        $primary => $results[$primary],
        $primary2 => $id,
      ]);
    }
  }

  //loop through all reverse relations
  $relations = $schema->getReverseRelations();
  foreach ($relations as $table => $relation) {
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

    //loop through yje IDs
    foreach ($data[$primary2] as $id) {
      //if id is not a number
      if (!is_numeric($id)) {
        //skip
        continue;
      }

      //link relations
      $this->method('system-relation-link', [
        'schema1' => $data['schema'],
        'schema2' => $relation['name'],
        $primary => $results[$primary],
        $primary2 => $id,
      ]);
    }
  }

  //fix the results and put back the arrays
  $results = $schema
    ->model()
    ->formatter()
    ->expandData($results);

  //return response format
  $response->setError(false)->setResults($results);
});

/**
 * System Model Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-model-detail', function ($request, $response) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //----------------------------//
  // 2. Validate Data
  if ($request->getStage('schema')) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->addValidation('schema', 'Schema is required.');
  }

  //----------------------------//
  // 3. Prepare Data
  //no preparation needed
  //----------------------------//
  // 4. Process Data
  //trigger store detail
  $this->trigger('system-model-detail-store', $request, $response);

  //if there's an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  $response->setError(false);
});

/**
 * System Model Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-model-remove', function ($request, $response) {
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

  $primary = $schema->getPrimaryFieldName();
  $active = $schema->getActiveFieldName();

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
$this->on('system-model-restore', function ($request, $response) {
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

  $primary = $schema->getPrimaryFieldName();
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
$this->on('system-model-update', function ($request, $response) {
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
  $primary = $schema->getPrimaryFieldName();
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
