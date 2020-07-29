<?php //-->
/*
 * This file is part of the Core package of the Cradle PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE
 * distributed with this package.
 */

if (!defined('CRADLE_CWD')) {
  if(file_exists(__DIR__ . '/../../../.cradle.php')) {
    require_once __DIR__ . '/../../../.cradle.php';
  }

  if(file_exists(__DIR__ . '/../../../../.cradle.php')) {
    require_once __DIR__ . '/../../../../.cradle.php';
  }
}

if (!defined('CRADLE_CWD')) {
  throw new Exception('Cannot find root of project');
}

cradle()->prepare();
