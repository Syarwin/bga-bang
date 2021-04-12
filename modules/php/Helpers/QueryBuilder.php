<?php
namespace BANG\Helpers;

class QueryBuilder extends \APP_DbObject
{
  private $table,
    $cast,
    $primary,
    $associative,
    $columns,
    $sql,
    $bindValues,
    $where,
    $orWhere,
    $whereCount = 0,
    $isOrWhere = false,
    $limit,
    $orderBy;

  public function __construct($table, $cast = null, $primary = 'id')
  {
    $this->table = $table;
    $this->cast = $cast;
    $this->primary = $primary;
    $this->columns = null;
    $this->sql = null;
    $this->limit = null;
    $this->orderBy = null;
    $this->where = null;
    $this->orWhere = null;
    $this->isOrWhere = false;
  }

  /*************************
   ********* INSERT *********
   *************************/
  /*
   * Single insert, array syntax is [ 'name_of_field' => $value, ... ]
   */
  public function insert($fields = [])
  {
    $this->multipleInsert(array_keys($fields))->values([array_values($fields)]);
    return self::DbGetLastId();
  }

  /*
   * Multiple insert, syntax is : ->multipleInsert(['field1', 'field2'])->values([ [1, 'test'], [2, 'tester'], ....])
   *   !!!! each values must have the content in same order as the fields
   */
  public function multipleInsert($fields = [])
  {
    $keys = implode('`, `', array_values($fields));
    $this->sql = "INSERT INTO `{$this->table}` (`{$keys}`) VALUES";
    return $this;
  }

  public function values($rows = [])
  {
    $vals = [];
    foreach ($rows as $row) {
      $vals[] = "('" . implode("','", array_map('mysql_escape_string', $row)) . "')";
    }

    $this->sql .= implode(',', $vals);
    self::DbQuery($this->sql);
    return self::DbGetLastId();
  }

  /********************************
   ********* BASIC QUERIES *********
   ********************************/

  // Delete : optional parameter $id which add a where clause on primary key
  public function delete($id = null)
  {
    $this->sql = "DELETE FROM `{$this->table}`";
    return isset($id) ? $this->run($id) : $this;
  }

  /*
   * Update: $fields array structure is the same as the one for insert
   *    optional parameter $id adds a where clause on primary key
   */
  public function update($fields = [], $id = null)
  {
    $values = [];
    foreach ($fields as $column => $field) {
      $values[] = "`$column` = " . (is_null($field) ? 'NULL' : "'$field'");
    }

    $this->sql = "UPDATE `{$this->table}` SET " . implode(',', $values);
    return isset($id) ? $this->run($id) : $this;
  }

  /*
   * Inc: $fields array structure is the same as the one for insert, but instead of value to be set,
   *    the array contains the offset
   */
  public function inc($fields = [], $id = null)
  {
    $values = [];
    foreach ($fields as $column => $field) {
      $values[] = "`$column` = `$column` + $field";
    }

    $this->sql = "UPDATE `{$this->table}` SET " . implode(',', $values);
    return isset($id) ? $this->run($id) : $this;
  }

  /*
   * Run a query
   */
  public function run($id = null)
  {
    if (isset($id)) {
      $this->computeWhereClause([[$id]]);
    }
    $this->assembleQueryClauses();
    self::DbQuery($this->sql);
    return self::DbAffectedRow();
  }

  /*********************************
   ********* SELECT QUERIES *********
   *********************************/

  /*
   * Select: fetch rows. Structure is columns is either an array with the name of columns you want to fetch,
   *    or an associative array [ 'alias' => 'fieldname'] if you want to use "AS"
   */
  public function select($columns)
  {
    $cols = ["{$this->primary} AS `result_associative_index`"];

    if (!is_array($columns)) {
      $cols = [$columns];
    } else {
      foreach ($columns as $alias => $col) {
        $cols[] = is_numeric($alias) ? "$col" : "$col AS `$alias`";
      }
    }

    $this->columns = implode(' , ', $cols);
    return $this;
  }

  /*
   * get : run a select query and fetch values
   */
  public function get($returnValueIfOnlyOneRow = false, $debug = false)
  {
    $select = $this->columns ?? "*, {$this->primary} AS `result_associative_index`";
    $this->sql = "SELECT $select FROM `$this->table`";
    $this->assembleQueryClauses();

    if ($debug) {
      throw new \feException($this->sql);
    }
    $res = self::getObjectListFromDB($this->sql);
    $oRes = [];
    foreach ($res as $row) {
      $id = $row['result_associative_index'];
      unset($row['result_associative_index']);

      $val = $row;
      if (is_callable($this->cast)) {
        $val = forward_static_call($this->cast, $row);
      } elseif (is_string($this->cast)) {
        $val = $this->cast == 'object' ? ((object) $row) : new $this->cast($row);
      }

      $oRes[$id] = $val;
    }

    if ($returnValueIfOnlyOneRow && count($oRes) <= 1) {
      return count($oRes) == 1 ? reset($oRes) : null;
    } else {
      return new Collection($oRes);
    }
  }

  public function getSingle()
  {
    return $this->limit(1)->get(true);
  }

  /*
   * ONLY for unary function : COUNT, MAX, MIN
   */
  public function func($func, $field = null)
  {
    if (!in_array($func, ['COUNT', 'MAX', 'MIN'])) {
      throw new \BgaVisibleSystemException('QueryBuilder: func is called with unknown function');
    }

    $field = is_null($field) ? '*' : "`$field`";
    $this->sql = "SELECT $func($field) FROM `$this->table`";
    $this->assembleQueryClauses();
    return (int) self::getUniqueValueFromDB($this->sql);
  }

  public function count($field = null)
  {
    return self::func('COUNT', $field);
  }

  public function min($field)
  {
    return self::func('MIN', $field);
  }

  public function max($field)
  {
    return self::func('MAX', $field);
  }

  /****************************
   ********* MODIFIERS *********
   ****************************/
  /*
   * Append all the modifiers to a query in the right order
   */
  private function assembleQueryClauses()
  {
    $this->sql .= $this->where ?? '';
    $this->sql .= $this->orderBy ?? '';
    $this->sql .= $this->limit ?? '';
  }

  private function protect($arg)
  {
    return is_string($arg) ? "'" . mysql_escape_string($arg) . "'" : $arg;
  }

  protected function computeWhereClause($arg)
  {
    $this->where = is_null($this->where) ? ' WHERE ' : $this->where . ($this->isOrWhere ? ' OR ' : ' AND ');

    if (!is_array($arg)) {
      $arg = [$arg];
    }

    $param = array_pop($arg);
    $n = count($param);
    // Only one param => use primary field
    if ($n == 1) {
      $this->where .= " `{$this->primary}` = " . $this->protect($param[0]);
    }
    // Three params : WHERE $1 OP2 $3
    elseif ($n == 3) {
      $this->where .= '`' . trim($param[0]) . '` ' . $param[1] . ' ' . $this->protect($param[2]);
    }
    // Two params : $1 = $2
    elseif ($n == 2) {
      $this->where .= '`' . trim($param[0]) . '` = ' . $this->protect($param[1]);
    }

    if (!empty($arg)) {
      self::computeWhereClause($arg);
    }
  }

  public function where()
  {
    $this->isOrWhere = false;
    $num_args = func_num_args();
    $args = func_get_args();
    $this->computeWhereClause($num_args == 1 && is_array($args[0]) ? $args[0] : [$args]);
    return $this;
  }

  public function whereIn()
  {
    $this->where = is_null($this->where) ? ' WHERE ' : $this->where . ($this->isOrWhere ? ' OR ' : ' AND ');

    $num_args = func_num_args();
    $args = func_get_args();
    $field = $num_args == 1 ? $this->primary : $args[0];
    $values = $num_args == 1 ? $args[0] : $args[1];
    if (is_null($values)) {
      return $this;
    }

    $this->where .= "`$field` IN ('" . implode("','", $values) . "')";
    return $this;
  }

  public function whereNotIn()
  {
    $this->where = is_null($this->where) ? ' WHERE ' : $this->where . ($this->isOrWhere ? ' OR ' : ' AND ');

    $num_args = func_num_args();
    $args = func_get_args();
    $field = $num_args == 1 ? $this->primary : $args[0];
    $values = $num_args == 1 ? $args[0] : $args[1];
    if (is_null($values)) {
      return $this;
    }

    $this->where .= "`$field` NOT IN ('" . implode("','", $values) . "')";
    return $this;
  }

  public function whereNull($field)
  {
    $this->where = is_null($this->where) ? ' WHERE ' : $this->where . ($this->isOrWhere ? ' OR ' : ' AND ');
    $this->where .= "`$field` IS NULL";
    return $this;
  }

  public function orWhere()
  {
    $this->isOrWhere = true;
    $num_args = func_num_args();
    $args = func_get_args();
    $this->computeWhereClause($num_args == 1 ? $args[0] : [$args]);
    return $this;
  }

  /*
   * Limit
   */
  public function limit($limit, $offset = null)
  {
    $this->limit = " LIMIT {$limit}" . (is_null($offset) ? '' : " OFFSET {$offset}");
    return $this;
  }

  public function orderBy()
  {
    $num_args = func_num_args();
    $args = func_get_args();

    $field_name = '';
    $order = 'ASC';
    if ($num_args == 1) {
      if (is_array($args[0])) {
        $field_name = trim($args[0][0]);
        $order = trim(strtoupper($args[0][1]));
      } else {
        $field_name = trim($args[0]);
      }
    } else {
      $field_name = trim($args[0]);
      $order = trim(strtoupper($args[1]));
    }

    // validate it's not empty and have a proper valuse
    if ($field_name !== null && ($order == 'ASC' || $order == 'DESC')) {
      if ($this->orderBy == null) {
        $this->orderBy = " ORDER BY $field_name $order";
      } else {
        $this->orderBy .= ", $field_name $order";
      }
    }

    return $this;
  }
}

class Collection extends \ArrayObject
{
  public function getIds()
  {
    return array_keys($this->getArrayCopy());
  }

  public function empty()
  {
    return empty($this->getArrayCopy());
  }

  public function first()
  {
    $arr = $this->toArray();
    return isset($arr[0]) ? $arr[0] : null;
  }

  public function toArray()
  {
    return array_values($this->getArrayCopy());
  }

  public function toAssoc()
  {
    return $this->getArrayCopy();
  }

  public function map($func)
  {
    return new Collection(array_map($func, $this->toAssoc()));
  }

  public function merge($arr)
  {
    return new Collection(array_merge($this->toAssoc(), $arr->toAssoc()));
  }

  public function reduce($func, $init)
  {
    return array_reduce($this->toArray(), $func, $init);
  }

  public function filter($func)
  {
    return new Collection(array_filter($this->toAssoc(), $func));
  }

  public function ui()
  {
    return $this->map(function ($elem) {
      return $elem->getUiData();
    });
  }

  public function contains($value)
  {
    return in_array($value, $this->toArray());
  }
}
