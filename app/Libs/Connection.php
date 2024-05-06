<?php

namespace PHPvian\Libs;

use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

class Connection extends PDO
{
    public function __construct()
    {
        if (file_exists(dirname(__DIR__) . "/../config/database.php")) {
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
    }

    public function executeQuery($sql, $params = [])
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException | RuntimeException $exc) {
            throw new RuntimeException("Query execution error: " . $exc->getMessage());
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

    public function select($table, $columns = '*', $where = '', array $params = [])
    {
        try {
            $sql = "SELECT $columns FROM $table";
            if (!empty($where)) {
                $sql .= " WHERE $where";
            }

            $stmt = $this->executeQuery($sql, $params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return count($result) === 1 ? $result[0] : $result;
        } catch (PDOException $e) {
            throw $e;
        }
        $this->closeConnection();
    }

    public function insert($table, $data = [])
    {
        try {
            $this->validateData($data);

            $columns = implode(', ', array_keys($data));
            $values = ':' . implode(', :', array_keys($data));

            $stmt = $this->prepare("INSERT INTO $table ($columns) VALUES ($values)");
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
        $this->closeConnection();
    }

    public function update($table, $data, $where, $params = [])
    {
        try {
            $this->validateData($data);

            $fields = '';
            foreach ($data as $key => $value) {
                $fields .= "`$key` = :$key, ";
            }
            $fields = rtrim($fields, ', ');

            $sql = "UPDATE $table SET $fields WHERE $where";

            $mergedParams = array_merge($data, $params);

            $stmt = $this->prepare($sql);
            foreach ($mergedParams as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
        $this->closeConnection();
    }

    public function delete($table, $where, $bind = [])
    {
        try {
            $stmt = $this->prepare("DELETE FROM $table WHERE $where");

            foreach ($bind as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
        $this->closeConnection();
    }

    public function orderBy($table, $columns, $orderColumn, $order, $where = '', $params = []) {
        $sql = "SELECT $columns FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        $sql .= " ORDER BY $orderColumn $order";
        try {
            $stmt = $this->executeQuery($sql, $params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return count($result) === 1 ? $result[0] : $result;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function orderByAsc($table, $columns, $orderColumn, $where = '', $params = []) {
        return $this->orderBy($table, $columns, $orderColumn, 'ASC', $where, $params);
    }

    public function orderByDesc($table, $columns, $orderColumn, $where = '', $params = []) {
        return $this->orderBy($table, $columns, $orderColumn, 'DESC', $where, $params);
    }

    public function selectFirst($table, $columns = '*')
    {
        $stmt = $this->executeQuery("SELECT $columns FROM $table LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function count($table, $condition = '', $params = [])
    {
        $sql = "SELECT COUNT(*) as total FROM $table";
        if (!empty($condition)) {
            $sql .= " WHERE $condition";
        }

        $stmt = $this->executeQuery($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function join($type, $table1, $table2, $onCondition, $columns = '*', $where = '', $params = [])
    {
        $sql = "SELECT $columns FROM $table1 $type $table2 ON $onCondition";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        try {
            $stmt = $this->executeQuery($sql, $params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return count($result) === 1 ? $result[0] : $result;
        } catch (RuntimeException $e) {
            throw $e;
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
        $stmt = $this->prepare("SELECT COUNT(*) as total FROM $table WHERE $column = :value");
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    public function replace($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
        $sql = "REPLACE INTO $table ($columns) VALUES ($values)";
        $statement = $this->prepare($sql);
        $statement->execute($data);
        return $statement->rowCount();
    }

    public function testConnection()
    {
        try {
            $this->query('SELECT 1');
            return "Connection successful!";
        } catch (PDOException $exc) {
            exit("Error testing connection: " . $exc->getMessage());
        }
    }

    public function getLastInsertId()
    {
        $stmt = $this->query("SELECT LAST_INSERT_ID()");
        return $stmt->fetchColumn();
    }

    public function closeConnection(): bool|\PDOStatement
    {
        try {
            return $this->query('KILL CONNECTION_ID()');
        } catch (PDOException $exc) {
            throw new RuntimeException("Error closing connection: " . $exc->getMessage());
        }
    }

}
