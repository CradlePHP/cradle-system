<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System;

use Cradle\Data\Registry;
use Cradle\Package\System\Fieldset\Field\FieldHandler;
use Cradle\Package\System\Fieldset\FieldsetTypes;
use Cradle\Package\System\Fieldset\Validation\ValidationHandler;
use Cradle\Package\System\Fieldset\Format\FormatHandler;

/**
 * Model Fieldset Manager. This was made
 * take advantage of pass-by-ref
 *
 * @vendor   Cradle
 * @package  System
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Fieldset extends Registry
{
  /**
   * Instantiate the Fieldet given the name
   *
   * @param *string $name
   *
   * @return Fieldset
   */
  public static function load(string $name): Fieldset
  {
    $fielset = cradle('global')->config('fieldset/' . $name);

    if (!$fielset) {
      throw SystemException::forFieldsetNotFound($name);
    }

    return new static($fieldset);
  }

  /**
   * Returns fieldset classes that match the given filters
   *
   * @param array $filters Keys can be `path`, `active`, `name`
   *
   * @return array
   */
  public static function search(array $filters = []): array
  {
    $path = $global->path('fieldset');
    if (isset($filters['path']) && is_dir($filters['path'])) {
      $path = $filters['path'];
    }

    $files = scandir($path);

    $active = 1;
    if (isset($filters['active'])) {
      $active = $filters['active'];
    }

    $rows = [];
    foreach ($files as $file) {
      $name = basename($file, '.php');
      if (//if this is not a php file
        strpos($file, '.php') === false
        //or active and this is not active
        || ($active && strpos($file, '_') === 0)
        //or not active and active
        || (!$active && strpos($file, '_') !== 0)
        //or not name
        || (isset($filters['name']) && $filters['name'] !== $name)
      ) {
        continue;
      }

      $rows[$name] = static::load($name);
    }

    return $rows;
  }

  /**
   * Formats the given data to be outputted
   *
   * @param array $data raw values
   *
   * @return array
   */
  public function format(array $data, string $format = 'list'): array
  {
    $formatted = [];
    //loop through each field
    $fields = $this->getFields();
    foreach ($fields as $key => $field) {
      //if it's not set
      if (!isset($data[$key])) {
        continue;
      }

      //if there is no format
      if (!isset($field[$format]['format'])) {
        //take it as is.
        $formatted[$key] = $data[$key];
        continue;
      }

      //load up the formatter
      $formatter = FormatHandler::getFormatter($field[$format]['format']);
      //if no formatter
      if (!$formatter) {
        continue;
      }

      //set parameters
      if (isset($field[$format]['parameters'])) {
        if (!is_array($field[$format]['parameters'])) {
          $field[$format]['parameters'] = [$field[$format]['parameters']];
        }
        $formatter->setParameters($field[$format]['parameters']);
      }

      $formatted[$key] = $formatter->format($data[$key]);
    }

    return $formatted;
  }

  /**
   * Returns All fields
   *
   * @return array
   */
  public function getFields(string ...$types): array
  {
    $results = [];
    if (!isset($this->data['fields'])
      || empty($this->data['fields'])
    ) {
      return $results;
    }

    $table = $this->data['name'];
    foreach ($this->data['fields'] as $field) {
      $key = $table . '_' . $field['name'];
      $field['types'] = $this->getTypes($field);
      //quick way of filtering
      if (!empty($types) && empty(array_intersect($ypes, $field['types']))) {
        continue;
      }
      $results[$key] = $field;
    }

    return $results;
  }

  /**
   * Renders all the fields
   *
   * @param array $data Values of the form
   *
   * @return array
   */
  public function getForm(array $data = []): array
  {
    $form = [];
    //loop through each field
    $fields = $this->getFields();
    foreach ($fields as $key => $field) {
      //if there is no type
      if (!isset($field['field']['type'])) {
        //skip
        continue;
      }

      //load up the field
      $field = FieldHandler::getField($field['field']['type']);
      //if no field
      if (!$field) {
        continue;
      }

      //make sure we have a value
      if (!isset($data[$key])) {
        $data[$key] = null;
      }

      //set name
      $field->setName($key);

      //set attributes
      if (isset($field['field']['attributes'])
        && is_array($field['field']['attributes'])
      ) {
        $field->setAttributes($field['field']['attributes']);
      }

      //set options
      if (isset($field['field']['options'])
        && is_array($field['field']['options'])
      ) {
        $field->setOptions($field['field']['options']);
      }

      //set parameters
      if (isset($field['field']['parameters'])) {
        if (!is_array($field['field']['parameters'])) {
          $field['field']['parameters'] = [$field['field']['parameters']];
        }

        $field->setParameters($field['field']['parameters']);
      }

      $form[$key] = $field->render($data[$key]);
    }

    return $form;
  }

  /**
   * Saves the fieldset to file
   *
   * @return Fieldset
   */
  public function save(): Fieldset
  {
    cradle('global')->config('fieldset/' . $this->getName(), $this->data);
    return $this;
  }

  /**
   * Validates the given data against the defined validation
   *
   * @param array $data values to compare
   *
   * @return array
   */
  public function getErrors(array $data): array
  {
    $errors = [];
    //loop through each field
    $fields = $this->getFields();
    foreach ($fields as $key => $field) {
      //if there is no validation
      if (!isset($field['validation'])
        || !is_array($field['validation'])
        || empty($field['validation'])
      ) {
        //it's obviously valid
        continue;
      }

      //make sure there is a value we can compare
      if (!isset($data[$key])) {
        $data[$key] = null;
      }

      //for each validation
      foreach ($field['validation'] as $validation) {
        //if there is no method set
        if (!isset($validation['method'])) {
          //skip
          continue;
        }

        //load up the validator
        $validator = ValidationHandler::getValidator($validation['method']);
        //if no validator
        if (!$validator) {
          //set an error
          $errors[$key] = sprintf('Validation %s not setup', $validation['method']);
          break;
        }

        //make sure we have an error message
        $message = 'Invalid';
        if (isset($validation['message'])) {
          $message = $validation['message'];
        }

        //set parameters
        if (isset($validation['parameters'])) {
          if (!is_array($validation['parameters'])) {
            $validation['parameters'] = [$validation['parameters']];
          }
          $validator->setParameters($validation['parameters']);
        }

        //if it's not valid
        if (!$validator->valid($data[$key])) {
          //set an error
          $errors[$key] = $message;
        }
      }
    }

    return $errors;
  }

  /**
   * Prepares the given data to be saved into an eventual store
   *
   * @param array $data Values to prepare
   *
   * @return array
   */
  public function prepare(array $data, bool $defaults = false): array
  {
    $prepped = [];
    //loop through each field
    $fields = $this->getFields();
    foreach ($fields as $key => $field) {
      //if it's not set and dont use defaults
      if (!isset($data[$key]) && !$defaults) {
        continue;
      }

      //if it's not set there are no defaults
      if (!isset($data[$key])
        && (
          !isset($field['default'])
          || !$field['default']
        )
      ) {
        continue;
      }

      //if it's not set
      if (!isset($data[$key]) ) {
        //use the default
        $prepped[$key] = $field['default'];
        continue;
      }

      //if there is no type
      if (!isset($field['field']['type'])) {
        //it's obviously valid
        continue;
      }

      //load up the field
      $field = FieldHandler::getField($field['field']['type']);
      //if no field
      if (!$field) {
        continue;
      }

      $prepped[$key] = $field->prepare($data[$key]);
    }

    return $prepped;
  }

  /**
   * Returns all possible advanced data types given the field
   *
   * @param *array $field
   *
   * @return array
   */
  protected function getTypes(array $field): array
  {
    $types = [];
    $schema = FieldHandler::getField($field['field']['type']);
    if ($schema) {
      $types = $schema->getConfigTypes();
    }

    if (isset($field['list']['format'])
      && $field['list']['format'] !== 'hide'
    ) {
      $types[] = FieldsetTypes::TYPE_LISTED;
    }

    if (isset($field['detail']['format'])
      && $field['detail']['format'] !== 'hide'
    ) {
      $types[] = FieldsetTypes::TYPE_DETAILED;
    }

    if (isset($field['default']) && trim($field['default'])) {
      $types[] = FieldsetTypes::TYPE_DEFAULTED;
    }

    if (isset($field['validation'])) {
      foreach ($field['validation'] as $validation) {
        if ($validation['method'] === 'required') {
          $types[] = FieldsetTypes::TYPE_REQUIRED;
        }

        if ($validation['method'] === 'unique') {
          $types[] = FieldsetTypes::TYPE_UNIQUEs;
        }
      }
    }

    return $types;
  }
}
