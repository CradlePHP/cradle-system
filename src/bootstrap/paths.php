<?php
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

return function ($request, $response) {
  $global = $this('global');
  $root = $global->path('root');
  $global->path('package', $root . '/config/fieldset');
  $global->path('schema', $root . '/config/schema');
};
