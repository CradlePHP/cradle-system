<?php //-->

use Cradle\Package\System\Schema;

/**
 * System Collection Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-create', function ($request, $response) {
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
});

/**
 * System Collection Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-remove', function ($request, $response) {
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
});

/**
 * System Collection Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-restore', function ($request, $response) {
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
});

/**
 * System Collection Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-update', function ($request, $response) {
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
});

/**
 * System Collection [Schema] Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-%s-create', function ($request, $response) {
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
$this('event')->on('system-collection-%s-remove', function ($request, $response) {
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
$this('event')->on('system-collection-%s-restore', function ($request, $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-restore', $request, $response);
  }
});

/**
 * System Collection [Schema] Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$this('event')->on('system-collection-%s-update', function ($request, $response) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-update', $request, $response);
  }
});
