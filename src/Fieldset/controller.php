<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Fieldset Search Page
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
 * Render the Fieldset Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/fieldset/create', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $global = $this->package('global');
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
            $global->flash($response->getMessage(), 'error');
            return $global->redirect('/admin/system/fieldset/search');
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
    $data['title'] = $global->translate('Create System Fieldset');

    //add custom page helpers
    $global
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
 * Render the Fieldset Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/fieldset/update/:name', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $global = $this->package('global');
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
            $global->flash($response->getMessage(), 'error');
            return $global->redirect($redirect);
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
    $data['title'] = $global->translate('Updating System Fieldset');

    //add custom page helpers
    $global
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
 * Process the Fieldset Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/fieldset/create', function ($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $global = $this->package('global');
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
    $global->flash('System Fieldset was Created', 'success');

    //redirect
    $global->redirect($redirect);
});

/**
 * Process the Fieldset Update Page
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
    $global = $this->package('global');
    //add a flash
    $global->flash('System Fieldset was Updated', 'success');

    //redirect
    $global->redirect($redirect);
});

/**
 * Process the Fieldset Remove
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

    $global = $this->package('global');
    if ($response->isError()) {
        //add a flash
        $global->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = $global->translate('System Fieldset was Removed');
        $global->flash($message, 'success');

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

    $global->redirect($redirect);
});

/**
 * Process the Fieldset Restore
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

    $global = $this->package('global');
    if ($response->isError()) {
        //add a flash
        $global->flash($response->getMessage(), 'error');
    } else {
        //add a flash
        $message = $global->translate('System Fieldset was Restored');
        $global->flash($message, 'success');

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

    $global->redirect($redirect);
});

/**
 * Process the Fieldset Export
 *
 * @param Request $request
 * @param Response $response`
 */
$this->get('/admin/system/fieldset/export', function($request, $response) {
    $global = $this->package('global');
    //get the name
    $name = $request->getStage('name');
    //get the config path
    $path = $global->path('config') . '/fieldset/';
    //default redirect
    $redirect = '/admin/system/fieldset/search';

    //if there is a specified redirect_uri
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    //specific fieldset?
    if (!is_null($name)) {
        //determine the file
        $file = $path . $name . '.php';

        //file does not exists?
        if (!file_exists($file)) {
            //add a flash
            $global->flash('Not Found', 'error');
            return $global->redirect($redirect);
        }

        //get the filename
        $filename = str_replace('.php', '.json', basename($file));

        //prepare response
        $response
            ->addHeader('Content-Encoding', 'UTF-8')
            ->addHeader('Content-Type', 'text/html; charset=UTF-8')
            ->addHeader('Content-Disposition', 'attachment; filename=' . $filename);

        //include the php file
        $content = json_encode(include($file), JSON_PRETTY_PRINT);

        //return content
        return $response->setContent($content);
    }

    //check if ZipArchive is installed
    if (!class_exists('ZipArchive')) {
        //add a flash
        $global->flash('ZipArchive module not found', 'error');
        return $global->redirect($redirect);
    }

    //create zip archive
    $zip = new ZipArchive();
    //create temporary file
    $tmp = sys_get_temp_dir() . '/fieldset.zip';

    //try to open
    if (!$zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        //add a flash
        $global->flash('Failed to create archive', 'error');
        return $global->redirect($redirect);
    }

    //create an empty directory
    $zip->addEmptyDir('fieldset');

    //collect all .php files and add it
    foreach(glob($path . '*.php') as $file) {
        //determin json filename
        $name = str_replace('.php', '.json', basename($file));
        //read the content
        $content = json_encode(include($file), JSON_PRETTY_PRINT);

        //add the content to zip
        $zip->addFromString('fieldset/' . $name, $content);
    }

    //close
    $zip->close();

    //check if file exists
    if (!file_exists($tmp)) {
        //add a flash
        $global->flash('Failed to create archive', 'error');
        return $global->redirect($redirect);
    }

    //prepare response
    $response
        ->addHeader('Content-Type', 'application/zip')
        ->addHeader('Content-Transfer-Encoding', 'Binary')
        ->addHeader('Content-Disposition', 'attachment; filename=' . basename($tmp))
        ->addHeader('Content-Length', filesize($tmp));

    return $response->setContent(file_get_contents($tmp));
});

/**
 * Render the Fieldset Import
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/fieldset/import', function($request, $response) {
    //----------------------------//
    // 1. Prepare Data
    $data = ['item' => $request->getPost()];

    //if this is a return back from processing
    //this form and it's because of an error
    if ($response->isError()) {
        //pass the error messages to the template
        $response->setFlash(json_encode($response->getValidation()), 'error');
        $data['errors'] = $response->getValidation();
    }

    //add CSRF
    $this->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 2. Render Template
    //set the class name
    $class = 'page-admin-system-fieldset-import page-admin';

    //determine the action
    $data['action'] = 'import';

    //determine the title
    $data['title'] = $this->package('global')->translate('Import System Fieldset');

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
            'import',
            $data,
            [],
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
 * Process the Fieldset Import
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/fieldset/import', function($request, $response) {
    $global = $this->package('global');
    //get the content
    $fieldset = $request->getStage('fieldset');
    //get the config path
    $config = $global->path('config') . '/fieldset/';
    //get the type
    $type = substr($fieldset, 5, strpos($fieldset, ';base64') - 5);
    //get the route
    $route = '/admin/system/fieldset/import';
    //get the redirect
    $redirect = '/admin/system/fieldset/search';

    //this is for flexibility
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //invalid file?
    if ($type !== 'application/json' && $type !== 'application/zip') {
        $response->setError(true, 'Invalid File');
        return $this->routeTo('get', $route, $request, $response);
    }

    //decode the content
    $content = base64_decode(
        substr($fieldset, strpos($fieldset, ';base64,') + 8)
    );

    //json file?
    if ($type === 'application/json') {
        //parse the content
        $content = json_decode($content, true);

        //if not name or is not an array
        if (!is_array($content) || !isset($content['name'])) {
            $response->setError(true, 'Invalid Fieldset');
            return $this->routeTo('get', $route, $request, $response);
        }

        //create payload
        $payload = $this->makePayload();

        //set fieldset to stage
        $payload['request']->setStage($content);
        //cleanup
        $payload['request']->removeStage('fieldset');

        //trigger update
        if (file_exists(sprintf('%s%s.%s', $config, $content['name'], 'php'))) {
            $this->trigger('system-fieldset-update', $payload['request'], $payload['response']);

        //trigger create
        } else {
            $this->trigger('system-fieldset-create', $payload['request'], $payload['response']);
        }

        //error?
        if ($payload['response']->isError()) {
            $response
                ->set('json', 'validation', [
                    $content['name'] => [
                        'message' => $payload['response']->getMessage(),
                        'validation' => $payload['response']->getValidation()
                    ]
                ])
                ->setError(true, $payload['response']->getMessage());

            return $this->routeTo('get', $route, $request, $response);
        }

        //record logs
        $this->log(
            sprintf(
                'imported fieldset: %s',
                $content['name']
            ),
            $request,
            $response,
            'import',
            'fieldset',
            $content['name']
        );

        //it was good
        //add a flash
        $global->flash('System Fieldset was Imported', 'success');
        //redirect
        return $global->redirect($redirect);
    }

    //get temporary folder
    $tmp = sys_get_temp_dir();
    //create temporary zip
    $file  = sprintf('%s/%s.zip', $tmp, uniqid());

    //create temporary zip file
    file_put_contents($file, $content);

    //check if ZipArchive is installed
    if (!class_exists('ZipArchive')) {
        $response->setError(true, 'ZipArchive module not found');
        return $this->routeTo('get', $route, $request, $response);
    }

    //open zip archive
    $zip = new ZipArchive();

    //try to open
    if (!$zip->open($file)) {
        $response->setError(true, 'Failed to parse archive');
        return $this->routeTo('get', $route, $request, $response);
    }

    //errors
    $errors = [];

    //loop through files
    for($i = 0; $i < $zip->numFiles; $i++){
        //get the filename
        $filename = $zip->getNameIndex($i);

        //root or not under fieldset?
        if ($filename === 'fieldset/'
        || strpos($filename , 'fieldset/') === false) {
            continue;
        }

        //parse the content of each filename
        $content = json_decode($zip->getFromName($filename), true);
        //create payload
        $payload = $this->makePayload();

        //skip if fieldset doesn't have name or is not an array
        if (!isset($content['name']) || !is_array($content)) {
            continue;
        }

        //set the content
        $payload['request']->setStage($content);
        //cleanup
        $payload['request']->removeStage('fieldset');

        //trigger update
        if (file_exists(sprintf('%s%s.%s', $config, $content['name'], 'php'))) {
            $this->trigger('system-fieldset-update', $payload['request'], $payload['response']);

        //trigger create
        } else {
            $this->trigger('system-fieldset-create', $payload['request'], $payload['response']);
        }

        //error?
        if ($payload['response']->isError()) {
            //set the message and validation
            $errors[$content['name']] = array(
                'message' => $payload['response']->getMessage(),
                'validation' => $payload['response']->getValidation()
            );

            continue;
        }

        //record logs
        $this->log(
            sprintf(
                'imported fieldset: %s',
                $content['name']
            ),
            $request,
            $response,
            'import',
            'fieldset',
            $content['name']
        );
    }

    //errors?
    if (!empty($errors)) {
        $response
            ->set('json', 'validation', $errors)
            ->setError(true, 'Invalid Parameters');

        return $this->routeTo('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    $global->flash('System Fieldset was Imported', 'success');
    //redirect
    return $global->redirect($redirect);
});
