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
$this->get('/admin/system/fieldset/search', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    if (!$request->hasStage()) {
        $request->setStage('filter', 'active', 1);
    }

    //trigger job
    $this->trigger('system-fieldset-search', $request, $response);

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
        $response->getResults() ? $response->getResults() : []
    );

    //----------------------------//
    // 2. Render Template
    $class = 'page-admin-system-fieldset-search page-admin';
    $data['title'] = $this->package('global')->translate('System Fieldsets');

    $template = __DIR__ . '/template';
    if (is_dir($response->getPage('template_root'))) {
        $template = $response->getPage('template_root');
    }

    $partials = __DIR__ . '/template';
    if (is_dir($response->getPage('partials_root'))) {
        $partials = $response->getPage('partials_root');
    }

    $body = $this
        ->package('cradlephp/cradle-system')
        ->template(
            'search',
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
 * Render the Model Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/fieldset/create', function ($request, $response) {
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
        $request->setStage('fieldset', $request->getStage('copy'));
        $this->trigger('system-fieldset-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            $this->package('global')->flash($response->getMessage(), 'error');
            return $this->package('global')->redirect('/admin/system/fieldset/search');
        }

        $data['item'] = $response->getResults();
    }

    //add CSRF
    $this->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 2. Render Template
    //set the class name
    $class = 'page-admin-system-fieldset-create page-admin';

    //determine the action
    $data['action'] = 'create';

    //determine the title
    $data['title'] = $this->package('global')->translate('Create System Fieldset');

    //add custom page helpers
    $this->package('global')
        ->handlebars()
        ->registerHelper('is_array', function ($value, $option) {
            if (is_array($value)) {
                return $option['fn']();
            }

            return $option['inverse']();
        });

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
                'styles',
                'templates',
                'scripts',
                'row',
                'modal',
                'field_type',
                'field_list',
                'field_detail',
                'field_validation',
                'options_type',
                'options_format',
                'options_validation',
                'options_icon'
            ],
            $template,
            $partials
        );

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
$this->get('/admin/system/fieldset/update/:name', function ($request, $response) {
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
        //get the original fieldset row
        $this->trigger('system-fieldset-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //redirect
            $redirect = '/admin/system/fieldset/search';

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
    $class = 'page-admin-system-fieldset-update page-admin';

    //determine the action
    $data['action'] = 'update';

    //determine the title
    $data['title'] = $this->package('global')->translate('Updating System Fieldset');

    //add custom page helpers
    $this->package('global')
        ->handlebars()
        ->registerHelper('is_array', function ($value, $option) {
            if (is_array($value)) {
                return $option['fn']();
            }

            return $option['inverse']();
        });

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
                'styles',
                'templates',
                'scripts',
                'row',
                'modal',
                'field_type',
                'field_list',
                'field_detail',
                'field_validation',
                'options_type',
                'options_format',
                'options_validation',
                'options_icon'
            ],
            $template,
            $partials
        );

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
$this->post('/admin/system/fieldset/create', function ($request, $response) {
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
    $this->trigger('system-fieldset-create', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = '/admin/system/fieldset/create';

        //this is for flexibility
        if ($request->hasStage('route')) {
            $route = $request->getStage('route');
        }

        return $this->routeTo('get', $route, $request, $response);
    }

    //redirect
    $redirect = '/admin/system/fieldset/search';

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
            'created fieldset: %s',
            $request->getStage('singular')
        ),
        $request,
        $response,
        'create',
        'fieldset',
        $request->getStage('name')
    );

    //it was good
    //add a flash
    $this->package('global')->flash('System Fieldset was Created', 'success');

    //redirect
    $this->package('global')->redirect($redirect);
});

/**
 * Process the Model Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/fieldset/update/:name', function ($request, $response) {
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
    $this->trigger('system-fieldset-update', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //if the event returned an error
    if ($response->isError()) {
        //determine route
        $route = sprintf(
            '/admin/system/fieldset/update/%s',
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
            'updated fieldset: %s',
            $request->getStage('singular')
        ),
        $request,
        $response,
        'update',
        'fieldset',
        $request->getStage('name')
    );

    //redirect
    $redirect = '/admin/system/fieldset/search';

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
    $this->package('global')->flash('System Fieldset was Updated', 'success');

    //redirect
    $this->package('global')->redirect($redirect);
});

/**
 * Process the Model Remove
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/fieldset/remove/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    $this->trigger('system-fieldset-remove', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //redirect
    $redirect = '/admin/system/fieldset/search';

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
        $message = $this->package('global')->translate('System Fieldset was Removed');
        $this->package('global')->flash($message, 'success');

        //record logs
        $this->log(
            sprintf(
                'removed fieldset: %s',
                $request->getStage('name')
            ),
            $request,
            $response,
            'remove',
            'fieldset',
            $request->getStage('name')
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
$this->get('/admin/system/fieldset/restore/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    // no data to preapre
    //----------------------------//
    // 2. Process Request
    $this->trigger('system-fieldset-restore', $request, $response);

    //----------------------------//
    // 3. Interpret Results
    //redirect
    $redirect = '/admin/system/fieldset/search';

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
        $message = $this->package('global')->translate('System Fieldset was Restored');
        $this->package('global')->flash($message, 'success');

        //record logs
        $this->log(
            sprintf(
                'restored fieldset: %s',
                $request->getStage('name')
            ),
            $request,
            $response,
            'restore',
            'fieldset',
            $request->getStage('name')
        );
    }

    $this->package('global')->redirect($redirect);
});
