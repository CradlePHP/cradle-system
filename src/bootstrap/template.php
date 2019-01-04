<?php
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

return function($request, $response) {
    /**
     * Add Template Builder
     */
    $this->package('cradlephp/cradle-system')->addMethod('template', function (
        $file,
        array $data = [],
        $partials = [],
        $template  = null,
        $partial = null
    ) {
        //if no template path was set
        if (!trim($template)) {
            $template = dirname(__DIR__) . '/template';
        }

        //if no partial path was set
        if (!trim($partial)) {
            $partial = dirname(__DIR__) . '/template';
        }

        if (substr($template, -1) !== '/') {
            $template .= '/';
        }

        if (substr($partial, -1) !== '/') {
            $partial .= '/';
        }

        // check for partials
        if (!is_array($partials)) {
            $partials = [$partials];
        }

        $paths = [];

        foreach ($partials as $name) {
            //Sample: product_comment => product/_comment
            //Sample: flash => _flash
            $path = str_replace('_', '/', $name);
            $last = strrpos($path, '/');

            if($last !== false) {
                $path = substr_replace($path, '/_', $last, 1);
            }

            $path = $path . '.html';

            if (strpos($path, '_') === false) {
                $path = '_' . $path;
            }

            $paths[$name] = $partial . $path;
        }

        $file = $template . $file . '.html';

        //render
        return cradle('global')->template($file, $data, $paths);
    });
};
