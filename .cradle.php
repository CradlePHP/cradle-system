<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
require_once __DIR__ . '/src/Schema/events.php';
require_once __DIR__ . '/src/Relation/events.php';
require_once __DIR__ . '/src/Model/events.php';

//bootstrap
$this
    ->preprocess(include __DIR__ . '/src/bootstrap/schema.php')
    ->preprocess(include __DIR__ . '/src/bootstrap/helpers.php')
    ->preprocess(include __DIR__ . '/src/bootstrap/template.php')
    ->preprocess(include __DIR__ . '/src/bootstrap/files.php')
    ->preprocess(include __DIR__ . '/src/bootstrap/controller.php');
