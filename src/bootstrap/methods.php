<?php //-->

use Cradle\Package\System\Schema;

$this('cradlephp/cradle-system')
  /**
   * Groups results by their table prefix ie. [table]_column
   *
   * @param mixed $filter
   *
   * @return array
   */
  ->addMethod('deflateRow', function (Schema $schema, array $row): array {
    //get valid json fields
    $jsons = array_keys($schema->getFields('json'));

    foreach ($row as $key => $value) {
      //name should be a json column type
      if (!in_array($key, $jsons) || is_array($row[$key])) {
        continue;
      }

      $row[$key] = json_decode($row[$key], true);
    }

    return $row;
  })

  /**
   * Generates an inner join clause
   *
   * @param mixed $filter
   *
   * @return array
   */
  ->addMethod('getInnerJoins', function (Schema $schema, array $data): array {
    $joins = [];
    $primary = $schema->getPrimaryName();

    if (!isset($data['join'])) {
      $data['join'] = 'forward';
    }

    foreach ($schema->getRelations(1) as $table => $relation) {
      $name = $relation->getName();
      $primary2 = $relation->getPrimaryName();

      $isFilter = isset($data['filter'][$primary2]);
      $isLike = isset($data['filter'][$primary2]);
      $isIn = isset($data['filter'][$primary2]);
      $isSpan = isset($data['filter'][$primary2]);

      $isJoin = $data['join'] === 'all'
        || $data['join'] === 'forward'
        || (
          is_array($data['join'])
          && in_array($name, $data['join'])
        );

      $isEmpty = isset($data['empty'])
        && is_array($data['empty'])
        && in_array($primary2, $data['empty']);

      $isNempty = isset($data['nempty'])
        && is_array($data['nempty'])
        && in_array($primary2, $data['nempty']);

      if (!$isJoin && !$isFilter && !$isLike && !$isIn && !$isEmpty && !$isNempty) {
        continue;
      }

      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      $joins[] = ['type' => 'inner', 'table' => $table, 'where' => $primary];
      $joins[] = ['type' => 'inner', 'table' => $name, 'where' => $primary2];
    }

    foreach ($schema->getReverseRelations(1) as $table => $relation) {
      $name = $relation->getName();
      $primary2 = $relation->getPrimaryName();

      $isFilter = isset($data['filter'][$primary2]);
      $isLike = isset($data['filter'][$primary2]);
      $isIn = isset($data['filter'][$primary2]);
      $isSpan = isset($data['filter'][$primary2]);

      $isJoin = $data['join'] === 'all'
        || $data['join'] === 'reverse'
        || (
          is_array($data['join'])
          && in_array($name, $data['join'])
        );

      $isEmpty = isset($data['empty'])
        && is_array($data['empty'])
        && in_array($primary2, $data['empty']);

      $isNempty = isset($data['nempty'])
        && is_array($data['nempty'])
        && in_array($primary2, $data['nempty']);

      if (!$isJoin && !$isFilter && !$isLike && !$isIn && !$isEmpty && !$isNempty) {
        continue;
      }

      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      $joins[] = ['type' => 'inner', 'table' => $table, 'where' => $primary];
      $joins[] = ['type' => 'inner', 'table' => $name, 'where' => $primary2];
    }

    return $joins;
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
    if (isset($query['filters']) && is_array($query['filters'])) {
      $map = $query['filters'];
    }

    //consider q
    //eg. q = 123
    if (isset($query['q'])) {
      $searchable = $schema->getFields('searchable');
      $keywords = $query['q'];
      if (!is_array($keywords)) {
        $keywords = [ $keywords ];
      }

      $map = array_merge($map, $this->mapKeywords($keywords, $searchable));
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
   * Translates safe q to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  ->addMethod('mapKeywords', function (array $filters, array $searchable): array {
    $map = [];

    foreach ($filters as $keyword) {
      $binds = $where = [];
      foreach ($searchable as $name => $field) {
        $where[] = sprintf('LOWER(%s) LIKE %%s', $name);
        $binds[] = sprintf('%%%s%%', strtolower($keyword));
      }

      $map[] = [
        'where' => sprintf('(%s)', implode(' OR ', $where)),
        'binds' => $binds
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
   * Groups results by their table prefix ie. [table]_column
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
