<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
//bootstrap
$this
    ->preprocess(include __DIR__ . '/src/helpers.php')
    ->preprocess(include __DIR__ . '/src/Schema/events.php')
    ->preprocess(include __DIR__ . '/src/Schema/controller.php')
    ->preprocess(include __DIR__ . '/src/Relation/events.php')
    ->preprocess(include __DIR__ . '/src/Relation/controller.php')
    ->preprocess(include __DIR__ . '/src/Object/events.php')
    ->preprocess(include __DIR__ . '/src/Object/controller.php');
