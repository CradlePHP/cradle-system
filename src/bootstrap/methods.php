<?php //-->

use Cradle\Package\System\Schema;

$this('cradlephp/cradle-system')
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
   * Translates safe query to serialized filters
   *
   * @param array $query
   *
   * @return array
   */
  ->addMethod('mapQuery', function (Schema $schema, array $query = []): array {
    //get valid json fields
    $jsons = array_keys($schema->getFields('json'));
    //eg. map = [['where' => 'product_id =%s', 'binds' => [1]]]
    $map = [];
    //consider q
    //eg. q = 123
    if (isset($query['q'])) {
      $searchable = $schema->getFields('searchable');
      foreach ($schema->getFields('searchable') as $name => $field) {
        $query['like'][$name] = $query['q'];
      }
    }

    //consider filters
    //eg. filter = [product_id => 1, product_meta.ref1 => 123]
    if (isset($query['filter'])) {
      $map = array_merge($map, $this->mapFilters($query['filter'], $jsons));
    }

    //consider like
    //eg. like = [product_id => 1, product_meta.ref1 => 123]
    if (isset($query['like'])) {
      $map = array_merge($map, $this->mapLikes($query['like'], $jsons));
    }

    //consider in
    //eg. in = [product_id => [1, 2, 3], product_meta.ref1 => [1, 2, 3]]
    if (isset($query['in'])) {
      $map = array_merge($map, $this->mapIns($query['in'], $jsons));
    }

    //consider span
    //eg. span = [product_id => [1, 10], product_meta.ref1 => [1, 10]]
    if (isset($query['span'])) {
      $map = array_merge($map, $this->mapSpans($query['in'], $jsons));
    }

    //consider null
    //eg. empty = [product_id, product_meta.ref1]
    if (isset($query['empty'])) {
      $map = array_merge($map, $this->mapEmpties($query['empty'], $jsons));
    }

    //consider notnull
    //eg. nempty = [product_id, product_meta.ref1]
    if (isset($query['nempty'])) {
      $map = array_merge($map, $this->mapNempties($query['nempty'], $jsons));
    }

    return $map;
  })

  /**
   * Translates safe filter to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  ->addMethod('mapFilters', function (array $filters, array $jsons): array {
    $map = [];

    foreach ($filters as $column => $value) {
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $map[] = [
          'where' => $column . ' = %s', 'binds' => [$value]
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $map[] = [
        'where' => $column . ' = %s', 'binds' => [$value]
      ];
    }

    return $map;
  })

  /**
   * Translates safe likes to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  ->addMethod('mapLikes', function (array $filters, array $jsons): array {
    $map = [];

    foreach ($filters as $column => $value) {
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $map[] = [
          'where' => $column . ' LIKE %s', 'binds' => [$value]
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $map[] = [
        'where' => $column . ' LIKE %s', 'binds' => [$value]
      ];
    }

    return $map;
  })

  /**
   * Translates safe ins to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  ->addMethod('mapIns', function (array $filters, array $jsons): array {
    $map = [];

    foreach ($filters as $column => $value) {
      //make sure value is an array
      if (!is_array($value)) {
        $value = [$value];
      }

      //this is like if an array has one of items in another array
      // eg. if product_tags has one of these values [foo, bar, etc.]
      if (in_array($column, $jsons)) {
        $or = [];
        $where = [];
        foreach ($value as $option) {
          $where[] = "JSON_SEARCH(LOWER($column), 'one', %s) IS NOT NULL";
          $or[] = '%' . strtolower($option) . '%';
        }

        $map[] = [
          'where' => '(' . implode(' OR ', $where) . ')',
          'binds' => $or
        ];
        continue;
      }

      //this is the normal
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $binds = implode(', ', array_fill(0, count($value), '%s'));
        $map[] = [
          'where' => sprintf('%s IN (%s)', $column, $binds),
          'binds' => $value
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD has
      //one of these values [foo, bar, etc.]
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $where = array_fill(0, count($value), $column . ' = %s');
      $map[] = [
        'where' => '(' . implode(' OR ', $where) . ')',
        'binds' => $value
      ];
    }

    return $map;
  })

  /**
   * Translates safe spans to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  ->addMethod('mapSpans', function (array $filters, array $jsons): array {
    $map = [];

    foreach ($filters as $column => $value) {
      if (!is_array($value) || empty($value)) {
        continue;
      }

      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        // minimum?
        if (isset($value[0]) && !empty($value[0])) {
          $map[] = [
            'where' => $column . ' >= %s', 'binds' => [$value[0]]
          ];
        }

        // maximum?
        if (isset($value[1]) && !empty($value[1])) {
          $map[] = [
            'where' => $column . ' <= %s', 'binds' => [$value[1]]
          ];
        }

        continue;
      }

      //by chance is it a json filter?
      if (strpos($column, '.') === false) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);

      // minimum?
      if (isset($value[0]) && !empty($value[0])) {
        $map[] = [
          'where' => $column . ' >= %s', 'binds' => [$value[0]]
        ];
      }

      // maximum?
      if (isset($value[1]) && !empty($value[1])) {
        $map[] = [
          'where' => $column . ' <= %s', 'binds' => [$value[1]]
        ];
      }
    }

    return $map;
  })

  /**
   * Translates safe empties to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  ->addMethod('mapEmpties', function (array $filters, array $jsons): array {
    $map = [];

    foreach ($filters as $column) {
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $map[] = [
          'where' => $column . ' IS NULL', 'binds' => []
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $map[] = [
        'where' => $column . ' IS NULL', 'binds' => []
      ];
    }

    return $map;
  })

  /**
   * Translates safe nempties to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  ->addMethod('mapNempties', function (array $filters, array $jsons): array {
    $map = [];

    foreach ($filters as $column) {
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $map[] = [
          'where' => $column . ' IS NOT NULL', 'binds' => []
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $map[] = [
        'where' => $column . ' IS NOT NULL', 'binds' => []
      ];
    }

    return $map;
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
