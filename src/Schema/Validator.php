<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Package\System\Schema;

use Cradle\Package\System\Schema;
use Cradle\Package\System\SystemException;

/**
 * Validator layer
 *
 * @vendor   Cradle
 * @package  System
 * @author   Christan Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Validator
{
  /**
   * Returns Table Create Errors
   *
   * @param *array $data
   * @param array  $errors
   *
   * @return array
   */
  public static function getCreateErrors(array $data, array $errors = [])
  {
    if (!isset($data['singular']) || empty($data['singular'])) {
      $errors['singular'] = 'Singular is required';
    }

    if (!isset($data['plural']) || empty($data['plural'])) {
      $errors['plural'] = 'Plural is required';
    }

    if (!isset($data['name']) || empty($data['name'])) {
      $errors['name'] = 'Keyword is required';
    }

    if (!isset($data['fields']) || empty($data['fields'])) {
      $errors['fields'] = 'Fields is required';
    }

    if (isset($data['name'])) {
      $exists = true;
      try {
        Schema::load($data['name']);
      } catch (SystemException $e) {
        $exists = false;
      }

      if ($exists) {
        $errors['name'] = 'Schema already exists';
      }
    }

    return self::getOptionalErrors($data, $errors);
  }

  /**
   * Returns Table Update Errors
   *
   * @param *array $data
   * @param array  $errors
   *
   * @return array
   */
  public static function getUpdateErrors(array $data, array $errors = [])
  {
    if (isset($data['singular']) && empty($data['singular'])) {
      $errors['singular'] = 'Singular is required';
    }

    if (isset($data['plural']) && empty($data['plural'])) {
      $errors['plural'] = 'Plural is required';
    }

    if (isset($data['name']) && empty($data['name'])) {
      $errors['name'] = 'Keyword is required';
    }

    if (!isset($data['fields']) || empty($data['fields'])) {
      $errors['fields'] = 'Fields is required';
    }

    return self::getOptionalErrors($data, $errors);
  }

  /**
   * Returns Table Optional Errors
   *
   * @param *array $data
   * @param array  $errors
   *
   * @return array
   */
  public static function getOptionalErrors(array $data, array $errors = [])
  {
    //validations

    if (isset($data['singular']) && strlen($data['singular']) <= 2) {
      $errors['singular'] = 'Singular should be longer than 2 characters';
    }

    if (isset($data['singular']) && strlen($data['singular']) >= 255) {
      $errors['singular'] = 'Singular should be less than 255 characters';
    }

    if (isset($data['plural']) && strlen($data['plural']) <= 2) {
      $errors['plural'] = 'Plural should be longer than 2 characters';
    }

    if (isset($data['plural']) && strlen($data['plural']) >= 255) {
      $errors['plural'] = 'Plural should be less than 255 characters';
    }

    if (isset($data['name']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['name'])) {
      $errors['name'] = 'Keyword must only have letters, numbers, dashes';
    }

    if (isset($data['relations'])) {
      $relations = [];
      foreach ($data['relations'] as $i => $relation) {
        if (in_array($relation['name'], $relations)) {
          $errors['relations'] = 'Duplicate Relations';
          break;
        }

        if (!trim($relation['name'])) {
          $errors['relations'] = 'Empty relation name';
          break;
        }

        $relations[] = $relation['name'];
      }
    }

    return $errors;
  }
}
