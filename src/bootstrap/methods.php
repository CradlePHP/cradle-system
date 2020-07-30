<?php //-->

use Cradle\Package\System\Schema;

$this->package('/module/cradle-system')
  /**
   * Generates an inner join clause
   *
   * @param mixed $filter
   *
   * @return array
   */
  ->addMethod('getInnerJoins', function (Schema $schema, $filters = []): array {
    $joins = [];
    $primary = $schema->getPrimaryName();
    $filter = $this->getJoinFilters($filter);

    foreach ($schema->getRelations(1) as $table => $relation) {
      $name = $relation->getName();
      $primary2 = $relation->getPrimaryName();
      if (!in_array($name, $data['joins'])) {
        continue;
      }

      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      $joins[] = ['type' => 'inner', 'table' => $table, 'where' => $primary];
      $joins[] = ['type' => 'inner', 'table' => $name, 'where' => $primary2];
    }

    foreach ($schema->getReverseRelations(1) as $table => $relation) {
      $name = $relation->getName();
      $primary2 = $relation->getName();
      if (!in_array($name, $data['joins'])) {
        continue;
      }

      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      $joins[] = ['type' => 'inner', 'table' => $table, 'where' => $primary];
      $joins[] = ['type' => 'inner', 'table' => $name, 'where' => $primary2];
    }

    return $joins;
  })

  /**
   * Generates an inner join clause
   *
   * @param mixed $filter
   *
   * @return array
   */
  ->addMethod('getJoinFilters', function (Schema $schema, $filters = []): array {
    if ($filter === 'all') {
      $filter = [];
      foreach ($schema->getRelations() as $relation) {
        $filter[] = $relation->getName();
      }

      foreach ($schema->getReverseRelations() as $relation) {
        $filter[] = $relation->getName();
      }
    }

    if (!is_array($filter)) {
      $filter = [];
    }

    return $filter;
  })

  /**
   * Generates an inner join clause
   *
   * @param mixed $filter
   *
   * @return array
   */
  ->addMethod('organizeRow', function (array $results): array {
    $organized = [];
    foreach ($results as $key => $value) {
      if (strpos($key, '_') === false) {
        $organized[$key] = $value;
        continue;
      }

      $group = substr($key, 0, strpos($key, '_'));
      $organized[$group][$key] = $value;
    }

    return $organized;
  })

;
