<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Model Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/search', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    if (!$request->hasStage()) {
        $request->setStage('filter', 'active', 1);
    }

    //trigger job
    $this->trigger('system-schema-search', $request, $response);

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

    //----------------------------//
    // 2. Render Template
    $class = 'page-admin-system-schema-search page-admin';
    $data['title'] = $this->package('global')->translate('System Schemas');

    $body = $this
        ->package('cradlephp/cradle-system')
        ->template('schema', 'search', $data);

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
 * Render the Model Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/create', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $data = ['item' => $request->getPost()];

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //for ?copy=1 functionality
    if (empty($data['item']) && $request->hasStage('copy')) {
        $request->setStage('schema', $request->getStage('copy'));
        $this->trigger('system-schema-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            $this->package('global')->flash($response->getMessage(), 'error');
            return $this->package('global')->redirect('/admin/system/schema/search');
        }

        $data['item'] = $response->getResults();
    }

    //add CSRF
    $this->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 2. Render Template
    //set the class name
    $class = 'page-admin-system-schema-create page-admin';

    //determine the action
    $data['action'] = 'create';

    //determine the title
    $data['title'] = $this->package('global')->translate('Create System Schema');

    //add custom page helpers
    $this->package('global')
        ->handlebars()
        ->registerHelper('is_array', function ($value, $option) {
            if (is_array($value)) {
                return $option['fn']();
            }

            return $option['inverse']();
        });

    //render the body
    $body = $this
        ->package('cradlephp/cradle-system')
        ->template('schema', 'form', $data, [
            'styles',
            'templates',
            'scripts',
            'row',
            'types',
            'lists',
            'details',
            'validation',
            'update',
            'options_type',
            'options_format',
            'options_validation',
            'options_icon'
        ]);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'admin-render-page');

/**
 * Render the Model Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/update/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //pass the item with only the post data
    $data = ['item' => $request->getPost()];

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //if no item
    if (empty($data['item'])) {
        //get the original schema row
        $this->trigger('system-schema-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //redirect
            $redirect = '/admin/system/schema/search';

            //this is for flexibility
            if ($request->hasStage('redirect_uri')) {
                $redirect = $request->getStage('redirect_uri');
            }

            //add a flash
            $this->package('global')->flash($response->getMessage(), 'error');
            return $this->package('global')->redirect($redirect);
        }

        $data['item'] = $response->getResults();
    }

    //if we only want the raw data
    if ($request->getStage('render') === 'false') {
        return;
    }

    //add CSRF
    $this->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 2. Render Template
    //set the class name
    $class = 'page-admin-system-schema-update page-admin';

    //determine the action
    $data['action'] = 'update';

    //determine the title
    $data['title'] = $this->package('global')->translate('Updating System Schema');

    //add custom page helpers
    $this->package('global')
        ->handlebars()
        ->registerHelper('is_array', function ($value, $option) {
            if (is_array($value)) {
                return $option['fn']();
            }

            return $option['inverse']();
        });

    //render the body
    $body = $this
        ->package('cradlephp/cradle-system')
        ->template('schema', 'form', $data, [
            'styles',
            'templates',
            'scripts',
            'row',
            'types',
            'lists',
            'details',
            'validation',
            'update',
            'options_type',
            'options_format',
            'options_validation',
            'options_icon'
        ]);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
    $this->trigger('admin-render-page', $request, $response);
});

/**
 * Process the Model Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/schema/create', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    //if detail has no value make it null
    if ($request->hasStage('detail') && !$request->getStage('detail')) {
        $request->setStage('detail', null);
    }

    //if fields has no value make it an array
    if ($request->hasStage('fields') && !$request->getStage('fields')) {
        $request->setStage('fields', []);
    }

    //if validation has no value make it an array
    if ($request->hasStage('validation') && !$request->getStage('validation')) {
        $request->setStage('validation', []);
    }

    //----------------------------//
    // 2. Process Request
    $this->trigger('system-schema-create', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = '/admin/system/schema/create';

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        return $this->routeTo('get', $route, $request, $response);
    }

    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //record logs
    $this->log(
        sprintf(
            '%s schema created',
            ucfirst($request->getStage('name'))
        ),
        $request,
        $response
    );

    //it was good
    //add a flash
    $this->package('global')->flash('System Schema was Created', 'success');

    //redirect
    $this->package('global')->redirect($redirect);
});

/**
 * Process the Model Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/schema/update/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data

    //if detail has no value make it null
    if ($request->hasStage('detail') && !$request->getStage('detail')) {
        $request->setStage('detail', null);
    }

    //if fields has no value make it an array
    if ($request->hasStage('fields') && !$request->getStage('fields')) {
        $request->setStage('fields', []);
    }

    //if validation has no value make it an array
    foreach ($request->getStage('fields') as $i => $field) {
        if ($request->hasStage('fields', $i, 'validation')
            && !$request->getStage('fields', $i, 'validation')
        ) {
            $request->setStage('fields', $i, 'validation', []);
        }
    }

    //if relations has no value make it an array
    if ($request->hasStage('relations') && !$request->getStage('relations')) {
        $request->setStage('relations', []);
    }

    //----------------------------//
    // 2. Process Request
    $this->trigger('system-schema-update', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/schema/update/%s',
            $request->getStage('name')
        );

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        //let the form route handle the rest
        return $this->routeTo('get', $route, $request, $response);
    }

    //record logs
    $this->log(
        sprintf(
            '%s schema updated',
            ucfirst($request->getStage('name'))
        ),
        $request,
        $response
    );

    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //it was good
    //add a flash
    $this->package('global')->flash('System Schema was Updated', 'success');

    //redirect
    $this->package('global')->redirect($redirect);
});

/**
 * Process the Model Remove
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/remove/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    $this->trigger('system-schema-remove', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    if ($response->isError()) {
        //add a flash
        $this->package('global')->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = $this->package('global')->translate('System Schema was Removed');
        $this->package('global')->flash($message, 'success');

        //record logs
        $this->log(
            sprintf(
                '%s schema removed',
                ucfirst($request->getStage('name'))
            ),
            $request,
            $response
        );
    }

    $this->package('global')->redirect($redirect);
});

/**
 * Process the Model Restore
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/restore/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    $this->trigger('system-schema-restore', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    if ($response->isError()) {
        //add a flash
        $this->package('global')->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = $this->package('global')->translate('System Schema was Restored');
        $this->package('global')->flash($message, 'success');

        //record logs
        $this->log(
            sprintf(
                '%s schema restored',
                ucfirst($request->getStage('name'))
            ),
            $request,
            $response
        );
    }

    $this->package('global')->redirect($redirect);
});

/**
 * Render the Model Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/elastic/search', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    $this->trigger('system-schema-search-elastic', $request, $response);

    //if we only want the raw data
    if($request->getStage('render') === 'false') {
        return;
    }

    $data = $response->getResults();

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-system-schema-search page-admin';
    $data['title'] = cradle('global')->translate('System Elastic Schema');
    
    //render the body
    $body = $this
        ->package('cradlephp/cradle-system')
        ->template('schema', 'elastic/search', $data, [
            'styles',
            'templates',
            'scripts',
            'row',
            'types',
            'lists',
            'details',
            'validation',
            'update',
            'options_type',
            'options_format',
            'options_validation',
            'options_icon'
        ]);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if($request->getStage('render') === 'body') {
        return;
    }
    
    //render page
    $this->trigger('admin-render-page', $request, $response);
});

/**
 * Create elastic schema
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/elastic/create/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    //----------------------------//
    // trigger create elastic schema event
    $this->trigger('system-schema-create-elastic', $request, $response);

    $nextUrl = '/admin/system/schema/elastic/search';
    // check if there are errors
    if ($response->isError()) {
        $this->package('global')->flash($response->getMessage(), 'error');
        $this->package('global')->redirect($nextUrl);
    }

    // process is successfull
    $this->package('global')
        ->flash(sprintf('Elastic schema for %s generated successfully.',
            $request->getStage('name')), 'success');
    
    $this->package('global')->redirect($nextUrl);
});

/**
 * Create elastic schema
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/elastic/map/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    //----------------------------//
    // redirect url
    $nextUrl = '/admin/system/schema/elastic/search';
    // trigger map elastic schema event
    $this->trigger('system-schema-map-elastic', $request, $response);
    // intercept errors
    if ($response->isError()) {
        $this->package('global')->flash($response->getMessage(), 'error');
        $this->package('global')->redirect($nextUrl);
    }

    $this->package('global')
        ->flash(sprintf('%s mapped successfully', ucwords ($request->getStage('name'))), 'success');
    
    $this->package('global')->redirect($nextUrl);
});

/**
 * Populate elastic schema
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/elastic/populate/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    //----------------------------//
    // trigger elastic populate
    $nextUrl = '/admin/system/schema/elastic/search';
    $this->trigger('system-schema-populate-elastic', $request, $response);
    // intercept error
    if ($response->isError()) {
        $this->package('global')->flash($response->getMessage(), 'error');
        $this->package('global')->redirect($nextUrl);
    }

    $this->package('global')
        ->flash(sprintf('Successully populated %s', $request->getStage('name')), 'success');
    
    $this->package('global')->redirect($nextUrl);
});

/**
 * Flush elastic schema
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/elastic/flush/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    // trigger elastic flush
    $nextUrl = '/admin/system/schema/elastic/search';
    $this->trigger('system-schema-flush-elastic', $request, $response);
    // intercept error
    if ($response->isError()) {
        $this->package('global')->flash($response->getMessage(), 'error');
        $this->package('global')->redirect($nextUrl);
    }
    
    $this->package('global')
        ->flash(sprintf('Successfully flushed %s.', $request->getStage('name')), 'success');
    
    $this->package('global')->redirect($nextUrl);
});


/**
 * Edit elastic schema
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/elastic/edit/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $data = [];
    
    //----------------------------//
    // 2. Process Request
    //----------------------------//
    $this->trigger('system-schema-get-elastic', $request, $response);
    // intercept error
    if ($response->isError()) {
        $this->package('global')->flash($response->getMessage(), 'error');
        $this->package('global')->redirect('/admin/system/schema/elastic/search');
    }

    $data['code'] = $response->getResults();
    // 3. Render Template
    $class = 'page-admin-system-schema-search page-admin';
    $data['title'] = 'Profile Elastic Schema';
    
    //render the body
    $body = $this
        ->package('cradlephp/cradle-system')
        ->template('schema', 'elastic/form', $data, [
            'styles',
            'templates',
            'scripts',
            'row',
            'types',
            'lists',
            'details',
            'validation',
            'update',
            'options_type',
            'options_format',
            'options_validation',
            'options_icon'
        ]);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
    
    //if we only want the body
    if($request->getStage('render') === 'body') {
        return;
    }
    
    //render page
    $this->trigger('admin-render-page', $request, $response);
});


/**
 * Edit elastic schema
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/schema/elastic/edit/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $nextUrl = '/admin/system/schema/elastic/search';
    
    //----------------------------//
    // 2. Process Request
    // trigger update elastic schema
    $this->trigger('system-schema-update-elastic', $request, $response);
    // intercept error
    if ($response->isError()) {
        $this->package('global')->flash($response->getMessage(), 'error');
        $this->package('global')->redirect($nextUrl);
    }

    $this->package('global')
        ->flash(sprintf('Elastic schema %s', $request->getStage('name')), 'success');
    
    $this->package('global')->redirect($nextUrl);
});

