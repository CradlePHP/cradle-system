<?php
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

return function($request, $response) {
    $package = $this->package('global');

    //set the fieldset path
    $config = $package->path('config');
    $package->path('fieldset', $config . '/fieldset');

    /**
     * A helper to manage the fieldset file system
     */
    $package->addMethod('fieldset', function ($path, $data = null) {
        static $cache = [];

        //determine file path
        $config = $this->path('fieldset');
        $file = $config . '/' . $path . '.php';

        //is it already in memory?
        if (!isset($cache[$path])) {
            if (!file_exists($file)) {
                $cache[$path] = [];
            } else {
                //get the data and cache
                $cache[$path] = include($file);
            }
        }

        //if data is false
        if ($data === false) {
            //they want to remove the cache
            $data = $cache[$path];
            unset($cache[$path]);
            //return the data
            return $data;
        }

        if (is_null($data)) {
            //return the data
            return $cache[$path];
        }

        //they are trying to write
        //if it is not a folder
        if (!is_dir(dirname($file))) {
            //make a folder
            mkdir(dirname($file), 0777, true);
        }

        //if it is not a file
        if (!file_exists($file)) {
            //make the file
            touch($file);
            chmod($file, 0777);
        }

        $cache[$path] = $data;

        // at any rate, update the config
        $content = "<?php //-->\nreturn " . var_export($cache[$path], true) . ';';
        file_put_contents($file, $content);

        return $this;
    });
};
