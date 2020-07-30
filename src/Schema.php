<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Package\System;

use Cradle\Package\System\Schema\SchemaTypes;

/**
 * Model Fieldset Manager. This was made
 * take advantage of pass-by-ref
 *
 * @vendor   Cradle
 * @package  System
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Schema extends Fieldset
{
  /**
   * Instantiate the Fieldet given the name
   *
   * @return array
   */
  public static function load(string $name): Fieldset
  {
    $schema = cradle('global')->config('schema/' . $name);

    if (!$schema) {
      throw SystemException::forSchemaNotFound($name);
    }

    return new static($schema);
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
    if (!isset($filters['path'])) {
      $filters['path'] = $global->path('schema');
    }

    $rows = parent::search($filters);

    //ex. ?filter[relation]=product
    //ex. ?filter[relation]=product,1
    if (isset($filters['relation']) && trim($filters['relation'])) {
      $many = null;
      $relation = $filters['relation'];
      if (strpos($filters['relation'], ',') !== false) {
        [$relation, $many] = explode(',', $filters['relation'], 2);
        $relation = trim($relation);
        $many = (int) $many;
      }

      foreach ($rows as $key => $row) {
        $relations = $row->getRelations($many, $relation);
        if (empty($relations)) {
          unset($rows[$key]);
        }
      }
    }

    return $rows;
  }

  /**
   * Returns primary
   *
   * @return string
   */
  public function getPrimaryName(): string
  {
    return $this->getName() . '_id';
  }

  /**
   * Returns relational data
   *
   * @param ?int    $many
   * @param ?string $name
   *
   * @return array
   */
  public function getRelations(?int $many = null, ?string $name = null): array
  {
    $relations = $this->get('relations');
    if (empty($relations)) {
      return [];
    }

    $table = $this->getName();
    $primary1 = $this->getPrimaryName();

    $results = [];
    foreach ($relations as $relation) {
      if ((is_numeric($many) && $many != $relation['many'])
        || (is_string($name) && $name !== $relation['name'])
      ) {
        continue;
      }

      $key = $table . '_' . $relation['name'];

      try {
        $results[$key] = Schema::load($relation['name']);
      } catch (Exception $e) {
        //this is not a registered schema
        //lets make a best guess
        $results[$key] = Schema::i([
          'name' => $relation['name'],
          'singular' => ucfirst($relation['name']),
          'plural' => ucfirst($relation['name']) . 's'
        ]);
      }

      $primary2 = $results[$key]->getPrimaryName();

      $results[$key]
        ->set('table', $key)
        ->set('primary1', $primary1)
        ->set('primary2', $primary2)
        ->set('many', $relation['many']);

      //case for relating to itself ie. post_post
      if ($primary1 === $primary2) {
        $results[$key]
          ->set('primary1', $primary1 . '_1')
          ->set('primary2', $primary2 . '_2');
      }
    }

    return $results;
  }

  /**
   * Returns reverse relational data
   *
   * @param ?int $many
   *
   * @return array
   */
  public function getReverseRelations(?int $many = null): array
  {
    $table = $this->getName();
    $relation = [$table];
    if (is_numeric($many)) {
      $relation[] = $many;
    }

    $rows = static::search([ 'relation' => implode(',', $relation) ]);

    if (empty($rows)) {
      return [];
    }

    $results = [];
    foreach ($rows as $key => $relation) {
      $reversed = $relation->getRelations($many, $table);

      if (!isset($reversed[0])
        || !trim($reversed[0]->get('table'))
        || !trim($reversed[0]->get('primary1'))
        || !trim($reversed[0]->get('primary2'))
        || !trim($reversed[0]->get('many'))
        //dont do post_post
        || $reversed[0]->getName() === $table
      ) {
        continue;
      }

      $relation
        ->set('table', $reversed[0]->get('table'))
        ->set('primary1', $reversed[0]->get('primary1'))
        ->set('primary2', $reversed[0]->get('primary2'))
        ->set('many', $reversed[0]->get('many'));

      $results[$reversed[0]->get('table')] = $relation;
    }

    return $results;
  }

  /**
   * Based on the data will generate a suggestion format
   *
   * @param array
   *
   * @return string
   */
  public function getSuggestion(array $data): string
  {
    $suggestion = trim($this->get('suggestion'));
    //if no suggestion format
    if (!$suggestion) {
      //use best guess
      $suggestion = null;
      foreach ($data as $key => $value) {
        if (is_numeric($value)
          || (
            isset($value[0])
            && is_numeric($value[0])
          )
        ) {
          continue;
        }

        $suggestion = $value;
        break;
      }

      //if still no suggestion
      if (is_null($suggestion)) {
        //just get the first one, i guess.
        foreach ($data as $key => $value) {
          $suggestion = $value;
          break;
        }
      }

      return $suggestion;
    }

    $template = cradle('global')->handlebars()->compile($suggestion);
    return $template($data);
  }

  /**
   * Saves the schema to file
   *
   * @return Fieldset
   */
  public function save(): Fieldset
  {
    cradle('global')->config('schema/' . $this->getName(), $this->data);
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
    $types = parent::getTypes($field);

    if (isset($field['searchable']) && $field['searchable']) {
      $types[] = SchemaTypes::TYPE_INDEXABLE;
      $types[] = SchemaTypes::TYPE_SEARCHABLE;
    }

    if (isset($field['filterable']) && $field['filterable']) {
      $types[] = SchemaTypes::TYPE_INDEXABLE;
      $types[] = SchemaTypes::TYPE_FILTERABLE;
    }

    if (isset($field['sortable']) && $field['sortable']) {
      $types[] = SchemaTypes::TYPE_INDEXABLE;
      $types[] = SchemaTypes::TYPE_SORTABLE;
    }

    return $types;
  }
}
