<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Schema;
use Cradle\Package\System\Exception;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Basic CRUD
 */
cradle(function() {
    /**
     * System Object Create Job
     *
     * @param Request $request
     * @param Response $response
     */
    $this->on('system-object-create', function ($request, $response) {
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
        $errors = $schema
            ->object()
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
            ->object()
            ->formatter()
            ->formatData(
                $data,
                $this->package('global')->service('s3-main'),
                $this->package('global')->path('upload')
            );

        //----------------------------//
        // 4. Process Data
        //this/these will be used a lot
        $objectSql = $schema->object()->service('sql');
        $objectRedis = $schema->object()->service('redis');
        $objectElastic = $schema->object()->service('elastic');
        //save object to database
        $results = $objectSql->create($data);

        //get the primary value
        $primary = $results[$schema->getPrimaryFieldName()];
        $relations = $schema->getRelations();

        //loop through relations
        foreach ($relations as $table => $relation) {
            //link relations
            if (isset($data[$relation['primary2']])
                && is_numeric($data[$relation['primary2']])
            ) {
                $objectSql->link(
                    $relation['name'],
                    $primary,
                    $data[$relation['primary2']]
                );
            }
        }

        //index object
        $objectElastic->create($primary);

        //invalidate cache
        $objectRedis->removeSearch();

        //return response format
        $response->setError(false)->setResults($results);
    });

    /**
     * System Object Detail Job
     *
     * @param Request $request
     * @param Response $response
     */
    $this->on('system-object-detail', function ($request, $response) {
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
        $slugs = $schema->getSlugableFieldNames($schema->getPrimaryFieldName());

        foreach ($slugs as $slug) {
            if (isset($data[$slug])) {
                $id = $data[$slug];
                $key = $slug;
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
        $objectSql = $schema->object()->service('sql');
        $objectRedis = $schema->object()->service('redis');
        $objectElastic = $schema->object()->service('elastic');

        $results = null;

        //if no flag
        if (!$request->hasGet('nocache')) {
            //get it from cache
            $results = $objectRedis->getDetail($key . '-' . $id);
        }

        //if no results
        if (!$results) {
            //if no flag
            if (!$request->hasGet('noindex')) {
                //get it from index
                $results = $objectElastic->get($key, $id);
            }

            //if no results
            if (!$results) {
                //get it from database
                $results = $objectSql->get($key, $id);
            }

            if ($results) {
                //cache it from database or index
                $objectRedis->createDetail($key . '-' . $id, $results);
            }
        }

        if (!$results) {
            return $response->setError(true, 'Not Found');
        }

        $response->setError(false)->setResults($results);
    });

    /**
     * System Object Remove Job
     *
     * @param Request $request
     * @param Response $response
     */
    $this->on('system-object-remove', function ($request, $response) {
        //----------------------------//
        // 1. Get Data
        //get the object detail
        $this->trigger('system-object-detail', $request, $response);

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
        $objectSql = $schema->object()->service('sql');
        $objectRedis = $schema->object()->service('redis');
        $objectElastic = $schema->object()->service('elastic');

        //save to database
        if ($active) {
            $payload = [];
            $payload[$primary] = $data[$primary];
            $payload[$active] = 0;

            $results = $objectSql->update($payload);
        } else {
            $results = $objectSql->remove($data[$primary]);
        }

        //remove from index
        $objectElastic->remove($data[$primary]);

        //invalidate cache
        $slugs = $schema->getSlugableFieldNames($primary);
        foreach ($slugs as $slug) {
            if (isset($data[$slug])) {
                $objectRedis->removeDetail($data[$slug]);
            }
        }

        $objectRedis->removeSearch();

        $response->setError(false)->setResults($results);
    });

    /**
     * System Object Restore Job
     *
     * @param Request $request
     * @param Response $response
     */
    $this->on('system-object-restore', function ($request, $response) {
        //----------------------------//
        // 1. Get Data
        //get the object detail
        $this->trigger('system-object-detail', $request, $response);

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
        $objectSql = $schema->object()->service('sql');
        $objectRedis = $schema->object()->service('redis');
        $objectElastic = $schema->object()->service('elastic');

        //save to database
        $payload = [];
        $payload[$primary] = $data[$primary];
        $payload[$active] = 1;

        $results = $objectSql->update($payload);

        //create index
        $objectElastic->create($data[$primary]);

        //invalidate cache
        $objectRedis->removeSearch();

        $response->setError(false)->setResults($results);
    });

    /**
     * System Object Search Job
     *
     * @param Request $request
     * @param Response $response
     */
    $this->on('system-object-search', function ($request, $response) {
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
        $objectSql = $schema->object()->service('sql');
        $objectRedis = $schema->object()->service('redis');
        $objectElastic = $schema->object()->service('elastic');

        $results = false;

        //if no flag
        if (!$request->hasGet('nocache')) {
            //get it from cache
            $results = $objectRedis->getSearch($data);
        }

        //if no results
        if (!$results) {
            //if no flag
            if (!$request->hasGet('noindex')) {
                //get it from index
                $results = $objectElastic->search($data);
            }

            //if no results
            if (!$results) {
                //get it from database
                $results = $objectSql->search($data);
            }

            if ($results) {
                //cache it from database or index
                $objectRedis->createSearch($data, $results);
            }
        }

        //set response format
        $response->setError(false)->setResults($results);
    });

    /**
     * System Object Update Job
     *
     * @param Request $request
     * @param Response $response
     */
    $this->on('system-object-update', function ($request, $response) {
        //----------------------------//
        // 1. Get Data
        //get the object detail
        $this->trigger('system-object-detail', $request, $response);

        //if there's an error
        if ($response->isError()) {
            return;
        }

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
            ->object()
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
            ->object()
            ->formatter()
            ->formatData(
                $data,
                $this->package('global')->service('s3-main'),
                $this->package('global')->path('upload')
            );

        //----------------------------//
        // 4. Process Data
        //this/these will be used a lot
        $objectSql = $schema->object()->service('sql');
        $objectRedis = $schema->object()->service('redis');
        $objectElastic = $schema->object()->service('elastic');

        //save object to database
        $results = $objectSql->update($data);

        //get the primary value
        $primary = $schema->getPrimaryFieldName();
        $relations = $schema->getRelations();

        //loop through relations
        foreach ($relations as $table => $relation) {
            //if 1:N, skip
            if ($relation['many'] > 1) {
                continue;
            }

            $lastId = $response->getResults($relation['primary2']);

            //if 0:1 and no primary
            if ($relation['many'] === 0
                && (
                    !isset($data[$relation['primary2']])
                    || !is_numeric($data[$relation['primary2']])
                )
            ) {
                //remove last id
                $objectSql->unlink(
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
                $objectSql->unlink(
                    $relation['name'],
                    $results[$primary],
                    $lastId
                );

                //link current id
                $objectSql->link(
                    $relation['name'],
                    $results[$primary],
                    $data[$relation['primary2']]
                );
            }
        }

        //index object
        $objectElastic->update($results[$primary]);

        //invalidate cache
        $slugs = $schema->getSlugableFieldNames($primary);
        foreach ($slugs as $slug) {
            if (isset($data[$slug])) {
                $objectRedis->removeDetail($data[$slug]);
            }
        }

        $objectRedis->removeSearch();

        //return response format
        $response->setError(false)->setResults($results);
    });

    /**
     * System Object Item Import Job
     *
     * @param Request $request
     * @param Response $response
     */
    $this->on('system-object-import', function ($request, $response) {
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

        foreach ($data['rows'] as $i => $row) {
            $error = $schema
                ->object()
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

            $rowRequest = Request::i()
                ->setStage($row)
                ->setStage('schema', $data['schema']);

            $rowResponse = Response::i()->load();

            $this->trigger('system-object-detail', $rowRequest, $rowResponse);

            if ($rowResponse->hasResults()) {
                // trigger single object update event
                $this->trigger('system-object-update', $rowRequest, $rowResponse);

                // check response if there is an error
                if ($rowResponse->isError()) {
                    $results['data'][$i] = [
                        'action' => 'update',
                        'row' => [],
                        'error' => $rowResponse->getMessage()
                    ];
                    continue;
                }

                //increment old counter
                $results['data'][$i] = [
                    'action' => 'update',
                    'row' => $rowResponse->getResults(),
                    'error' => false
                ];

                $results['old'] ++;
                continue;
            }

            // trigger single object update event
            $this->trigger('system-object-create', $rowRequest, $rowResponse);

            // check response if there is an error
            if ($rowResponse->isError()) {
                $results['data'][$i] = [
                    'action' => 'create',
                    'row' => [],
                    'error' => $rowResponse->getMessage()
                ];
                continue;
            }

            //increment old counter
            $results['data'][$i] = [
                'action' => 'create',
                'row' => $rowResponse->getResults(),
                'error' => false
            ];

            if (!empty($schema2)) {
                //for linking relation
                $linkRequest = Request::i()
                    ->setStage('schema2', $schema->getName())
                    ->setStage('schema1', $schema2->getName());

                $linkResponse = Response::i()->load();

                //so it must have been successful
                //lets link the tables now
                $primary1 = $schema->getPrimaryFieldName();
                $primary2 = $schema2->getPrimaryFieldName();

                if ($primary1 == $primary2) {
                    $primary1 = sprintf('%s_2', $primary1);
                    $primary2 = sprintf('%s_1', $primary2);
                }

                //set the stage to link
                $linkRequest
                    ->setStage($primary1, $rowResponse->getResults($schema->getPrimaryFieldName()))
                    ->setStage($primary2, $request->getStage('id'));

                //now link it
                $this->trigger('system-relation-link', $linkRequest, $linkResponse);
            }

            $results['new'] ++;
        }

        $response->setError(false)->setResults($results);
    });
});
