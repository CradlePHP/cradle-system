<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Schema;
use Cradle\Package\System\Exception;

/**
 * System Model Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-model-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if (!isset($data['schema'])) {
        throw Exception::forNoSchema();
    }

    $schema = Schema::i($data['schema']);

    //
    // FIX: For import or in any part of the system
    // if primary is set but doesn't have a value.
    //
    if (isset($data[$schema->getPrimaryFieldName()])
        && empty($data[$schema->getPrimaryFieldName()])
    ) {
        // remove the field instead
        unset($data[$schema->getPrimaryFieldName()]);
    }

    //----------------------------//
    // 2. Validate Data
    $errors = $schema
        ->model()
        ->validator()
        ->getCreateErrors($data);

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
    $results = $modelSql->create($data);

    //get the primary value
    $primary = $results[$schema->getPrimaryFieldName()];
    $relations = $schema->getRelations();

    //loop through relations
    foreach ($relations as $table => $relation) {
        //link relations
        if (isset($data[$relation['primary2']])
            && is_numeric($data[$relation['primary2']])
        ) {
            $modelSql->link(
                $relation['name'],
                $primary,
                $data[$relation['primary2']]
            );
        }
    }

    //index object
    $modelElastic->create($primary);

    //invalidate cache
    $modelRedis->removeSearch();

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
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if (!isset($data['schema'])) {
        throw Exception::forNoSchema();
    }

    $schema = Schema::i($data['schema']);

    $id = $key = null;
    $uniques = $schema->getUniqueFieldNames();
    foreach ($uniques as $unique) {
        if (isset($data[$unique])) {
            $id = $data[$unique];
            $key = $unique;
            break;
        }
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
    //this/these will be used a lot
    $modelSql = $schema->model()->service('sql');
    $modelRedis = $schema->model()->service('redis');
    $modelElastic = $schema->model()->service('elastic');

    $results = null;

    //if no flag
    if (!$request->hasStage('nocache')) {
        //get it from cache
        $results = $modelRedis->getDetail($key . '-' . $id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasStage('noindex')) {
            //get it from index
            $results = $modelElastic->get($key, $id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $modelSql->get($key, $id);
        }

        if ($results) {
            //cache it from database or index
            $modelRedis->createDetail($key . '-' . $id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Model Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-model-remove', function ($request, $response) {
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
        throw Exception::forNoSchema();
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
        throw Exception::forNoSchema();
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
 * System Model Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-model-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if (!isset($data['schema'])) {
        throw Exception::forNoSchema();
    }

    $schema = Schema::i($data['schema']);

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $modelSql = $schema->model()->service('sql');
    $modelRedis = $schema->model()->service('redis');
    $modelElastic = $schema->model()->service('elastic');

    $results = false;

    //if no flag
    if (!$request->hasStage('nocache')) {
        //get it from cache
        $results = $modelRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasStage('noindex')) {
            //get it from index
            $results = $modelElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $modelSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $modelRedis->createSearch($data, $results);
        }
    }

    //set response format
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
        throw Exception::forNoSchema();
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

/**
 * System Model Item Import Job
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-model-import', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //set counter
    $results = [
        'data' => [],
        'new' => 0,
        'old' => 0
    ];

    if (!isset($data['schema'])) {
        throw Exception::forNoSchema();
    }

    $schema = Schema::i($data['schema']);
    $schema2 = [];

    //check if relation exists
    if ($request->hasStage('schema2')) {
        $reverserRelations = $schema->getReverseRelations(2);
        $relation = $request->getStage('schema2');
        $possibleRelation = sprintf('%s_%s', $relation, $schema->getName());

        //check if relation exists
        if (array_key_exists($possibleRelation, $reverserRelations)) {
            $schema2 = Schema::i($relation);
        }

        //return if empty
        if (empty($schema2)) {
            return $response
                ->setError(true, 'Invalid Schema Relation');
        }
    }

    //----------------------------//
    // 2. Validate Data
    //validate data
    $errors = [];

    // if we don't have rows
    if (!is_array($data['rows'])) {
        return $response
            ->setError(true, 'Data is empty')
            ->set('json', 'validation', []);
    }

    foreach ($data['rows'] as $i => $row) {
        $error = $schema
            ->model()
            ->validator()
            ->getCreateErrors($row);

        //if there are errors
        if (!empty($error)) {
            $errors[$i] = $error;
        }
    }

    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Row/s')
            ->set('json', 'validation', $errors);
    }

    // There is no error,
    // So proceed on adding/updating the items one by one
    foreach ($data['rows'] as $i => $row) {
        $created = $schema->getCreatedFieldName();
        if ($created && isset($row[$created])) {
            unset($row[$created]);
        }

        $updated = $schema->getUpdatedFieldName();
        if ($updated && isset($row[$updated])) {
            unset($row[$updated]);
        }

        $payload = $this->makePayload();

        $payload['request']
            ->setStage($row)
            ->setStage('schema', $data['schema']);

        $this->trigger(
            'system-model-detail',
            $payload['request'],
            $payload['response']
        );

        if ($payload['response']->hasResults()) {
            // trigger single object update event
            $this->trigger(
                'system-model-update',
                $payload['request'],
                $payload['response']
            );

            // check response if there is an error
            if ($payload['response']->isError()) {
                $results['data'][$i] = [
                    'action' => 'update',
                    'row' => [],
                    'error' => $payload['response']->getMessage()
                ];
                continue;
            }

            //increment old counter
            $results['data'][$i] = [
                'action' => 'update',
                'row' => $payload['response']->getResults(),
                'error' => false
            ];

            $results['old'] ++;
            continue;
        }

        // trigger single object update event
        $this->trigger(
            'system-model-create',
            $payload['request'],
            $payload['response']
        );

        // check response if there is an error
        if ($payload['response']->isError()) {
            $results['data'][$i] = [
                'action' => 'create',
                'row' => [],
                'error' => $payload['response']->getMessage()
            ];
            continue;
        }

        //increment old counter
        $results['data'][$i] = [
            'action' => 'create',
            'row' => $payload['response']->getResults(),
            'error' => false
        ];

        if (!empty($schema2)) {
            //for linking relation
            $payload = $this->makePayload();
            $payload['request']
                ->setStage('schema2', $schema->getName())
                ->setStage('schema1', $schema2->getName());

            //so it must have been successful
            //lets link the tables now
            $primary1 = $schema->getPrimaryFieldName();
            $primary2 = $schema2->getPrimaryFieldName();

            if ($primary1 == $primary2) {
                $primary1 = sprintf('%s_2', $primary1);
                $primary2 = sprintf('%s_1', $primary2);
            }

            //set the stage to link
            $payload['request']
                ->setStage(
                    $primary1,
                    $payload['response']->getResults(
                        $schema->getPrimaryFieldName()
                    )
                )
                ->setStage($primary2, $request->getStage('id'));

            //now link it
            $this->trigger('system-relation-link', $payload['request'], $payload['response']);
        }

        $results['new'] ++;
    }

    $response->setError(false)->setResults($results);
});
