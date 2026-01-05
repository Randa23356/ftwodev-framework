<?php

namespace Engine;

use PDO;

class QueryBuilder
{
    protected $db;
    protected $table;
    protected $wheres = [];
    protected $orders = [];
    protected $limit;
    protected $offset;
    protected $joins = [];
    protected $selects = ['*'];
    protected $bindings = [];
    protected $operation = 'SELECT';

    public function __construct($table, $db)
    {
        $this->table = $table;
        $this->db = $db;
    }

    // SELECT operations
    public function select($columns = '*')
    {
        if (is_array($columns)) {
            $this->selects = $columns;
        } else {
            $this->selects = func_get_args();
        }
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];

        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];

        $this->bindings[] = $value;
        return $this;
    }

    public function whereIn($column, $values)
    {
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => 'AND'
        ];

        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function whereNotIn($column, $values)
    {
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $this->wheres[] = [
            'type' => 'not_in',
            'column' => $column,
            'values' => $values,
            'boolean' => 'AND'
        ];

        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function whereNull($column)
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function whereNotNull($column)
    {
        $this->wheres[] = [
            'type' => 'not_null',
            'column' => $column,
            'boolean' => 'AND'
        ];
        return $this;
    }

    // JOIN operations
    public function join($table, $first, $operator, $second)
    {
        $this->joins[] = [
            'type' => 'inner',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        return $this;
    }

    public function leftJoin($table, $first, $operator, $second)
    {
        $this->joins[] = [
            'type' => 'left',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        return $this;
    }

    // ORDER operations
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        return $this;
    }

    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'DESC');
    }

    // LIMIT operations
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function take($limit)
    {
        return $this->limit($limit);
    }

    public function skip($offset)
    {
        return $this->offset($offset);
    }

    // Execute queries
    public function get()
    {
        $this->operation = 'SELECT';
        $sql = $this->buildSelectQuery();
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->bindings);
        
        return $stmt->fetchAll();
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results ? $results[0] : null;
    }

    public function find($id)
    {
        return $this->where('id', $id)->first();
    }

    public function count()
    {
        $this->selects = ['COUNT(*) as count'];
        $result = $this->first();
        return $result ? (int)$result['count'] : 0;
    }

    public function exists()
    {
        return $this->count() > 0;
    }

    // INSERT operations
    public function insert($data)
    {
        $this->operation = 'INSERT';
        
        if (isset($data[0])) {
            // Bulk insert
            return $this->insertMultiple($data);
        }
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    protected function insertMultiple($data)
    {
        $columns = array_keys($data[0]);
        $placeholders = array_fill(0, count($columns), '?');
        $placeholderGroups = [];

        foreach ($data as $row) {
            $placeholderGroups[] = '(' . implode(', ', $placeholders) . ')';
            $this->bindings = array_merge($this->bindings, array_values($row));
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES %s",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholderGroups)
        );

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($this->bindings);
    }

    // UPDATE operations
    public function update($data)
    {
        $this->operation = 'UPDATE';
        
        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = ?";
            $this->bindings[] = $value;
        }

        $sql = sprintf(
            "UPDATE %s SET %s",
            $this->table,
            implode(', ', $setParts)
        );

        $sql .= $this->buildWhereClause();
        $sql .= $this->buildOrderClause();
        $sql .= $this->buildLimitClause();

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($this->bindings);
    }

    // DELETE operations
    public function delete()
    {
        $this->operation = 'DELETE';
        
        $sql = sprintf("DELETE FROM %s", $this->table);
        $sql .= $this->buildWhereClause();
        $sql .= $this->buildOrderClause();
        $sql .= $this->buildLimitClause();

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($this->bindings);
    }

    // Query builders
    protected function buildSelectQuery()
    {
        $sql = sprintf(
            "SELECT %s FROM %s",
            implode(', ', $this->selects),
            $this->table
        );

        $sql .= $this->buildJoinClause();
        $sql .= $this->buildWhereClause();
        $sql .= $this->buildOrderClause();
        $sql .= $this->buildLimitClause();

        return $sql;
    }

    protected function buildWhereClause()
    {
        if (empty($this->wheres)) {
            return '';
        }

        $whereParts = [];
        foreach ($this->wheres as $where) {
            $part = $this->buildWherePart($where);
            if ($part) {
                $whereParts[] = $part;
            }
        }

        return ' WHERE ' . implode(' ', $whereParts);
    }

    protected function buildWherePart($where)
    {
        switch ($where['type']) {
            case 'basic':
                return sprintf("%s %s ?", $where['column'], $where['operator']);
            
            case 'in':
                $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                return sprintf("%s IN (%s)", $where['column'], $placeholders);
            
            case 'not_in':
                $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                return sprintf("%s NOT IN (%s)", $where['column'], $placeholders);
            
            case 'null':
                return sprintf("%s IS NULL", $where['column']);
            
            case 'not_null':
                return sprintf("%s IS NOT NULL", $where['column']);
            
            default:
                return '';
        }
    }

    protected function buildJoinClause()
    {
        if (empty($this->joins)) {
            return '';
        }

        $joinParts = [];
        foreach ($this->joins as $join) {
            $type = strtoupper($join['type']);
            $joinParts[] = sprintf(
                "%s JOIN %s ON %s %s %s",
                $type,
                $join['table'],
                $join['first'],
                $join['operator'],
                $join['second']
            );
        }

        return ' ' . implode(' ', $joinParts);
    }

    protected function buildOrderClause()
    {
        if (empty($this->orders)) {
            return '';
        }

        $orderParts = [];
        foreach ($this->orders as $order) {
            $orderParts[] = sprintf("%s %s", $order['column'], $order['direction']);
        }

        return ' ORDER BY ' . implode(', ', $orderParts);
    }

    protected function buildLimitClause()
    {
        $limitParts = [];
        
        if ($this->limit !== null) {
            $limitParts[] = (int)$this->limit;
        }
        
        if ($this->offset !== null) {
            $limitParts[] = (int)$this->offset;
        }

        if (empty($limitParts)) {
            return '';
        }

        return ' LIMIT ' . implode(', ', $limitParts);
    }

    // Utility methods
    public function toSql()
    {
        switch ($this->operation) {
            case 'SELECT':
                return $this->buildSelectQuery();
            case 'INSERT':
                return $this->buildInsertQuery();
            case 'UPDATE':
                return $this->buildUpdateQuery();
            case 'DELETE':
                return $this->buildDeleteQuery();
            default:
                return '';
        }
    }

    public function getBindings()
    {
        return $this->bindings;
    }

    public function reset()
    {
        $this->wheres = [];
        $this->orders = [];
        $this->limit = null;
        $this->offset = null;
        $this->joins = [];
        $this->selects = ['*'];
        $this->bindings = [];
        $this->operation = 'SELECT';
        
        return $this;
    }
}
