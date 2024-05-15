<?php

namespace PHPvian\Libs;

use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

class Connection extends PDO
{
    protected string $table;
    protected string $columns = '*';
    protected string $where = '';
    protected array $params = [];
    protected array $setValues = [];
    protected string $orderBy;
    protected string $limit;
    protected string $sumColumn;

    public function __construct()
    {
        if (connection_file()) {
            $config = config('database');
            $dsn = "{$config['DB_TYPE']}:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_NAME']};charset=UTF8";

            try {
                parent::__construct($dsn, $config['DB_USER'], $config['DB_PASS']);
                $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->exec('SET CHARACTER SET utf8');
            } catch (PDOException $exc) {
                exit('Connection error: ' . $exc->getMessage());
            }
        }
    }

    public function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException | RuntimeException $exc) {
            throw new RuntimeException('Query execution error: ' . $exc->getMessage());
        }
    }

    private function validateData($data)
    {
        foreach ($data as $key => $value) {
            if (!is_numeric($value) && !is_string($value) && !is_bool($value) && !is_null($value)) {
                throw new InvalidArgumentException('Invalid data type for column: ' . $key);
            }
        }
    }

    public function select($columns = '*')
    {
        $this->columns = $columns;
        return $this;
    }

    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    public function where($condition, $params = [])
    {
        $this->where = $condition;
        $this->params = $params;
        return $this;
    }

    public function andWhere($condition, $params = [])
    {
        if (!empty($this->where)) {
            $this->where .= " AND $condition";
        } else {
            $this->where = $condition;
        }
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function order($column)
    {
        $this->orderBy = " $column";
        return $this;
    }

    public function orderBy($column, $order)
    {
        $this->orderBy = " ORDER BY $column $order";
        return $this;
    }

    public function orderByAsc($column)
    {
        $this->orderBy = " ORDER BY $column ASC";
        return $this;
    }

    public function orderByDesc($column)
    {
        $this->orderBy = " ORDER BY $column DESC";
        return $this;
    }

    public function limit($limit, $offset = 0)
    {
        $this->limit = " LIMIT $limit";
        if ($offset > 0) {
            $this->limit .= " OFFSET $offset";
        }
        return $this;
    }

    public function like($column, $value, $wildcard = 'both')
    {
        if ($wildcard === 'both') {
            $value = "%$value%";
        } elseif ($wildcard === 'before') {
            $value = "%$value";
        } elseif ($wildcard === 'after') {
            $value = "$value%";
        } else {
            throw new InvalidArgumentException("The wildcard option must be 'both', 'before' or 'after'.");
        }

        $this->where("$column LIKE :$column");
        $this->params[":$column"] = $value;

        return $this;
    }

    public function sum($column)
    {
        $this->sumColumn = $column;
        return $this;
    }

    public function between($column, $start, $end)
    {
        $this->where("$column BETWEEN :start AND :end");
        $this->params[':start'] = $start;
        $this->params[':end'] = $end;
        return $this;
    }

    public function first()
    {
        $result = $this->get();
        return count($result) > 0 ? $result[0] : null;
    }

    public function get()
    {
        $sql = "SELECT {$this->columns} FROM {$this->table}";
        if (!empty($this->where)) {
            $sql .= " WHERE {$this->where}";
        }
        if (!empty($this->orderBy)) {
            $sql .= $this->orderBy;
        }
        if (!empty($this->limit)) {
            $sql .= $this->limit;
        }
        if (!empty($this->sumColumn)) {
            $sql .= ", SUM({$this->sumColumn}) as total";
        }

        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($this->params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Get error: ' . $e->getMessage());
        }
    }

    public function insert($table, $data = [])
    {
        $this->validateData($data);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        try {
            $stmt = $this->prepare("INSERT INTO $table ($columns) VALUES ($values)");
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Insert error: ' . $e->getMessage());
        }
    }

    public function set($column, $value)
    {
        $this->setValues[$column] = $value;
        return $this;
    }

    public function input(array $data)
    {
        foreach ($data as $column => $value) {
            $this->setValues[$column] = $value;
        }
        return $this;
    }

    public function decrement($column, $value)
    {
        $this->setValues[$column] = "`$column` - $value";
        return $this;
    }

    public function increment($column, $value)
    {
        $this->setValues[$column] = "`$column` + $value";
        return $this;
    }

    public function update()
    {
        $setFields = '';
        foreach ($this->setValues as $column => $value) {
            $setFields .= "`$column` = $value, ";
        }
        $setFields = rtrim($setFields, ', ');

        $sql = "UPDATE `$this->table` SET $setFields";

        if (!empty($this->where)) {
            $sql .= " WHERE $this->where";
        }

        try {
            return $this->executeQuery($sql, $this->params);
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Update error: ' . $e->getMessage());
        }
    }

    public function upgrade($table, $data, $where, $params = [])
    {
        $this->validateData($data);

        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "`$key` = :$key, ";
        }
        $fields = rtrim($fields, ', ');

        $sql = "UPDATE $table SET $fields WHERE $where";

        $mergedParams = array_merge($data, $params);

        try {
            $stmt = $this->prepare($sql);
            foreach ($mergedParams as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Upgrade error: ' . $e->getMessage());
        }
    }

    public function delete($table, $where, $bind = [])
    {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            return $this->executeQuery($sql, $bind);
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Delete error: ' . $e->getMessage());
        }
    }

    public function count($table, $params = [])
    {
        $sql = "SELECT COUNT(*) as total FROM $table";
        if (!empty($this->where)) {
            $sql .= " WHERE {$this->where}";
        }

        try {
            return $this->executeQuery($sql, $params)->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Count error: ' . $e->getMessage());
        }
    }

    public function join($type, $table1, $table2, $onCondition, $columns = '*', $where = '', $params = [])
    {
        $sql = "SELECT $columns FROM $table1 $type $table2 ON $onCondition";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        try {
            $result = $this->executeQuery($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
            return count($result) === 1 ? $result[0] : $result;
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Join error: ' . $e->getMessage());
        }
    }

    public function innerJoin($table1, $table2, $onCondition, $columns = '*', $where = '', $params = [])
    {
        return $this->join('INNER JOIN', $table1, $table2, $onCondition, $columns, $where, $params);
    }

    public function rightJoin($table1, $table2, $onCondition, $columns = '*', $where = '', $params = [])
    {
        return $this->join('RIGHT JOIN', $table1, $table2, $onCondition, $columns, $where, $params);
    }

    public function leftJoin($table1, $table2, $onCondition, $columns = '*', $where = '', $params = [])
    {
        return $this->join('LEFT JOIN', $table1, $table2, $onCondition, $columns, $where, $params);
    }

    public function exists($table, $column, $value)
    {
        try {
            $stmt = $this->prepare("SELECT COUNT(*) as total FROM $table WHERE $column = :value");
            $stmt->bindParam(':value', $value);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] > 0;
        } catch (PDOException $e) {
            throw new RuntimeException('Error checking existence: ' . $e->getMessage());
        }
    }

    public function replace($table, $data = [])
    {
        $this->validateData($data);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        try {
            $stmt = $this->prepare("REPLACE INTO $table ($columns) VALUES ($values)");

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            return $stmt->execute();
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Replace error: ' . $e->getMessage());
        }
    }

    public function testConnection(): string
    {
        try {
            $this->query('SELECT 1');
            return 'Connection successful!';
        } catch (PDOException | RuntimeException $exc) {
            throw new RuntimeException('Error testing connection: ' . $exc->getMessage());
        }
    }

    public function getLastInsertId()
    {
        $stmt = $this->query("SELECT LAST_INSERT_ID()");
        return $stmt->fetchColumn();
    }

    public function closeConnection()
    {
        try {
            return $this->query('KILL CONNECTION_ID()');
        } catch (PDOException | RuntimeException $exc) {
            throw new RuntimeException('Error closing connection: ' . $exc->getMessage());
        }
    }

}
