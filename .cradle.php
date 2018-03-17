<?php //-->
include_once __DIR__ . '/src/helpers.php';

include_once __DIR__ . '/src/Schema/events.php';
include_once __DIR__ . '/src/Schema/controller.php';

include_once __DIR__ . '/src/Relation/events.php';
include_once __DIR__ . '/src/Relation/controller.php';

include_once __DIR__ . '/src/Object/events.php';
include_once __DIR__ . '/src/Object/controller.php';

$cradle->preprocess(function($request, $response) {
    //add helpers
    $handlebars = cradle('global')->handlebars();
    include __DIR__ . '/src/helpers.php';

    $this->package('cradlephp/cradle-system')
        /**
         * Add Template Builder
         *
         */
        ->addMethod('template', function ($type, $file, array $data = [], $partials = []) {
            // get the root directory
            $type = ucwords($type);
            $root =  sprintf('%s/src/%s/template/', __DIR__, $type);

            // check for partials
            if (!is_array($partials)) {
                $partials = [$partials];
            }

            $paths = [];

            foreach ($partials as $partial) {
                //Sample: product_comment => product/_comment
                //Sample: flash => _flash
                $path = str_replace('_', '/', $partial);
                $last = strrpos($path, '/');

                if($last !== false) {
                    $path = substr_replace($path, '/_', $last, 1);
                }

                $path = $path . '.html';

                if (strpos($path, '_') === false) {
                    $path = '_' . $path;
                }

                $paths[$partial] = $root . $path;
            }

            $file = $root . $file . '.html';

            //render
            return cradle('global')->template($file, $data, $paths);
        });
});
