<?php

namespace PHPvian\Libs;

use InvalidArgumentException;
use PDO;
use PDOException;

class Database extends PDO
{
    public function __construct()
    {
        $config = config('database');
        $dsn = "{$config['DB_TYPE']}:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_NAME']};charset=UTF8";

        try {
            parent::__construct($dsn, $config['DB_USER'], $config['DB_PASS']);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->exec("SET CHARACTER SET utf8");
        } catch (PDOException $exc) {
            exit("Connection error: " . $exc->getMessage());
        }
    }

    public function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $exc) {
            exit("Query execution error: " . $exc->getMessage());
        }
    }

    private function validateData($data)
    {
        foreach ($data as $key => $value) {
            if (!is_numeric($value) && !is_string($value) && !is_bool($value) && !is_null($value)) {
                throw new InvalidArgumentException("Invalid data type for column $key.");
            }
        }
    }

    public function select($table, $columns = '*', $where = '', $params = [])
    {
        $sql = "SELECT $columns FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($table, $data = [])
    {
        $this->validateData($data);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $stmt = $this->prepare("INSERT INTO $table ($columns) VALUES ($values)");
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    public function update($table, $data, $where)
    {
        $this->validateData($data);

        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ', ');

        $stmt = $this->prepare("UPDATE $table SET $fields WHERE $where");
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    public function delete($table, $where, $bind = [])
    {
        $stmt = $this->prepare("DELETE FROM $table WHERE $where");

        foreach ($bind as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    public function orderBy($table, $columns, $order)
    {
        $columnString = is_array($columns) ? implode(', ', $columns) : $columns;
        $stmt = $this->executeQuery("SELECT * FROM $table ORDER BY $columnString $order");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function orderByDesc($table, $column)
    {
        return $this->orderBy($table, $column, "DESC");
    }

    public function orderByAsc($table, $column)
    {
        return $this->orderBy($table, $column, "ASC");
    }

    public function limit($limit, $offset = 0)
    {
        $limitClause = '';
        if ($limit !== null) {
            $limitClause .= "LIMIT $limit";
            if ($offset !== null) {
                $limitClause .= " OFFSET $offset";
            }
        }
        return $limitClause;
    }

    public function selectFirst($table, $columns = '*')
    {
        $stmt = $this->executeQuery("SELECT $columns FROM $table LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function count($table)
    {
        $stmt = $this->executeQuery("SELECT COUNT(*) as total FROM $table");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function join($type, $table1, $table2, $onCondition)
    {
        $stmt = $this->executeQuery("SELECT * FROM $table1 $type $table2 ON $onCondition");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function innerJoin($table1, $table2, $onCondition)
    {
        return $this->join("INNER JOIN", $table1, $table2, $onCondition);
    }

    public function leftJoin($table1, $table2, $onCondition)
    {
        return $this->join("LEFT JOIN", $table1, $table2, $onCondition);
    }

    public function rightJoin($table1, $table2, $onCondition)
    {
        return $this->join("RIGHT JOIN", $table1, $table2, $onCondition);
    }

    public function exists($table, $column, $value)
    {
        $stmt = $this->prepare("SELECT COUNT(*) as total FROM $table WHERE $column = :value");
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    public function importSQL($filePath)
    {
        // Read the contents of the SQL file
        $sql = file_get_contents($filePath);

        // Execute the contents of the SQL file as a single query
        $this->exec($sql);
    }

}
