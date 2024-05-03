<?php

namespace PHPvian\Libs;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;

class Database extends PDO
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
        } catch (PDOException $exc) {
            throw new \RuntimeException("Query execution error: " . $exc->getMessage());
        }
    }

    private function validateData($data)
    {
        foreach ($data as $key => $value) {
            if (!is_numeric($value) && !is_string($value) && !is_bool($value) && !is_null($value)) {
                throw new \InvalidArgumentException("Invalid data type for column $key.");
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

    public function testConnection()
    {
        try {
            $this->query('SELECT 1');
            return "Connection successful!";
        } catch (PDOException $exc) {
            exit("Error testing connection: " . $exc->getMessage());
        }
    }

    /**
     * Now Start The Game Engine
     */
    public function modifyPoints($aid, $pointsColumn, $amt)
    {
        try {
            $table = 'users';
            $result = $this->selectFirst($table, $pointsColumn, 'id = :id', [':id' => $aid]);
            if (!$result) {
                return false; // Se não houver registro para o ID fornecido
            }
            $newPoints = $result[$pointsColumn] + $amt;
            $data = [$pointsColumn => $newPoints];
            return $this->update($table, $data, 'id = :aid', [':aid' => $aid]);
        } catch (PDOException $e) {
            throw new Exception("Error modifying points: " . $e->getMessage());
        }
    }


    public function modifyPointsAlly($aid, $pointsColumn, $amt)
    {
        if (!$aid) return false;

        try {
            $table = 'alidata';
            $result = $this->selectFirst($table, $pointsColumn, 'id = :id', [':id' => $aid]);
            if (!$result) {
                return false; // Se não houver registro para o ID fornecido
            }
            $newPoints = $result[$pointsColumn] + $amt;
            $data = [$pointsColumn => $newPoints];
            return $this->update($table, $data, 'id = :aid', [':aid' => $aid]);
        } catch (PDOException $e) {
            throw new Exception("Error modifying ally points: " . $e->getMessage());
        }
    }


    public function checkHeroItem($uid, $btype, $type = 0, $proc = 2)
    {
        $whereClause = "1";
        $params = [];

        if ($uid) {
            $whereClause .= " AND uid = :uid";
            $params[':uid'] = $uid;
        }

        if ($btype) {
            $whereClause .= " AND btype = :btype";
            $params[':btype'] = $btype;
        }

        if ($type) {
            $whereClause .= " AND type = :type";
            $params[':type'] = $type;
        }

        if ($proc != 2) {
            $whereClause .= " AND proc = :proc";
            $params[':proc'] = $proc;
        }

        try {
            $result = $this->selectFirst('heroitems', 'id, btype', $whereClause, $params);
            return $result ? $result['id'] : false;
        } catch (PDOException $e) {
            throw new Exception("Error checking hero item: " . $e->getMessage());
        }
    }

    public function modifyHeroItem($id, $column, $value, $mode)
    {
        // mode=0 set; 1 add; 2 sub; 3 mul; 4 div
        switch ($mode) {
            case 0:
                $data = [$column => $value];
                return $this->update('heroitems', $data, 'id = :id', [':id' => $id]);
            case 1:
                return $this->update('heroitems', [$column => "$column + :value"], 'id = :id', [':id' => $id, ':value' => $value]);
            case 2:
                return $this->update('heroitems', [$column => "$column - :value"], 'id = :id', [':id' => $id, ':value' => $value]);
            case 3:
                return $this->update('heroitems', [$column => "$column * :value"], 'id = :id', [':id' => $id, ':value' => $value]);
            case 4:
                return $this->update('heroitems', [$column => "$column / :value"], 'id = :id', [':id' => $id, ':value' => $value]);
            default:
                throw new InvalidArgumentException("Invalid mode value. Mode must be between 0 and 4.");
        }
    }

    public function addHeroItem($uid, $btype, $type, $num)
    {
        try {
            $data = [
                'uid' => $uid,
                'btype' => $btype,
                'type' => $type,
                'num' => $num,
                'proc' => 0
            ];
            return $this->insert('heroitems', $data);
        } catch (PDOException $e) {
            throw new Exception("Error adding hero item: " . $e->getMessage());
        }
    }

    public function setSilver($uid, $silver, $mode)
    {
        if (!$mode) {
            $data = ['silver' => "silver - :silver"];
            $result1 = $this->update('users', $data, 'id = :uid', [':uid' => $uid, ':silver' => $silver]);
            $data = ['usedsilver' => "usedsilver + :silver"];
            $result2 = $this->update('users', $data, 'id = :uid', [':uid' => $uid, ':silver' => $silver]);
        } else {
            $data = ['silver' => "silver + :silver"];
            $result1 = $this->update('users', $data, 'id = :uid', [':uid' => $uid, ':silver' => $silver]);
            $data = ['addsilver' => "addsilver + :silver"];
            $result2 = $this->update('users', $data, 'id = :uid', [':uid' => $uid, ':silver' => $silver]);
        }
        return $result1 && $result2;
    }


}
