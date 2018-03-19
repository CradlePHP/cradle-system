<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
require_once __DIR__ . '/src/Schema/events.php';
require_once __DIR__ . '/src/Schema/controller.php';
require_once __DIR__ . '/src/Relation/events.php';
require_once __DIR__ . '/src/Relation/controller.php';
require_once __DIR__ . '/src/Object/events.php';
require_once __DIR__ . '/src/Object/controller.php';

//bootstrap
$this->preprocess(include __DIR__ . '/src/helpers.php');
