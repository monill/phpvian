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
    protected string $order = '';
    protected string $limit = '';
    protected string $sumColumn = '';

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

    public function executeQuery(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException | RuntimeException $exc) {
            throw new RuntimeException('Query execution error: ' . $exc->getMessage());
        }
    }

    private function validateData(array $data): void
    {
        foreach ($data as $key => $value) {
            if (!is_numeric($value) && !is_string($value) && !is_bool($value) && !is_null($value)) {
                throw new InvalidArgumentException('Invalid data type for column: ' . $key);
            }
        }
    }

    public function select(string $columns = '*'): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function from(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function where(string $condition, array $params = []): self
    {
        $this->where = $condition;
        $this->params = $params;
        return $this;
    }

    public function andWhere(string $condition, array $params = []): self
    {
        $this->where .= $this->where ? " AND {$condition}" : $condition;
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function order(string $column): self
    {
        $this->order = " {$column}";
        return $this;
    }

    public function orderBy(string $column, string $order): self
    {
        $this->order = " ORDER BY {$column} {$order}";
        return $this;
    }

    public function orderByAsc(string $column): self
    {
        return $this->orderBy($column, 'ASC');
    }

    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = " LIMIT {$limit}" . ($offset > 0 ? " OFFSET {$offset}" : '');
        return $this;
    }

    public function like(string $column, string $value, string $wildcard = 'both'): self
    {
        $value = match ($wildcard) {
            'both' => "%$value%",
            'before' => "%$value",
            'after' => "$value%",
            default => throw new InvalidArgumentException("The wildcard option must be 'both', 'before' or 'after'."),
        };

        $this->where("$column LIKE :$column");
        $this->params[":$column"] = $value;

        return $this;
    }

    public function sum(string $column): self
    {
        $this->sumColumn = $column;
        return $this;
    }

    public function between(string $column, $start, $end): self
    {
        $this->where("{$column} BETWEEN :start AND :end");
        $this->params[':start'] = $start;
        $this->params[':end'] = $end;
        return $this;
    }

    public function set(string $column, $value, bool $isExpression = false): self
    {
        if ($isExpression) {
            $this->setValues[$column] = $value;
        } else {
            $this->setValues[$column] = ":$column";
            $this->params[":$column"] = $value;
        }
        return $this;
    }

    public function values(array $data): self
    {
        foreach ($data as $column => $value) {
            $this->setValues[$column] = $value;
        }
        return $this;
    }

    public function decrement(string $column, $value): self
    {
        $this->setValues[$column] = "`$column` - $value";
        return $this;
    }

    public function increment(string $column, $value): self
    {
        $this->setValues[$column] = "`$column` + $value";
        return $this;
    }

    public function first(): ?array
    {
        $result = $this->get();
        return $result[0] ?? null;
    }

    public function get(): array
    {
        $sql = "SELECT {$this->columns} FROM {$this->table}";
        if ($this->where) {
            $sql .= " WHERE {$this->where}";
        }
        if ($this->order) {
            $sql .= $this->order;
        }
        if ($this->limit) {
            $sql .= $this->limit;
        }
        if ($this->sumColumn) {
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

    public function fetchAll($sql, $params = []): array
    {
        try {
            $stmt = $this->executeQuery($sql, $params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Fetch All error: ' . $e->getMessage());
        }
    }

    public function insert(string $table, array $data): bool
    {
        $this->validateData($data);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        try {
            $stmt = $this->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$values})");
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Insert error: ' . $e->getMessage());
        }
    }

    public function insertIgnore(string $table, array $data): bool
    {
        $this->validateData($data);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        try {
            $stmt = $this->prepare("INSERT IGNORE INTO {$table} ({$columns}) VALUES ({$values})");
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Insert Ignore error: ' . $e->getMessage());
        }
    }

    public function update()
    {
        $fields = '';
        foreach ($this->setValues as $column => $value) {
            $fields .= "`$column` = $value, ";
        }
        $fields = rtrim($fields, ', ');

        $sql = "UPDATE `{$this->table}` SET {$fields}";
        if ($this->where) {
            $sql .= " WHERE {$this->where}";
        }

        print_r($sql);
        try {
            return $this->executeQuery($sql, $this->params);
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Update error: ' . $e->getMessage());
        }
    }

    public function upgrade(string $table, array $data, string $where, array $params = [])
    {
        $this->validateData($data);

        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "`$key` = :$key, ";
        }
        $fields = rtrim($fields, ', ');

        try {
            $stmt = $this->prepare("UPDATE $table SET $fields WHERE $where");
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Upgrade error: ' . $e->getMessage());
        }
    }

    public function replace(string $table, array $data): bool
    {
        $this->validateData($data);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        try {
            $stmt = $this->prepare("REPLACE INTO {$table} ({$columns}) VALUES ({$values})");
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Replace error: ' . $e->getMessage());
        }
    }

    public function delete(string $table, string $where, array $bind = []): bool
    {
        try {
            $sql = "DELETE FROM {$table} WHERE {$where}";
            return $this->executeQuery($sql, $bind)->rowCount() > 0;
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Delete error: ' . $e->getMessage());
        }
    }

    public function count(string $table, array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        $conditions = [];
        foreach ($params as $column => $value) {
            $conditions[] = "`{$column}` = :{$column}";
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        try {
            $stmt = $this->executeQuery($sql, $params);
            return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Count error: ' . $e->getMessage());
        }
    }

    public function join($type, $table1, $table2, $onCondition, $columns = '*', $where = '', $params = [], $orderBy = '')
    {
        $sql = "SELECT {$columns} FROM {$table1} {$type} {$table2} ON {$onCondition}";
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        if (!empty($orderBy)) {
            $sql .= " ORDER BY {$orderBy}";
        }

        try {
            $result = $this->executeQuery($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
            return count($result) === 1 ? $result[0] : $result;
        } catch (PDOException | RuntimeException $e) {
            throw new RuntimeException('Join error: ' . $e->getMessage());
        }
    }

    public function innerJoin(string $table1, string $table2, string $onCondition, string $columns = '*', string $where = '', array $params = [], $orderBy = ''): array
    {
        return $this->join('INNER JOIN', $table1, $table2, $onCondition, $columns, $where, $params, $orderBy);
    }

    public function rightJoin(string $table1, string $table2, string $onCondition, string $columns = '*', string $where = '', array $params = [], $orderBy = ''): array
    {
        return $this->join('RIGHT JOIN', $table1, $table2, $onCondition, $columns, $where, $params, $orderBy);
    }

    public function leftJoin($table1, $table2, $onCondition, $columns = '*', $where = '', $params = [], $orderBy = '')
    {
        return $this->join('LEFT JOIN', $table1, $table2, $onCondition, $columns, $where, $params, $orderBy);
    }

    public function exists(string $table, string $column, $value): bool
    {
        try {
            $stmt = $this->prepare("SELECT COUNT(*) as total FROM {$table} WHERE {$column} = :value");
            $stmt->bindParam(':value', $value);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'] > 0;
        } catch (PDOException $e) {
            throw new RuntimeException('Error checking existence: ' . $e->getMessage());
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

    public function getLastInsertId(): string
    {
        $stmt = $this->query('SELECT LAST_INSERT_ID()');
        return $stmt->fetchColumn();
    }

    public function closeConnection(): bool
    {
        try {
            return $this->query('KILL CONNECTION_ID()') !== false;
        } catch (PDOException | RuntimeException $exc) {
            throw new RuntimeException('Error closing connection: ' . $exc->getMessage());
        }
    }
}
