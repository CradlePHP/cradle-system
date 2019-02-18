<?php
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\File;

return function ($request, $response) {
    //for backwards compatibility
    $events = $this
        ->getEventHandler()
        ->match('utility-file-upload');

    if (empty($events)) {
        /**
         * File Upload (supporting job)
         *
         * @param Request  $request
         * @param Response $response
         */
        $this->on('utility-file-upload', function ($request, $response) {
            //get data
            $data = $request->getStage('data');

            //try cdn if enabled
            $global = $this->package('global');
            $s3 = $global->service('s3-main');
            $upload = $global->path('upload');

            //try cdn if enabled
            $data = File::base64ToS3($data, $s3);

            //try being old school
            $data = File::base64ToUpload($data, $upload);

            $response->setError(false)->setResults([
                'data' => $data
            ]);
        });
    }
};
