<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Schema Search Page
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

    //organize by groups
    $data['groups'] = [];
    if (isset($data['rows']) && is_array($data['rows'])) {
        foreach ($data['rows'] as $row) {
            $group = 'Custom';
            if (isset($row['group']) && trim($row['group'])) {
                $group = $row['group'];
            }

            $data['groups'][$group][] = $row;
        }
    }

    ksort($data['groups']);

    //----------------------------//
    // 2. Render Template
    $class = 'page-admin-system-schema-search page-admin';
    $data['title'] = $this->package('global')->translate('System Schemas');

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
 * Render the Schema Create Page
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
 * Render the Schema Update Page
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
 * Process the Schema Create Page
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

    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //make sure these are not added
    $request->removeStage('redirect_uri');
    $request->removeStage('csrf');

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

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //record logs
    $this->log(
        sprintf(
            'created schema: %s',
            $request->getStage('singular')
        ),
        $request,
        $response,
        'create',
        'schema',
        $request->getStage('name')
    );

    //it was good
    //add a flash
    $this->package('global')->flash('System Schema was Created', 'success');

    //redirect
    $this->package('global')->redirect($redirect);
});

/**
 * Process the Schema Update Page
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

    //redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect
    if ($request->getStage('redirect_uri')) {
        //set the redirect
        $redirect = $request->getStage('redirect_uri');
    }

    //make sure these are not added
    $request->removeStage('redirect_uri');
    $request->removeStage('csrf');

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
            'updated schema: %s',
            $request->getStage('singular')
        ),
        $request,
        $response,
        'update',
        'schema',
        $request->getStage('name')
    );

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
 * Process the Schema Remove
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
                'removed schema: %s',
                $request->getStage('name')
            ),
            $request,
            $response,
            'remove',
            'schema',
            $request->getStage('name')
        );
    }

    $this->package('global')->redirect($redirect);
});

/**
 * Process the Schema Restore
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
                'restored schema: %s',
                $request->getStage('name')
            ),
            $request,
            $response,
            'restore',
            'schema',
            $request->getStage('name')
        );
    }

    $this->package('global')->redirect($redirect);
});

/**
 * Process the Schema Export
 *
 * @param Request $request
 * @param Response $response`
 */
$this->get('/admin/system/schema/export', function ($request, $response) {
    //get the name
    $name = $request->getStage('name');
    //get the config path
    $path = $this->package('global')->path('config') . '/schema/';
    //default redirect
    $redirect = '/admin/system/schema/search';

    //if there is a specified redirect_uri
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    //specific schema?
    if (!is_null($name)) {
        //determine the file
        $file = $path . $name . '.php';

        //file does not exists?
        if (!file_exists($file)) {
            //add a flash
            $this->package('global')->flash('Not Found', 'error');
            return $this->package('global')->redirect($redirect);
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
        $this->package('global')->flash('ZipArchive module not found', 'error');
        return $this->package('global')->redirect($redirect);
    }

    //create zip archive
    $zip = new ZipArchive();
    //create temporary file
    $tmp = sys_get_temp_dir() . '/schema.zip';

    //try to open
    if (!$zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        //add a flash
        $this->package('global')->flash('Failed to create archive', 'error');
        return $this->package('global')->redirect($redirect);
    }

    //create an empty directory
    $zip->addEmptyDir('schema');

    //collect all .php files and add it
    foreach (glob($path . '*.php') as $file) {
        //determin json filename
        $name = str_replace('.php', '.json', basename($file));
        //read the content
        $content = json_encode(include($file), JSON_PRETTY_PRINT);

        //add the content to zip
        $zip->addFromString('schema/' . $name, $content);
    }

    //close
    $zip->close();

    //check if file exists
    if (!file_exists($tmp)) {
        //add a flash
        $this->package('global')->flash('Failed to create archive', 'error');
        return $this->package('global')->redirect($redirect);
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
 * Render the Schema Import
 *
 * @param Request $request
 * @param Response $response
 */
$this->get('/admin/system/schema/import', function ($request, $response) {
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
    $class = 'page-admin-system-schema-import page-admin';

    //determine the action
    $data['action'] = 'import';

    //determine the title
    $data['title'] = $this->package('global')->translate('Import System Schema');

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
 * Process the Schema Import
 *
 * @param Request $request
 * @param Response $response
 */
$this->post('/admin/system/schema/import', function ($request, $response) {
    //get the content
    $schema = $request->getStage('schema');
    //get the config path
    $config = $this->package('global')->path('config') . '/schema/';
    //get the type
    $type = substr($schema, 5, strpos($schema, ';base64') - 5);
    //get the route
    $route = '/admin/system/schema/import';
    //get the redirect
    $redirect = '/admin/system/schema/search';

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
        substr($schema, strpos($schema, ';base64,') + 8)
    );

    //json file?
    if ($type === 'application/json') {
        //parse the content
        $content = json_decode($content, true);

        //if not name or is not an array
        if (!is_array($content) || !isset($content['name'])) {
            $response->setError(true, 'Invalid Schema');
            return $this->routeTo('get', $route, $request, $response);
        }

        //create payload
        $payload = $this->makePayload();

        //set schema to stage
        $payload['request']->setStage($content);
        //cleanup
        $payload['request']->removeStage('schema');

        //trigger update
        if (file_exists(sprintf('%s%s.%s', $config, $content['name'], 'php'))) {
            $this->trigger('system-schema-update', $payload['request'], $payload['response']);

        //trigger create
        } else {
            $this->trigger('system-schema-create', $payload['request'], $payload['response']);
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
                'imported schema: %s',
                $content['name']
            ),
            $request,
            $response,
            'import',
            'schema',
            $content['name']
        );

        //it was good
        //add a flash
        $this->package('global')->flash('System Schema was Imported', 'success');
        //redirect
        return $this->package('global')->redirect($redirect);
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
    for ($i = 0; $i < $zip->numFiles; $i++) {
        //get the filename
        $filename = $zip->getNameIndex($i);

        //root or not under schema?
        if ($filename === 'schema/'
        || strpos($filename, 'schema/') === false) {
            continue;
        }

        //parse the content of each filename
        $content = json_decode($zip->getFromName($filename), true);
        //create payload
        $payload = $this->makePayload();

        //skip if schema doesn't have name or is not an array
        if (!isset($content['name']) || !is_array($content)) {
            continue;
        }

        //set the content
        $payload['request']->setStage($content);
        //cleanup
        $payload['request']->removeStage('schema');

        //trigger update
        if (file_exists(sprintf('%s%s.%s', $config, $content['name'], 'php'))) {
            $this->trigger('system-schema-update', $payload['request'], $payload['response']);

        //trigger create
        } else {
            $this->trigger('system-schema-create', $payload['request'], $payload['response']);
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
                'imported schema: %s',
                $content['name']
            ),
            $request,
            $response,
            'import',
            'schema',
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
    $this->package('global')->flash('System Schema was Imported', 'success');
    //redirect
    return $this->package('global')->redirect($redirect);
});
