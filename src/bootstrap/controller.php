<?php
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

return function($request, $response) {
    include_once dirname(__DIR__) . '/Model/controller.php';
    include_once dirname(__DIR__) . '/Relation/controller.php';
    include_once dirname(__DIR__) . '/Fieldset/controller.php';
    include_once dirname(__DIR__) . '/Schema/controller.php';
};
