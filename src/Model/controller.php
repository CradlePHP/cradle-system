<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\File;
use Cradle\Package\System\Schema;

/**
 * Render the System Model Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/model/:schema/search', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
    }

    //set a default range
    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        foreach ($request->getStage('filter') as $key => $value) {
            //if invalid key format or there is no value
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $key) || !strlen($value)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        foreach ($request->getStage('order') as $key => $value) {
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $key)) {
                $request->removeStage('order', $key);
            }
        }
    }

    //trigger job
    $this->trigger('system-model-search', $request, $response);

    //if we only want the raw data
    if ($request->getStage('render') === 'false') {
        return;
    }

    //form the data
    $data = array_merge(
        //we need to case for things like
        //filter and sort on the template
        $request->getStage(),
        //this is from the search event
        $response->getResults()
    );

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

    //if there's an active field
    if ($data['schema']['active']) {
        //find it
        foreach ($data['schema']['filterable'] as $i => $filter) {
            //if we found it
            if ($filter === $data['schema']['active']) {
                //remove it from the filters
                unset($data['schema']['filterable'][$i]);
            }
        }

        //reindex filterable
        $data['schema']['filterable'] = array_values($data['schema']['filterable']);
    }

    $data['filterable_relations'] = [];
    foreach ($data['schema']['relations'] as $relation) {
        if ($relation['many'] < 2) {
            $data['filterable_relations'][] = $relation;
        }
    }

    //determine valid relations
    $data['valid_relations'] = [];

    //if there's active filter, get its value
    //for search purposes
    if (isset($data['filter'])) {
        $data['filter']['active'] = null;

        foreach ($data['filter'] as $filter => $value) {
            if ($filter === $data['schema']['active']) {
                $data['filter']['active'] = $value;
            }
        }
    }

    $this->trigger('system-schema-search', $request, $response);

    foreach ($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
    }

    //----------------------------//
    // 2. Render Template
    //set the class name
    $class = 'page-admin-system-model-search page-admin';

    $template = __DIR__ . '/template';
    if (is_dir($response->getPage('template_root'))) {
        $template = $response->getPage('template_root');
    }

    $partials = __DIR__ . '/template';
    if (is_dir($response->getPage('partials_root'))) {
        $partials = $response->getPage('partials_root');
    }

    //render the body
    $body = $this
        ->package('cradlephp/cradle-system')
        ->template(
            'search',
            $data,
            [
                'search_head',
                'search_form',
                'search_filters',
                'search_actions',
                'search_row_format',
                'search_row_actions'
            ],
            $template,
            $partials
        );

    //set content
    $response
        ->setPage('title', $data['schema']['plural'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //render page
    $this->trigger('admin-render-page', $request, $response);
});

/**
 * Render the System Model Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/model/:schema/create', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $global = $this->package('global');
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //pass the item with only the post data
    $data = ['item' => $request->getPost()];

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //for ?copy=1 functionality
    if (empty($data['item']) && is_numeric($request->getStage('copy'))) {
        //table_id, 1 for example
        $request->setStage(
            $schema->getPrimaryFieldName(),
            $request->getStage('copy')
        );

        //get the original table row
        $this->trigger('system-model-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            $global->flash($response->getMessage(), 'error');
            return $global->redirect(sprintf(
                '/admin/system/schema/%s/search',
                $request->getStage('schema')
            ));
        }

        //pass the item to the template
        $data['item'] = $response->getResults();

        //add suggestion value for each relation
        foreach ($data['schema']['relations'] as $name => $relation) {
            if ($relation['many'] > 1) {
                continue;
            }

            $suggestion = '_' . $relation['primary2'];

            $suggestionData = $data['item'];
            if ($relation['many'] == 0) {
                if (!isset($data['item'][$relation['name']])) {
                    continue;
                }

                $suggestionData = $data['item'][$relation['name']];

                if (!$suggestionData) {
                    continue;
                }
            }

            try {
                $data['item'][$suggestion] = Schema::i($relation['name'])
                    ->getSuggestionFormat($suggestionData);
            } catch (Exception $e) {
            }
        }
    }

    //add CSRF
    $this->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //if there are file fields
    if (!empty($data['schema']['files'])) {
        //add CDN
        $config = $global->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);
    }

    //if this is a relational process
    if ($request->hasStage('relation')) {
        //also pass the relation to the form
        $data['relation'] = $request->getStage('relation');
    }

    //determine valid relations
    $data['valid_relations'] = [];
    $this->trigger('system-schema-search', $request, $response);
    foreach ($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
    }

    //if we only want the data
    if ($request->getStage('render') === 'false') {
        return $response->setJson($data);
    }

    //----------------------------//
    // 2. Render Template
    //set the class name
    $class = 'page-admin-system-model-create page-admin';

    //set the action
    $data['action'] = 'create';

    //determine the title
    $data['title'] = $global->translate(
        'Create %s',
        $data['schema']['singular']
    );

    $template = __DIR__ . '/template';
    if (is_dir($response->getPage('template_root'))) {
        $template = $response->getPage('template_root');
    }

    $partials = __DIR__ . '/template';
    if (is_dir($response->getPage('partials_root'))) {
        $partials = $response->getPage('partials_root');
    }

    //render the body
    $body = $this
        ->package('cradlephp/cradle-system')
        ->template(
            'form',
            $data,
            [
                'form_fieldset'
            ],
            $template,
            $partials
        );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //render page
    $this->trigger('admin-render-page', $request, $response);
});

/**
 * Render the System Model Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/model/:schema/update/:id', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $global = $this->package('global');
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //pass the item with only the post data
    $data = ['item' => $request->getPost()];

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //get the original table row
    $this->trigger('system-model-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //redirect
        $redirect = sprintf(
            '/admin/system/model/%s/search',
            $request->getStage('schema')
        );

        //this is for flexibility
        if ($request->hasStage('redirect_uri')) {
            $redirect = $request->getStage('redirect_uri');
        }

        //add a flash
        $global->flash($response->getMessage(), 'error');
        return $global->redirect($redirect);
    }

    $data['detail'] = $response->getResults();

    //if no item
    if (empty($data['item'])) {
        //pass the item to the template
        $data['item'] = $data['detail'];

        //add suggestion value for each relation
        foreach ($data['schema']['relations'] as $name => $relation) {
            if ($relation['many'] > 1) {
                continue;
            }

            $suggestion = '_' . $relation['primary2'];

            $suggestionData = $data['item'];
            if ($relation['many'] == 0) {
                if (!isset($data['item'][$relation['name']])) {
                    continue;
                }

                $suggestionData = $data['item'][$relation['name']];

                if (!$suggestionData) {
                    continue;
                }
            }

            try {
                $data['item'][$suggestion] = Schema::i($relation['name'])
                    ->getSuggestionFormat($suggestionData);
            } catch (Exception $e) {
            }
        }
    }

    //if we only want the raw data
    if ($request->getStage('render') === 'false') {
        return;
    }

    //determine the suggestion
    $data['detail']['suggestion'] = $schema->getSuggestionFormat($data['item']);

    //add CSRF
    $this->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //if there are file fields
    if (!empty($data['schema']['files'])) {
        //add CDN
        $config = $global->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);
    }

    //determine valid relations
    $data['valid_relations'] = [];
    $this->trigger('system-schema-search', $request, $response);
    foreach ($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
    }

    $data['redirect'] = urlencode($request->getServer('REQUEST_URI'));

    //----------------------------//
    // 2. Render Template
    //set the class name
    $class = 'page-admin-system-model-update page-admin';

    //set the action
    $data['action'] = 'update';

    //determine the title
    $data['title'] = $global->translate(
        'Updating %s',
        $data['schema']['singular']
    );

    $template = __DIR__ . '/template';
    if (is_dir($response->getPage('template_root'))) {
        $template = $response->getPage('template_root');
    }

    $partials = __DIR__ . '/template';
    if (is_dir($response->getPage('partials_root'))) {
        $partials = $response->getPage('partials_root');
    }

    //render the body
    $body = $this
        ->package('cradlephp/cradle-system')
        ->template(
            'form',
            $data,
            [
                'form_fieldset'
            ],
            $template,
            $partials
        );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //render page
    $this->trigger('admin-render-page', $request, $response);
});

/**
 * Render the System Model Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/model/:schema/detail/:id', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $global = $this->package('global');
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //pass the item with only the post data
    $data = [];

    //also pass the schema to the template
    $data['schema'] = $schema->getAll();

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //get the original table row
    $this->trigger('system-model-detail', $request, $response);

    //if we only want the raw data
    if ($request->getStage('render') === 'false') {
        return;
    }

    //can we view ?
    if ($response->isError()) {
        //redirect
        $redirect = sprintf(
            '/admin/system/model/%s/search',
            $request->getStage('schema')
        );

        //this is for flexibility
        if ($request->hasStage('redirect_uri')) {
            $redirect = $request->getStage('redirect_uri');
        }

        //add a flash
        $global->flash($response->getMessage(), 'error');
        return $global->redirect($redirect);
    }

    $data['detail'] = $response->getResults();

    //add suggestion value for each relation
    foreach ($data['schema']['relations'] as $name => $relation) {
        if ($relation['many'] > 1) {
            continue;
        }

        $suggestion = '_' . $relation['primary2'];

        $suggestionData = $data['detail'];
        if ($relation['many'] == 0) {
            if (!isset($data['detail'][$relation['name']])) {
                continue;
            }

            $suggestionData = $data['detail'][$relation['name']];

            if (!$suggestionData) {
                continue;
            }
        }

        try {
            $data['detail'][$suggestion] = Schema::i($relation['name'])
                ->getSuggestionFormat($suggestionData);
        } catch (Exception $e) {
        }
    }

    //determine the suggestion
    $data['detail']['suggestion'] = $schema->getSuggestionFormat($data['detail']);

    //determine valid relations
    $data['valid_relations'] = [];
    $this->trigger('system-schema-search', $request, $response);
    foreach ($response->getResults('rows') as $relation) {
        $data['valid_relations'][] = $relation['name'];
    }

    $data['redirect'] = urlencode($request->getServer('REQUEST_URI'));

    //----------------------------//
    // 2. Render Template
    //set the class name
    $class = 'page-admin-system-model-update page-admin';

    //set the action
    $data['action'] = 'detail';

    // get the suggestion title
    $suggestion = $data['schema']['suggestion'];
    $handlebars = cradle('global')->handlebars();
    $compiled = $handlebars->compile($data['schema']['suggestion'])($data['detail']);

    //determine the title
    $data['title'] = $global->translate($compiled);

    $template = __DIR__ . '/template';
    if (is_dir($response->getPage('template_root'))) {
        $template = $response->getPage('template_root');
    }

    $partials = __DIR__ . '/template';
    if (is_dir($response->getPage('partials_root'))) {
        $partials = $response->getPage('partials_root');
    }

    //render the body
    $body = $this
        ->package('cradlephp/cradle-system')
        ->template(
            'detail',
            $data,
            [],
            $template,
            $partials
        );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //render page
    $this->trigger('admin-render-page', $request, $response);
});

/**
 * Process the System Model Search Actions
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/model/:schema/search', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //determine route
    $route = sprintf(
        '/admin/system/model/%s/search',
        $request->getStage('schema')
    );

    //this is for flexibility
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    $action = $request->getStage('bulk-action');
    $ids = $request->getStage($schema->getPrimaryFieldName());

    if (empty($ids)) {
        $response->setError(true, 'No IDs chosen');
        //let the form route handle the rest
        return $this->routeTo('get', $route, $request, $response);
    }

    //----------------------------//
    // 2. Process Request
    $errors = [];
    foreach ($ids as $id) {
        //table_id, 1 for example
        $request->setStage($schema->getPrimaryFieldName(), $id);

        //case for actions
        switch ($action) {
            case 'remove':
                $this->trigger('system-model-remove', $request, $response);
                break;
            case 'restore':
                $this->trigger('system-model-restore', $request, $response);
                break;
            default:
                //set an error
                $response->setError(true, 'No valid action chosen');
                //let the search route handle the rest
                return $this->routeTo('get', $route, $request, $response);
        }

        if ($response->isError()) {
            $errors[] = $response->getMessage();
        } else {
            $this->log(
                sprintf(
                    '%s #%s %s',
                    $schema->getSingular(),
                    $id,
                    $action
                ),
                $request,
                $response
            );
        }
    }

    //----------------------------//
    // 3. Interpret Results
    //redirect
    $redirect = sprintf(
        '/admin/system/model/%s/search',
        $schema->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $global = $this->package('global');
    if (!empty($errors)) {
        $global->flash(
            'Some items could not be processed',
            'error',
            $errors
        );
    } else {
        $global->flash(
            sprintf(
                'Bulk action %s successful',
                $action
            ),
            'success'
        );
    }

    $global->redirect($redirect);
});

/**
 * Process the System Model Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/model/:schema/create', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //get all the schema field data
    $fields = $schema->getFields();

    //these are invalid types to set
    $invalidTypes = ['none', 'active', 'created', 'updated'];

    //for each field
    foreach ($fields as $name => $field) {
        //if the field is invalid
        if (in_array($field['field']['type'], $invalidTypes)) {
            $request->removeStage($name);
            continue;
        }

        //if no value
        if ($request->hasStage($name) && !$request->getStage($name)) {
            //make it null
            $request->setStage($name, null);
            continue;
        }

        if (//if there is a default
            isset($field['default'])
            && trim($field['default'])
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            if (strtoupper($field['default']) === 'NOW()') {
                $field['default'] = date('Y-m-d H:i:s');
            }

            //set the default
            $request->setStage($name, $field['default']);
            continue;
        }
    }

    //----------------------------//
    // 2. Process Request
    $this->trigger('system-model-create', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/model/%s/create',
            $request->getStage('schema')
        );

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return $this->routeTo('get', $route, $request, $response);
    }

    //it was good

    //record logs
    $this->log(
        sprintf(
            'created new %s',
            $schema->getSingular()
        ),
        $request,
        $response,
        'create',
        $request->getStage('schema'),
        $response->getResults($schema->getPrimaryFieldName())
    );

    //redirect
    $redirect = sprintf(
        '/admin/system/model/%s/search',
        $schema->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $global = $this->package('global');
    $global->flash(sprintf(
        '%s was Created',
        $schema->getSingular()
    ), 'success');

    $global->redirect($redirect);
});

/**
 * Process the System Model Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/model/:schema/update/:id', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //get all the schema field data
    $fields = $schema->getFields();

    //these are invalid types to set
    $invalidTypes = ['none', 'active', 'created', 'updated'];

    //for each field
    foreach ($fields as $name => $field) {
        //if the field is invalid
        if (in_array($field['field']['type'], $invalidTypes)) {
            $request->removeStage($name);
            continue;
        }

        //if password has no value
        if ($request->hasStage($name) && !$request->getStage($name)
            && $field['field']['type'] === 'password'
        ) {
            //make it null
            $request->removeStage($name);
            continue;
        }

        //if no value
        if ($request->hasStage($name) && !$request->getStage($name)) {
            //make it null
            $request->setStage($name, null);
            continue;
        }

        if (//if there is a default
            isset($field['default'])
            && trim($field['default'])
            // and there's no stage
            && $request->hasStage($name)
            && !$request->getStage($name)
        ) {
            if (strtoupper($field['default']) === 'NOW()') {
                $field['default'] = date('Y-m-d H:i:s');
            }

            //set the default
            $request->setStage($name, $field['default']);
            continue;
        }
    }

    //----------------------------//
    // 2. Process Request
    $this->trigger('system-model-update', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/model/%s/update/%s',
            $request->getStage('schema'),
            $request->getStage('id')
        );

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return $this->routeTo('get', $route, $request, $response);
    }

    //it was good

    //record logs
    $this->log(
        sprintf(
            'updated %s #%s',
            $schema->getSingular(),
            $request->getStage('id')
        ),
        $request,
        $response,
        'update',
        $request->getStage('schema'),
        $request->getStage('id')
    );

    //redirect
    $redirect = sprintf(
        '/admin/system/model/%s/search',
        $schema->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $global = $this->package('global');
    $global->flash(sprintf(
        '%s was Updated',
        $schema->getSingular()
    ), 'success');

    $global->redirect($redirect);
});

/**
 * Process the System Model Remove
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/model/:schema/remove/:id', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //----------------------------//
    // 2. Process Request
    $this->trigger('system-model-remove', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    if (!$response->isError()) {
        //record logs
        $this->log(
            sprintf(
                'removed %s #%s',
                $schema->getSingular(),
                $request->getStage('id')
            ),
            $request,
            $response,
            'remove',
            $request->getStage('schema'),
            $request->getStage('id')
        );
    }

    //redirect
    $redirect = sprintf(
        '/admin/system/model/%s/search',
        $schema->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    $global = $this->package('global');
    if ($response->isError()) {
        //add a flash
        $global->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = sprintf('%s was Removed', $schema->getSingular());
        $global->flash($message, 'success');
    }

    $global->redirect($redirect);
});

/**
 * Process the System Model Restore
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/model/:schema/restore/:id', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //table_id, 1 for example
    $request->setStage(
        $schema->getPrimaryFieldName(),
        $request->getStage('id')
    );

    //----------------------------//
    // 2. Process Request
    $this->trigger('system-model-restore', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    if (!$response->isError()) {
        //record logs
        $this->log(
            sprintf(
                'restored %s #%s',
                $schema->getSingular(),
                $request->getStage('id')
            ),
            $request,
            $response,
            'restore',
            $request->getStage('schema'),
            $request->getStage('id')
        );
    }

    //redirect
    $redirect = sprintf(
        '/admin/system/model/%s/search',
        $schema->getName()
    );

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    $global = $this->package('global');
    if ($response->isError()) {
        //add a flash
        $global->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = sprintf('%s was Restored', $schema->getSingular());
        $global->flash($message, 'success');
    }

    $global->redirect($redirect);
});

/**
 * Process Object Import
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/model/:schema/import', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $schema = Schema::i($request->getStage('schema'));

    // data
    $data = [];

    // try to parse the data
    try {
        // decode the data
        $data = @json_decode($request->getBody(), true);
    } catch (\Exception $e) {
        return $response
            ->setContent(json_encode([
                'error' => true,
                'message' => 'Unable to parse data',
                'errors' => [
                    'Unable to parse data',
                    $e->getMessage()
                ]
            ]));
    }

    // set data
    $request->setStage('rows', $data);

    //----------------------------//
    // 2. Process Request
    // catch errors for better debugging
    try {
        $this->trigger('system-model-import', $request, $response);
    } catch (\Exception $e) {
        return $response
            ->setContent(json_encode([
                'error' => true,
                'message' => $e->getMessage(),
                'errors' => [
                    $e->getMessage()
                ]
            ]));
    }

    //----------------------------//
    // 3. Interpret Results
    //if the import event returned errors
    if ($response->isError()) {
        $errors = [];
        //loop through each row
        foreach ($response->getValidation() as $i => $validation) {
            //and loop through each error
            foreach ($validation as $key => $error) {
                //add the error
                $errors[] = sprintf('ROW %s - %s: %s', $i, $key, $error);
            }
        }

        //Set JSON Content
        return $response->setContent(json_encode([
            'error' => true,
            'message' => $response->getMessage(),
            'errors' => $errors
        ]));
    }

    //record logs
    $this->log(
        sprintf(
            'imported %s',
            $schema->getPlural()
        ),
        $request,
        $response,
        'import'
    );

    //add a flash
    $message = $this->package('global')->translate(
        '%s was Imported',
        $schema->getPlural()
    );

    //Set JSON Content
    return $response->setContent(json_encode([
        'error' => false,
        'message' => $message
    ]));
});

/**
 * Process Object Export
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/model/:schema/export/:type', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //get schema data
    $schema = Schema::i($request->getStage('schema'));

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        foreach ($request->getStage('filter') as $key => $value) {
            //if invalid key format or there is no value
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $key) || !strlen($value)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sort options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        foreach ($request->getStage('order') as $key => $value) {
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $key)) {
                $request->removeStage('order', $key);
            }
        }
    }

    $request->setStage('range', 0);

    //----------------------------//
    // 2. Process Request
    $this->trigger('system-model-search', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //get the output type
    $type = $request->getStage('type');
    //get the rows
    $rows = $response->getResults('rows');
    //determine the filename
    $filename = $schema->getPlural() . '-' . date('Y-m-d');

    //flatten all json columns
    foreach ($rows as $i => $row) {
        foreach ($row as $key => $value) {
            //transform oobject to array
            if (is_object($value)) {
                $value = (array) $value;
            }

            //if array, let's flatten
            if (is_array($value)) {
                //if no count
                if (!count($value)) {
                    $rows[$i][$key] = '';
                    continue;
                }

                //if regular array
                if (isset($value[0])) {
                    $rows[$i][$key] = implode(',', $value);
                    continue;
                }

                $rows[$i][$key] = json_encode($value);
                continue;
            }

            //provision for any other conversions needed
        }
    }

    //if the output type is csv
    if ($type === 'csv') {
        //if there are no rows
        if (empty($rows)) {
            //at least give the headers
            $rows = [array_keys($schema->getFields())];
        } else {
            //add the headers
            array_unshift($rows, array_keys($rows[0]));
        }

        //set the output headers
        $response
            ->addHeader('Content-Encoding', 'UTF-8')
            ->addHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.csv');

        //open a tmp file
        $file = tmpfile();
        //for each row
        foreach ($rows as $row) {
            //add it to the tmp file as a csv
            fputcsv($file, array_values($row));
        }

        //this is the final output
        $contents = '';

        //rewind the file pointer
        rewind($file);
        //and set all the contents
        while (!feof($file)) {
            $contents .= fread($file, 8192);
        }

        //close the tmp file
        fclose($file);

        //set contents
        return $response->setContent($contents);
    }

    //if the output type is xml
    if ($type === 'xml') {
        //recursive xml parser
        $toXml = function ($array, $xml) use (&$toXml) {
            //for each array
            foreach ($array as $key => $value) {
                //if the value is an array
                if (is_array($value)) {
                    //if the key is not a number
                    if (!is_numeric($key)) {
                        //send it out for further processing (recursive)
                        $toXml($value, $xml->addChild($key));
                        continue;
                    }

                    //send it out for further processing (recursive)
                    $toXml($value, $xml->addChild('item'));
                    continue;
                }

                //add the value
                $xml->addChild($key, htmlspecialchars($value));
            }

            return $xml;
        };

        //set up the xml template
        $root = sprintf(
            "<?xml version=\"1.0\"?>\n<%s></%s>",
            $schema->getName(),
            $schema->getName()
        );

        //set the output headers
        $response
            ->addHeader('Content-Encoding', 'UTF-8')
            ->addHeader('Content-Type', 'text/xml; charset=UTF-8')
            ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.xml');

        //get the contents
        $contents = $toXml($rows, new SimpleXMLElement($root))->asXML();

        //set the contents
        return $response->setContent($contents);
    }

    //json maybe?

    //set the output headers
    $response
        ->addHeader('Content-Encoding', 'UTF-8')
        ->addHeader('Content-Type', 'text/json; charset=UTF-8')
        ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.json');

    //set content
    $response->set('json', $rows);
});
