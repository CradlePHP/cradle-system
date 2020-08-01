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
   * @var string $path
   */
  protected static $path;

  /**
   * Instantiate the Fieldet given the name
   *
   * @param *string $name
   * @param ?string $path
   *
   * @return Fieldset
   */
  public static function load(string $name, ?string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $source = sprintf('%s/%s.php', $path, $name);
    if (!file_exists($source)) {
      throw SystemException::forFileNotFound($source);
    }

    return new static(include $source);
  }

  /**
   * Sets folder where fieldset is located
   *
   * @param *string $path
   */
  public static function setFolder(string $path)
  {
    if (!is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    static::$path = $path;
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
    $path = self::$path;
    if (isset($filters['path']) && is_dir($filters['path'])) {
      $path = $filters['path'];
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
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
   * Archives a fieldset
   *
   * @param ?string $path
   *
   * @return Fieldset
   */
  public function archive(?string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $name = $this->getName();

    $source = sprintf('%s/%s.php', $path, $name);
    if (!file_exists($source)) {
      throw SystemException::forFileNotFound($source);
    }

    $destination = sprintf('%s/_%s.php', $path, $name);
    if (file_exists($destination)) {
      throw SystemException::forArchiveExists($destination);
    }

    rename($source, $destination);
    return $this;
  }

  /**
   * Deletes a fieldset
   *
   * @param ?string $path
   *
   * @return Fieldset
   */
  public function delete(?string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $source = sprintf('%s/%s.php', $path, $this->getName());
    if (!file_exists($source)) {
      throw SystemException::forFileNotFound($source);
    }

    unlink($source);
    return $this;
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
    $formatted = $data;
    //loop through each field
    $fields = $this->getFields();
    foreach ($fields as $key => $field) {
      //if it's not set
      if (!array_key_exists($key, $data)) {
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
      if (!empty($types) && empty(array_intersect($types, $field['types']))) {
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
      $fieldSchema = FieldHandler::getField($field['field']['type']);
      //if no field
      if (!$fieldSchema) {
        continue;
      }

      //make sure we have a value
      if (!isset($data[$key])) {
        $data[$key] = null;
      }

      //set name
      $fieldSchema->setName($key);

      //set attributes
      if (isset($field['field']['attributes'])
        && is_array($field['field']['attributes'])
      ) {
        $fieldSchema->setAttributes($field['field']['attributes']);
      }

      //set options
      if (isset($field['field']['options'])
        && is_array($field['field']['options'])
      ) {
        $fieldSchema->setOptions($field['field']['options']);
      }

      //set parameters
      if (isset($field['field']['parameters'])) {
        if (!is_array($field['field']['parameters'])) {
          $field['field']['parameters'] = [$field['field']['parameters']];
        }

        $fieldSchema->setParameters($field['field']['parameters']);
      }

      $form[$key] = $fieldSchema->render($data[$key]);
    }

    return $form;
  }

  /**
   * Saves the fieldset to file
   *
   * @return Fieldset
   */
  public function save(string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $destination = sprintf('%s/%s.php', $path, $this->getName());

    //if it is not a file
    if (!file_exists($destination)) {
      //make the file
      touch($destination);
      chmod($destination, 0777);
    }

    // at any rate, update the config
    file_put_contents($destination, sprintf(
      "<?php //-->\nreturn %s;",
      var_export($this->data, true)
    ));

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
      //make sure there is a value we can compare
      if (!array_key_exists($key, $data)) {
        $data[$key] = null;
        //if the value is not set, dont field validate..
      } else {
        //load up the field
        $fieldSchema = FieldHandler::getField($field['field']['type']);
        //if it's not valid
        if ($fieldSchema && !$fieldSchema->valid($data[$key])) {
          //set an error
          $errors[$key] = 'Invalid field format';
          continue;
        }
      }

      //if there is no validation
      if (!isset($field['validation'])
        || !is_array($field['validation'])
        || empty($field['validation'])
      ) {
        //it's obviously valid
        continue;
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
          break;
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
      //if it's not set
      if (!array_key_exists($key, $data)
        && isset($field['default'])
        && $field['default']
      ) {
        //use the default
        $data[$key] = $field['default'];
      }

      //if there is no type
      if (!isset($field['field']['type'])) {
        //it's obviously not valid
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
   * Restores a fieldset
   *
   * @param ?string $path
   *
   * @return Fieldset
   */
  public function restore(?string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $name = $this->getName();

    $source = sprintf('%s/_%s.php', $path, $name);
    if (!file_exists($source)) {
      throw SystemException::forArchiveNotFound($source);
    }

    $destination = sprintf('%s/%s.php', $path, $name);
    if (file_exists($destination)) {
      throw SystemException::forFileExists($destination);
    }

    rename($source, $destination);
    return $this;
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
          $types[] = FieldsetTypes::TYPE_UNIQUE;
        }
      }
    }

    return $types;
  }
}
