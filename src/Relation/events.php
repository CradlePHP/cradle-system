<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Package\System\Schema;
use Cradle\Package\System\Exception;

use Cradle\Sql\SqlException;

/**
 * Links model to relation
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-relation-link', function ($request, $response) {
    //----------------------------//
    // 0. Abort on Errors
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 1. Get Data
    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $errors = [];
    if (!isset($data['schema1'])) {
        $errors['schema1'] = 'Schema is required.';
    }

    if (!isset($data['schema2'])) {
        $errors['schema2'] = 'Schema is required.';
    }

    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    $schema = Schema::i($data['schema1']);
    $relation = $schema->getRelations($data['schema2']);

    //if no relation
    if (empty($relation)) {
        //try the other way around
        $schema = Schema::i($data['schema2']);
        $relation = $schema->getRelations($data['schema1']);
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data[$relation['primary1']], $data[$relation['primary2']])) {
        return $response->setError(true, 'No ID provided');
    }

    $primary1 = $data[$relation['primary1']];
    $primary2 = $data[$relation['primary2']];

    if (!is_numeric($primary1) || !is_numeric($primary2)) {
        return $response->setError(true, 'Invalid Id provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $modelSql = $schema->model()->service('sql');
    $modelRedis = $schema->model()->service('redis');
    $modelElastic = $schema->model()->service('elastic');

    try {
        $results = $modelSql->link(
            $relation['name'],
            $primary1,
            $primary2
        );
    } catch (SqlException $e) {
        $results = [];
    }

    //index post
    $modelElastic->update($primary1);

    //invalidate cache
    $modelRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Uninks model to relation
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-relation-unlink', function ($request, $response) {
    //----------------------------//
    // 0. Abort on Errors
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 1. Get Data
    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $errors = [];
    if (!isset($data['schema1'])) {
        $errors['schema1'] = 'Schema is required.';
    }

    if (!isset($data['schema2'])) {
        $errors['schema2'] = 'Schema is required.';
    }

    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    $schema = Schema::i($data['schema1']);
    $relation = $schema->getRelations($data['schema2']);

    //if no relation
    if (empty($relation)) {
        //try the other way around
        $schema = Schema::i($data['schema2']);
        $relation = $schema->getRelations($data['schema1']);
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data[$relation['primary1']], $data[$relation['primary2']])) {
        return $response->setError(true, 'No ID provided');
    }

    $primary1 = $data[$relation['primary1']];
    $primary2 = $data[$relation['primary2']];

    if (!is_numeric($primary1) || !is_numeric($primary2)) {
        return $response->setError(true, 'Invalid Id provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $modelSql = $schema->model()->service('sql');
    $modelRedis = $schema->model()->service('redis');
    $modelElastic = $schema->model()->service('elastic');

    try {
        $results = $modelSql->unlink(
            $relation['name'],
            $primary1,
            $primary2
        );
    } catch (SqlException $e) {
        $results = [];
    }

    //index post
    $modelElastic->update($primary1);

    //invalidate cache
    $modelRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all model from relation
 *
 * @param Request $request
 * @param Response $response
 */
$this->on('system-relation-unlink-all', function ($request, $response) {
    //----------------------------//
    // 0. Abort on Errors
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 1. Get Data
    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $errors = [];
    if (!isset($data['schema1'])) {
        $errors['schema1'] = 'Schema is required.';
    }

    if (!isset($data['schema2'])) {
        $errors['schema2'] = 'Schema is required.';
    }

    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    $schema1 = Schema::i($data['schema1']);
    $schema2 = Schema::i($data['schema2']);
    $schema1Primary = $schema1->getPrimaryFieldName();
    $schema2Primary = $schema2->getPrimaryFieldName();

    //----------------------------//
    // 2. Validate Data
    if (!isset($data[$schema1Primary]) && !isset($data[$schema2Primary])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $modelSql = $schema1->model()->service('sql');

    if (isset($data[$schema1Primary])) {
        $modelRedis = $schema1->model()->service('redis');
        $modelElastic = $schema1->model()->service('elastic');

        $results = $modelSql->unlinkAllChildren(
            $data['schema2'],
            $data[$schema1Primary]
        );

        //index post
        $modelElastic->update($data[$schema1Primary]);
    }

    if (isset($data[$schema2Primary])) {
        $modelRedis = $schema2->model()->service('redis');
        $modelElastic = $schema2->model()->service('elastic');

        $results = $modelSql->unlinkAllParents(
            $data['schema2'],
            $data[$schema2Primary]
        );

        //index post
        $modelElastic->update($data[$schema2Primary]);
    }

    //invalidate cache
    $modelRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
