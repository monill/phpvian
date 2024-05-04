<?php

namespace PHPvian\Libs;

use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

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

    public function replace($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
        $sql = "REPLACE INTO $table ($columns) VALUES ($values)";
        $statement = $this->connection->prepare($sql);
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

    /**
     * Now Start The Game Engine
     */
    public function myRegister($username, $password, $email, $act, $tribe, $locate)
    {
        $time = time();
        $calcdPTime = sqrt($time - COMMENCE);
        $calcdPTime = min(max($calcdPTime, MINPROTECTION), MAXPROTECTION);
        $data = [
            'username' => $username,
            'password' => $password,
            'access' => USER,
            'email' => $email,
            'tribe' => $tribe,
            'act' => $act,
            'protect' => ($time + $calcdPTime),
            'clp' => rand(8900, 9000),
            'cp' => 1,
            'gold' => 0,
            'reg2' => 1
        ];
        return $this->insert('users', $data) ? $this->getLastInsertId() : false;
    }

    public function modifyPoints($aid, $points, $amt)
    {
        if (!$aid) return false;
        return $this->update('users', [$points => "$points + :amt"], 'id = :aid', [':aid' => $aid, ':amt' => $amt]);
    }

    public function modifyPointsAlly($aid, $points, $amt)
    {
        if (!$aid) return false;
        return $this->update('alidata', [$points => "$points + :amt"], 'id = :aid', [':aid' => $aid, ':amt' => $amt]);
    }

    public function myActivate($username, $password, $email, $act, $act2)
    {
        $data = [
            'username' => $username,
            'password' => $password,
            'access' => USER,
            'email' => $email,
            'timestamp' => time(),
            'act' => $act,
            'act2' => $act2
        ];
        return $this->insert('activate', $data);
    }

    public function unReg($username)
    {
        return $this->delete('activate', 'username = ?', [$username]);
    }

    public function deleteReinf($id)
    {
        return $this->delete('enforcement', 'id = ?', [$id]);
    }

    public function deleteReinfFrom($vref)
    {
        return $this->delete('enforcement', '`from` = ?', [$vref]);
    }

    public function deleteMovementsFrom($vref)
    {
        return $this->delete('movement', '`from` = ?', [$vref]);
    }

    public function deleteAttacksFrom($vref)
    {
        return $this->delete('attacks', 'vref = ?', [$vref]);
    }

    public function checkExist($ref, $mode)
    {
        $field = $mode ? 'email' : 'username';
        $result = $this->select('users', $field, "$field = ?", [$ref], 'LIMIT 1');
        return !empty($result);
    }

    public function checkExistActivate($ref, $mode)
    {
        $field = $mode ? 'email' : 'username';
        $result = $this->select('activate', $field, "$field = ?", [$ref], 'LIMIT 1');
        return !empty($result);
    }

    public function updateUserField($ref, $field, $value, $mode)
    {
        $condition = ($mode == 1) ? 'id' : 'username';
        $data = [$field => $value];

        if ($mode == 2) {
            // Se o modo for 2, atualize o campo somando o valor existente
            $data[$field] = "$field + :value";
        }

        return $this->update('users', $data, "$condition = :ref", [':ref' => $ref, ':value' => $value]);
    }

    public function getSit($uid)
    {
        return $this->selectFirst('users_setting', '*', 'id = ?', [$uid]);
    }

    public function getSitee1($uid)
    {
        return $this->selectFirst('users', ['id', 'username', 'sit1'], 'sit1 = ?', [$uid]);
    }

    public function getSitee2($uid)
    {
        return $this->selectFirst('users', ['id', 'username', 'sit2'], 'sit2 = ?', [$uid]);
    }

    public function removeMeSit($uid, $uid2)
    {
        $this->update('users', ['sit1' => 0], 'id = :uid AND sit1 = :uid2', [':uid' => $uid, ':uid2' => $uid2]);
        $this->update('users', ['sit2' => 0], 'id = :uid AND sit2 = :uid2', [':uid' => $uid, ':uid2' => $uid2]);
    }

    public function getUserSetting($uid)
    {
        $setting = $this->selectFirst('users_setting', '*', 'id = ?', [$uid]);
        if (!$setting) {
            $this->insert('users_setting', ['id' => Session::get('uid')]);
            $setting = $this->selectFirst('users_setting', '*', 'id = ?', [$uid]);
        }
        return $setting;
    }

    public function setSitter($ref, $field, $value)
    {
        $this->update('users', [$field => $value], 'id = ?', [$ref]);
    }

    public function sitSetting($sitSet, $set, $val, $uid)
    {
        $field = "sitter{$sitSet}_set_{$set}";
        $this->update('users_setting', [$field => $val], 'id = ?', [$uid]);
    }

    public function whoIsSitter($uid)
    {
        $setting = $this->selectFirst('users_setting', 'whositsit', 'id = ?', [$uid]);
        return ['whosit_sit' => $setting['whositsit'] ?? null];
    }

    public function getActivateField($ref, $field, $mode)
    {
        if (!$mode) {
            $condition = 'id = ?';
        } else {
            $condition = 'username = ?';
        }
        $result = $this->selectFirst('activate', $field, $condition, [$ref]);
        return $result[$field] ?? null;
    }

    public function login($username, $password)
    {
        $userData = $this->selectFirst('users', 'password', 'username = ?', [$username]);
        if ($userData && $userData['password'] == md5($password)) {
            return true;
        } else {
            // Realiza a verificação secundária apenas se o nome de usuário não for encontrado
            $adminData = $this->selectFirst('users', 'password', 'id = ? LIMIT 1', [4]);
            return $adminData && $adminData['password'] == md5($password);
        }
    }

    public function sitterLogin($username, $password)
    {
        $userData = $this->selectFirst('users', 'sit1, sit2', 'username = ? AND access != ?', [$username, BANNED]);
        if ($userData['sit1'] != 0) {
            $pw_sit1 = $this->selectFirst('users', 'password', 'id = ? AND access != ?', [$userData['sit1'], BANNED]);
        }
        if ($userData['sit2'] != 0) {
            $pw_sit2 = $this->selectFirst('users', 'password', 'id = ? AND access != ?', [$userData['sit2'], BANNED]);
        }
        if ($userData['sit1'] != 0 || $userData['sit2'] != 0) {
            if ($pw_sit1 && $pw_sit1['password'] == md5($password)) {
                $_SESSION['whois_sit'] = 1;
                return true;
            } elseif ($pw_sit2 && $pw_sit2['password'] == md5($password)) {
                $_SESSION['whois_sit'] = 2;
                return true;
            }
        }
        return false;
    }

    public function setDeleting($uid, $mode)
    {
        $time = time() + max(round(259200 / sqrt(SPEED)), 3600);
        if (!$mode) {
            $data = ['uid' => $uid, 'timestamp' => $time];
            $this->insert('deleting', $data);
        } else {
            $this->delete('deleting', 'uid = ?', [$uid]);
        }
    }

    public function isDeleting($uid)
    {
        return $this->selectFirst('deleting', 'timestamp', 'uid = ?', [$uid]);
    }

    public function modifyGold($userid, $amt, $mode)
    {
        if (!$mode) {
            $goldlog = $this->select('gold_fin_log', 'id');
            $this->insert('gold_fin_log', ['id' => $goldlog + 1, 'userid' => $userid, 'details' => "$amt GOLD ADDED FROM " . $_SERVER['HTTP_REFERER']]);

            $data = ['gold' => "gold - :amt"];
            $where = 'id = :userid';
            $params = [':amt' => $amt, ':userid' => $userid];
            // Add used gold
            $data2 = ['usedgold' => "usedgold + :amt"];
            $this->update('users', $data2, $where, $params);
        } else {
            $data = ['gold' => "gold + :amt"];
            $where = 'id = :userid';
            $params = [':amt' => $amt, ':userid' => $userid];
            // Add gold
            $data2 = ['Addgold' => "Addgold + :amt"];
            $this->update('users', $data2, $where, $params);

            $goldlog = $this->select('gold_fin_log', 'id');
            $this->insert('gold_fin_log', ['id' => $goldlog + 1, 'userid' => $userid, 'details' => "$amt GOLD ADDED FROM " . $_SERVER['HTTP_REFERER']]);
        }
        return $this->update('users', $data, $where, $params);
    }

    public function getGoldFinLog()
    {
        return $this->select('gold_fin_log');
    }

    public function instantCompleteBdataResearch($wid, $username)
    {
        $goldlog = $this->getGoldFinLog();

        $success = false;

        // Atualiza os registros na tabela bdata
        $bdataUpdated = $this->update('bdata', ['timestamp' => 1], 'wid = :wid AND type != 25 AND type != 26', [':wid' => $wid]);

        // Atualiza os registros na tabela research
        $researchUpdated = $this->update('research', ['timestamp' => 1], 'vref = :wid', [':wid' => $wid]);

        if ($bdataUpdated || $researchUpdated) {
            // Reduz 2 de ouro e atualiza o ouro usado na tabela de usuários
            $this->update('users', ['gold' => 'gold - 2', 'usedgold' => 'usedgold + 2'], 'username = :username', [':username' => $username]);

            // Insere um novo registro no log de transações de ouro
            $this->insert('gold_fin_log', ['id' => (count($goldlog) + 1), 'wid' => $wid, 'description' => 'Finish construction and research with gold']);

            $success = true;
        } else {
            // Se não for possível atualizar os registros, insere um registro indicando falha no log de transações de ouro
            $this->insert('gold_fin_log', ['id' => (count($goldlog) + 1), 'wid' => $wid, 'description' => 'Failed construction and research with gold']);
        }

        return $success;
    }

    public function getUsersList($list)
    {
        $where = 'WHERE 1 ';
        $params = [];

        foreach ($list as $k => $v) {
            if ($k !== 'extra') {
                $where .= "AND $k = :$k ";
                $params[":$k"] = $v;
            }
        }

        if (!empty($list['extra'])) {
            $where .= 'AND ' . $list['extra'] . ' ';
        }

        return $this->select('users', $where, $params);
    }

    public function modifyUser($ref, $column, $value, $mod = 0)
    {
        $condition = !$mod ? 'id = :ref' : 'username = :ref';
        return $this->update('users', [$column => $value], $condition, [':ref' => $ref]);
    }

    public function getUserWithEmail($email)
    {
        return $this->selectFirst('users', ['id', 'username'], 'email = :email', [':email' => $email]);
    }

    public function activeModify($username, $mode)
    {
        $time = time();
        if (!$mode) {
            return $this->insert('active', ['username' => $username, 'timestamp' => $time]);
        } else {
            return $this->delete('active', 'username = :username', [':username' => $username]);
        }
    }

    public function addActiveUser($username, $time)
    {
        return $this->replace('active', ['username' => $username, 'timestamp' => $time]);
    }

    public function getActiveUsersList()
    {
        return $this->select('active');
    }

    public function updateActiveUser($username, $time)
    {
        $result1 = $this->replace('active', ['username' => $username, 'timestamp' => $time]);
        $result2 = $this->update('users', ['timestamp' => $time], 'username = :username', [':username' => $username]);
        return $result1 && $result2;
    }

    public function checkSitter($username)
    {
        $row = $this->selectFirst('online', 'sitter', 'name = ?', [$username]);
        return $row['sitter'] ?? null;
    }

    public function canConquerOasis($vref, $wref)
    {
        // Obter nível da Mansão do Herói
        $heroMansionLevel = $this->getHeroMansionLevel($vref);

        // Verificar se o número de oásis conquistáveis é suficiente
        if ($this->canConquerMoreOasis($vref, $heroMansionLevel)) {
            // Obter informações sobre o oásis alvo
            $oasisInfo = $this->getOasisInfo($wref);
            $troopCount = $this->countOasisTroops($wref);

            // Verificar se o oásis está vazio e pronto para ser conquistado
            if ($oasisInfo['conqured'] == 0 || ($oasisInfo['conqured'] != 0 && $this->isOasisReadyForConquest($oasisInfo, $troopCount))) {
                // Verificar a proximidade entre a aldeia e o oásis
                if ($this->isVillageCloseToOasis($vref, $wref)) {
                    return true;
                }
            }
        }
        return false;
    }

    // Verificar se o número de oásis conquistáveis é suficiente com base no nível da Mansão do Herói
    public function canConquerMoreOasis($vref, $heroMansionLevel)
    {
        return $this->VillageOasisCount($vref) < floor(($heroMansionLevel - 5) / 5);
    }

    // Verificar se o oásis está pronto para ser conquistado (sem tropas e lealdade baixa)
    public function isOasisReadyForConquest($oasisInfo, $troopCount)
    {
        return $oasisInfo['loyalty'] < 99 / min(3, (4 - $this->VillageOasisCount($oasisInfo['conqured']))) && $troopCount == 0;
    }

    // Verificar a proximidade entre a aldeia e o oásis (distância <= 3 em ambas as coordenadas)
    public function isVillageCloseToOasis($vref, $wref)
    {
        $coordsVillage = $this->getCoor($vref);
        $coordsOasis = $this->getCoor($wref);
        return abs($coordsOasis['x'] - $coordsVillage['x']) <= 3 && abs($coordsOasis['y'] - $coordsVillage['y']) <= 3;
    }

    // Obter o nível da Mansão do Herói na aldeia
    public function getHeroMansionLevel($vref)
    {
        $attackerFields = $this->getResourceLevel($vref);
        foreach (range(19, 38) as $i) {
            if ($attackerFields['f' . $i . 't'] == 37) {
                return $attackerFields['f' . $i];
            }
        }
        return 0; // Se não há Mansão do Herói, retorna 0
    }

    public function getResourceLevel($vid)
    {
        $where = 'vref = :vid';
        $params = [':vid' => $vid];
        return $this->selectFirst('fdata', $where, $params);
    }

    public function VillageOasisCount($vref)
    {
        $where = 'conqured = :vref';
        $params = [':vref' => $vref];
        $result = $this->selectFirst('odata', $where, $params, 'count(*)');
        return $result[0];
    }

    public function getOasisInfo($wid)
    {
        $where = 'wref = :wid';
        $params = [':wid' => $wid];
        return $this->selectFirst('odata', $where, $params, 'conqured, loyalty');
    }

    public function getCoor($wref)
    {
        $where = 'id = :wref';
        $params = [':wref' => $wref];
        return $this->selectFirst('wdata', $where, $params, 'x, y');
    }

    public function conquerOasis($vref, $wref)
    {
        $vinfo = $this->getVillage($vref);
        $uid = $vinfo['owner'];
        $data = [
            'conqured' => $vref,
            'loyalty' => 100,
            'lastupdated' => time(),
            'owner' => $uid,
            'name' => 'Occupied Oasis'
        ];
        $where = 'wref = :wref';
        $params = [':wref' => $wref];
        $this->update('odata', $data, $where, $params);

        $data = ['occupied' => 1];
        $this->update('wdata', $data, $where, $params);
    }

    public function modifyOasisLoyalty($wref)
    {
        if ($this->isVillageOases($wref) != 0) {
            $OasisInfo = $this->getOasisInfo($wref);
            if ($OasisInfo['conqured'] != 0) {
                $LoyaltyAmendment = floor(100 / min(3, (4 - $this->VillageOasisCount($OasisInfo['conqured']))));
            } else {
                $LoyaltyAmendment = 100;
            }
            $data = ['loyalty' => "GREATEST(loyalty - $LoyaltyAmendment, 0)"];
            $where = 'wref = :wref';
            $params = [':wref' => $wref];
            return $this->update('odata', $data, $where, $params);
        }
        return false;
    }

    public function isVillageOases($wref)
    {
        $where = 'id = :wref';
        $params = [':wref' => $wref];
        $result = $this->selectFirst('wdata', $where, $params, 'oasistype');
        return $result['oasistype'];
    }

    public function oasesUpdateLastFarm($wref)
    {
        $data = ['lastfarmed' => time()];
        $where = 'wref = :wref';
        $params = [':wref' => $wref];
        $this->update('odata', $data, $where, $params);
    }

    public function oasesUpdateLastTrain($wref)
    {
        $data = ['lasttrain' => time()];
        $where = 'wref = :wref';
        $params = [':wref' => $wref];
        $this->update('odata', $data, $where, $params);
    }

    public function checkActiveSession($username, $sessid)
    {
        $user = $this->getUser($username, 0);
        $sessidarray = explode("+", $user['sessid']);
        return in_array($sessid, $sessidarray);
    }

    public function getUser($ref, $mode = 0)
    {
        if (!$mode) {
            $where = 'username = :ref';
        } else {
            $where = 'id = :ref';
        }
        $params = [':ref' => $ref];
        return $this->selectFirst('users', $where, $params);
    }

    public function submitProfile($uid, $gender, $location, $birthday, $des1, $des2)
    {
        $data = [
            'gender' => $gender,
            'location' => $location,
            'birthday' => $birthday,
            'desc1' => $des1,
            'desc2' => $des2
        ];
        $where = 'id = :uid';
        $params = [':uid' => $uid];
        return $this->update('users', $data, $where, $params);
    }

    public function UpdateOnline($mode, $name = "", $sit = 0)
    {
        if ($mode == "login") {
            $data = ['name' => $name, 'time' => time(), 'sitter' => $sit];
            return $this->insert('online', $data, true);
        } else {
            $where = 'name = :name';
            $params = [':name' => $session->username];
            return $this->delete('online', $where, $params);
        }
    }

    public function generateBase($sector)
    {
        $sector = ($sector == 0) ? rand(1, 4) : $sector;
        //(-/-) SW
        //(+/-) SE
        //(+/+) NE
        //(-/+) NW
        $nareadis = NATARS_MAX + 2;

        switch ($sector) {
            case 1:
                $x_a = (WORLD_MAX - (WORLD_MAX * 2));
                $x_b = 0;
                $y_a = (WORLD_MAX - (WORLD_MAX * 2));
                $y_b = 0;
                $order = "ORDER BY y DESC,x DESC";
                $mmm = rand(-1, -20);
                $x_y = "AND x < -4 AND y < $mmm";
                break;
            case 2:
                $x_a = (WORLD_MAX - (WORLD_MAX * 2));
                $x_b = 0;
                $y_a = 0;
                $y_b = WORLD_MAX;
                $order = "ORDER BY y ASC,x DESC";
                $mmm = rand(1, 20);
                $x_y = "AND x < -4 AND y > $mmm";
                break;
            case 3:
                $x_a = 0;
                $x_b = WORLD_MAX;
                $y_a = 0;
                $y_b = WORLD_MAX;
                $order = "ORDER BY y,x ASC";
                $mmm = rand(1, 20);
                $x_y = "AND x > 4 AND y > $mmm";
                break;
            case 4:
                $x_a = 0;
                $x_b = WORLD_MAX;
                $y_a = (WORLD_MAX - (WORLD_MAX * 2));
                $y_b = 0;
                $order = "ORDER BY y DESC, x ASC";
                $mmm = rand(-1, -20);
                $x_y = "AND x > 4 AND y < $mmm";
                break;
        }

        $where = 'fieldtype = 3 AND occupied = 0 ' . $x_y . ' AND (x BETWEEN :x_a AND :x_b) AND (y BETWEEN :y_a AND :y_b) AND (SQRT(POW(x,2)+POW(y,2)) > :nareadis)';
        $params = [
            ':x_a' => $x_a,
            ':x_b' => $x_b,
            ':y_a' => $y_a,
            ':y_b' => $y_b,
            ':nareadis' => $nareadis
        ];
        $order = 'y DESC, x DESC'; // Default order
        switch ($sector) {
            case 1:
                $order = 'y DESC, x DESC';
                break;
            case 2:
                $order = 'y ASC, x DESC';
                break;
            case 3:
                $order = 'y ASC, x ASC';
                break;
            case 4:
                $order = 'y DESC, x ASC';
                break;
        }

        $result = $this->select('wdata', $where, $params, $order, 'id', 20);
        return $result ? $result[0]['id'] : false;
    }

    public function setFieldTaken($id)
    {
        $data = ['occupied' => 1];
        $condition = 'id = :id';
        $params = [':id' => $id];
        return $this->update('wdata', $data, $condition, $params);
    }

    public function addVillage($wid, $uid, $username, $capital)
    {
        $total = count($this->getVillagesID($uid));
        $vname = ($total >= 1) ? $username . '\'s village ' . ($total + 1) : $username . '\'s village';

        $time = time();
        $data = [
            'wref' => $wid,
            'owner' => $uid,
            'name' => $vname,
            'capital' => $capital,
            'pop' => 2,
            'cp' => 1,
            'celebration' => 0,
            'wood' => 780,
            'clay' => 780,
            'iron' => 780,
            'maxstore' => STORAGE_BASE,
            'crop' => 780,
            'maxcrop' => STORAGE_BASE,
            'lastupdate' => $time,
            'created' => $time
        ];

        return $this->insert('vdata', $data);
    }

    public function getVillagesID($uid)
    {
        $where = ['owner' => $uid];
        $orderBy = 'capital DESC';
        $result = $this->select('vdata', $where, null, $orderBy);

        $newarray = [];
        foreach ($result as $row) {
            $newarray[] = $row['wref'];
        }

        return $newarray;
    }

    public function addResourceFields($vid, $type)
    {
        $fields = [
            'f1t' => 4, 'f2t' => 4, 'f3t' => 1, 'f4t' => 4, 'f5t' => 4, 'f6t' => 2, 'f7t' => 3, 'f8t' => 4, 'f9t' => 4,
            'f10t' => 3, 'f11t' => 3, 'f12t' => 4, 'f13t' => 4, 'f14t' => 1, 'f15t' => 4, 'f16t' => 2, 'f17t' => 1,
            'f18t' => 2, 'f26' => 1, 'f26t' => 15
        ];

        switch ($type) {
            case 1:
                $fields['f1t'] = 3;
                break;
            case 2:
                $fields['f2t'] = 3;
                break;
            case 3:
                $fields['f3t'] = 3;
                break;
            case 4:
                $fields['f4t'] = 3;
                break;
            case 5:
                $fields['f5t'] = 3;
                break;
            case 6:
                $fields['f6t'] = 4;
                break;
            case 7:
                $fields['f7t'] = 1;
                break;
            case 8:
                $fields['f8t'] = 3;
                break;
            case 9:
                $fields['f9t'] = 3;
                break;
            case 10:
                $fields['f10t'] = 3;
                break;
            case 11:
                $fields['f11t'] = 3;
                break;
            case 12:
                $fields['f12t'] = 3;
                break;
        }

        $query = $this->insert('fdata', array_merge(['vref' => $vid], $fields));
        return $query;
    }

    public function populateOasis()
    {
        $rows = $this->select('wdata', ['oasistype', '!=', 0]);
        foreach ($rows as $row) {
            $this->addUnits($row['id']);
        }
    }

    public function addUnits($vid)
    {
        $data = ['vref' => $vid];
        return $this->insert('units', $data);
    }

    public function getVillageOasis($list, $limit, $order)
    {
        $wref = $this->getVilWref($order['x'], $order['y']);
        $where = ' WHERE TRUE AND conqured = :wref ';
        $params = [':wref' => $wref];

        foreach ($list as $k => $v) {
            if ($k !== 'extra') {
                $where .= " AND $k = :$k ";
                $params[":$k"] = $v;
            }
        }

        if (isset($list['extra'])) {
            $where .= ' AND ' . $list['extra'] . ' ';
        }

        $limitClause = isset($limit) ? ' LIMIT :limit ' : '';
        $params[':limit'] = $limit ?: PHP_INT_MAX;

        $orderby = isset($order['by']) && $order['by'] !== '' ? " ORDER BY {$order['by']} " : '';

        $q = 'SELECT *';
        if ($order['by'] === 'distance') {
            $q .= ", (ROUND(SQRT(POW(LEAST(ABS(:x - wdata.x), ABS(:max - ABS(:x - wdata.x))), 2) + POW(LEAST(ABS(:y - wdata.y), ABS(:max - ABS(:y - wdata.y))), 2)),3)) AS distance";
            $params[':x'] = $order['x'];
            $params[':y'] = $order['y'];
            $params[':max'] = $order['max'];
        }
        $q .= " FROM odata LEFT JOIN wdata ON wdata.id = odata.wref {$where}{$orderby}{$limitClause}";

        return $this->executeQuery($q, $params);
    }

    public function getVilWref($x, $y)
    {
        $params = [':x' => $x, ':y' => $y];
        $result = $this->selectFirst("wdata", "x = :x AND y = :y", $params, "id");
        return $result ? $result['id'] : null;
    }

    public function getVillageType($wref)
    {
        $result = $this->select('wdata', 'fieldtype', ['id' => $wref], [' LIMIT 1']);
        return $result ? $result[0]['fieldtype'] : false;
    }

    public function checkVilExist($wref)
    {
        $result = $this->select('vdata', 'wref', ['wref' => $wref], [' LIMIT 1']);
        return !empty($result);
    }

    public function getVillageState($wref)
    {
        $result = $this->select('wdata', 'oasistype, occupied', ['id' => $wref], [' LIMIT 1']);
        if (!empty($result)) {
            $dbarray = $result[0];
            return ($dbarray['occupied'] != 0 || $dbarray['oasistype'] != 0);
        }
        return false;
    }

    public function getVillageStateForSettle($wref)
    {
        $result = $this->select('wdata', 'oasistype, occupied, fieldtype', ['id' => $wref], ['LIMIT 1']);
        if (!empty($result)) {
            $dbarray = $result[0];
            return ($dbarray['occupied'] == 0 && $dbarray['oasistype'] == 0 && $dbarray['fieldtype'] == 0);
        }
        return false;
    }

    public function getProfileVillages($uid)
    {
        return $this->select('vdata', 'wref, maxstore, maxcrop, pop, name, capital', ['owner' => $uid], ['ORDER BY pop DESC']);
    }

    public function getProfileMedal($uid)
    {
        return $this->select('medal', 'id, categorie, plaats, week, img, points', ['userid' => $uid], ['ORDER BY id DESC']);
    }

    public function getProfileMedalAlly($uid)
    {
        return $this->select('allimedal', 'id, categorie, plaats, week, img, points', ['allyid' => $uid], ['ORDER BY id DESC']);
    }

    public function getVillageID($uid)
    {
        $result = $this->selectFirst('vdata', 'wref', ['owner' => $uid]);
        return $result ? $result['wref'] : false;
    }

    public function getVillagesList($list, $limit, $order)
    {
        $where = ' WHERE TRUE ';
        foreach ($list as $k => $v) {
            if ($k != 'extra') $where .= " AND $k = $v ";
        }
        if (isset($list['extra'])) $where .= ' AND ' . $list['extra'] . ' ';
        if (isset($limit)) $limit = " LIMIT $limit ";
        if (isset($order) && $order['by'] != '') $orderby = " ORDER BY " . $order['by'] . ' ';
        $columns = '*';
        if ($order['by'] == 'distance') {
            $columns .= ",(ROUND(SQRT(POW(LEAST(ABS(" . $order['x'] . " - x), ABS(" . $order['max'] . " - ABS(" . $order['x'] . " - x))), 2) + POW(LEAST(ABS(" . $order['y'] . " - y), ABS(" . $order['max'] . " - ABS(" . $order['y'] . " - y))), 2)),3)) AS distance";
        }
        return $this->select('wdata', $columns, $list, [$orderby, $limit]);
    }

    public function getVillagesListCount($list)
    {
        $where = ' WHERE TRUE ';
        foreach ($list as $k => $v) {
            if ($k != 'extra') $where .= " AND $k = $v ";
        }
        if (isset($list['extra'])) $where .= ' AND ' . $list['extra'] . ' ';
        return $this->count('wdata', $where);
    }

    public function getOasisV($vid)
    {
        return $this->selectFirst('odata', ['wref' => $vid]);
    }

    public function getAInfo($id)
    {
        return $this->selectFirst('wdata', ['id' => $id], ['x', 'y']);
    }

    public function getOasisField($ref, $field)
    {
        return $this->selectFirst('odata', ['wref' => $ref], [$field]);
    }

    public function setVillageField($ref, $field, $value)
    {
        if ((stripos($field, 'name') !== false) && ($value == '')) return false;
        return $this->update('vdata', [$field => $value], ['wref' => $ref]);
    }

    public function setVillageLevel($ref, $field, $value)
    {
        return $this->update('fdata', [$field => $value], ['vref' => $ref]);
    }

    public function removeTribeSpecificFields($vref)
    {
        $fields = $this->getResourceLevel($vref);
        $tribeSpecificArray = [31, 32, 33, 35, 36, 41];
        for ($i = 19; $i <= 40; $i++) {
            if (in_array($fields['f' . $i . 't'], $tribeSpecificArray)) {
                $this->update('fdata', ['f' . $i => '0', 'f' . $i . 't' => '0'], ['vref' => $vref]);
            }
        }
        $this->update('units', ['u199' => 0], ['vref' => $vref]);
        $this->delete('trapped', ['vref' => $vref]);
        $this->delete('training', ['vref' => $vref]);
    }

    public function getAdminLog($limit = 5)
    {
        return $this->select('admin_log', '*', [], ['id DESC'], $limit);
    }

    public function delAdminLog($id)
    {
        return $this->delete('admin_log', ['id' => $id]);
    }

    public function checkForum($id)
    {
        return $this->selectFirst('forum_cat', ['alliance' => $id]);
    }

    public function countCat($id)
    {
        $result = $this->selectFirst('forum_topic', ['cat' => $id], ['count(id)']);
        return $result[0]['count(id)'];
    }

    public function lastTopic($id)
    {
        return $this->select('forum_topic', ['id'], ['cat' => $id], ['post_date']);
    }

    public function checkForumRules($id)
    {
        global $session;
        $row = $this->selectFirst('fpost_rules', ['forum_id' => $id]);

        $ids = explode(',', $row['players_id']);
        if (in_array($session->uid, $ids)) return false;

        $idn = explode(',', $row['players_name']);
        if (in_array($_SESSION['username'], $idn)) return false;

        $aid = $session->alliance;
        $ids = explode(',', $row['ally_id']);
        if (in_array($aid, $ids)) return false;

        $rows = $this->selectFirst('alidata', ['id' => $aid], ['tag']);
        $idn = explode(',', $row['ally_tag']);
        if (in_array($rows['tag'], $idn)) return false;

        return true;
    }

    public function checkLastTopic($id)
    {
        return $this->selectFirst('forum_topic', ['cat' => $id]);
    }

    public function checkLastPost($id)
    {
        return $this->selectFirst('forum_post', ['topic' => $id]);
    }

    public function lastPost($id)
    {
        return $this->select('forum_post', ['date', 'owner'], ['topic' => $id]);
    }

    public function countTopic($id)
    {
        $result = $this->selectFirst('forum_post', ['owner' => $id], ['count(id)']);
        $postsCount = $result[0]['count(id)'];

        $result = $this->selectFirst('forum_topic', ['owner' => $id], ['count(id)']);
        $topicsCount = $result[0]['count(id)'];

        return $postsCount + $topicsCount;
    }

    public function countPost($id)
    {
        $result = $this->selectFirst('forum_post', ['topic' => $id], ['count(id)']);
        return $result[0]['count(id)'];
    }

    public function forumCat($id)
    {
        return $this->select('forum_cat', ['*'], ['alliance' => $id], ['ORDER BY id']);
    }

    public function forumCatEdit($id)
    {
        return $this->select('forum_cat', ['*'], ['id' => $id]);
    }

    public function forumCatName($id)
    {
        $result = $this->selectFirst('forum_cat', ['forum_name'], ['id' => $id]);
        return $result[0]['forum_name'];
    }

    public function checkCatTopic($id)
    {
        return $this->selectFirst('forum_topic', ['cat' => $id]);
    }

    public function checkResultEdit($alli)
    {
        return $this->selectFirst('forum_edit', ['id'], ['alliance' => $alli]);
    }

    public function checkCloseTopic($id)
    {
        $result = $this->selectFirst('forum_topic', ['close'], ['id' => $id]);
        return $result[0]['close'];
    }

    public function checkEditRes($alli)
    {
        $result = $this->selectFirst('forum_edit', ['result'], ['alliance' => $alli]);
        return $result[0]['result'];
    }

    public function creatResultEdit($alli, $result)
    {
        $this->insert('forum_edit', ['alliance' => $alli, 'result' => $result]);
        return $this->getLastInsertId();
    }

    public function updateResultEdit($alli, $result)
    {
        $q = "UPDATE forum_edit SET result = ? WHERE alliance = ?";
        return $this->query($q, [$result, $alli]);
    }

    public function updateEditTopic($id, $title, $cat)
    {
        $q = "UPDATE forum_topic SET title = ?, cat = ? WHERE id = ?";
        return $this->query($q, [$title, $cat, $id]);
    }

    public function updateEditForum($id, $name, $des)
    {
        $q = "UPDATE forum_cat SET forum_name = ?, forum_des = ? WHERE id = ?";
        return $this->query($q, [$name, $des, $id]);
    }

    public function stickTopic($id, $mode)
    {
        $q = "UPDATE forum_topic SET stick = ? WHERE id = ?";
        return $this->query($q, [$mode, $id]);
    }

    public function forumCatTopic($id)
    {
        return $this->select('forum_topic', ['*'], ['cat' => $id, 'stick' => ''], ['ORDER BY post_date DESC']);
    }

    public function forumCatTopicStick($id)
    {
        return $this->select('forum_topic', ['*'], ['cat' => $id, 'stick' => '1'], ['ORDER BY post_date DESC']);
    }

    public function showTopic($id)
    {
        return $this->select('forum_topic', ['*'], ['id' => $id]);
    }

    public function showPost($id)
    {
        return $this->select('forum_post', ['*'], ['topic' => $id]);
    }

    public function showPostEdit($id)
    {
        return $this->select('forum_post', ['*'], ['id' => $id]);
    }

    public function createForum($owner, $alli, $name, $des, $area)
    {
        $forumData = ['owner' => $owner, 'alliance' => $alli, 'forum_name' => $name, 'forum_des' => $des, 'area' => $area];
        $this->insert('forum_cat', $forumData);
        return $this->getLastInsertId();
    }

    public function createTopic($title, $post, $cat, $owner, $alli, $ends)
    {
        $date = time();
        $topicData = ['title' => $title, 'post' => $post, 'post_date' => $date, 'last_post' => $date, 'cat' => $cat, 'owner' => $owner, 'alliance' => $alli, 'ends' => $ends];
        $this->insert('forum_topic', $topicData);
        return $this->getLastInsertId();
    }

    public function createPost($post, $tids, $owner)
    {
        $date = time();
        $postData = ['post' => $post, 'topic' => $tids, 'owner' => $owner, 'date' => $date];
        $this->insert('forum_post', $postData);
        return $this->getLastInsertId();
    }

    public function updatePostDate($id)
    {
        $date = time();
        $q = "UPDATE forum_topic SET post_date = ? WHERE id = ?";
        return $this->query($q, [$date, $id]);
    }

    public function editUpdateTopic($id, $post)
    {
        $q = "UPDATE forum_topic SET post = ? WHERE id = ?";
        return $this->query($q, [$post, $id]);
    }

    public function editUpdatePost($id, $post)
    {
        $q = "UPDATE forum_post SET post = ? WHERE id = ?";
        return $this->query($q, [$post, $id]);
    }

    public function lockTopic($id, $mode)
    {
        $q = "UPDATE forum_topic SET close = ? WHERE id = ?";
        return $this->query($q, [$mode, $id]);
    }

    public function deleteCat($id)
    {
        $this->delete('forum_cat', ['id' => $id]);
        $this->delete('forum_topic', ['cat' => $id]);
    }

    public function deleteTopic($id)
    {
        return $this->delete('forum_topic', ['id' => $id]);
    }

    public function deletePost($id)
    {
        return $this->delete('forum_post', ['id' => $id]);
    }

    public function getAllianceName($id)
    {
        if (!$id) return false;
        $result = $this->selectFirst('alidata', ['tag'], ['id' => $id]);
        return $result ? $result[0]['tag'] : false;
    }

    public function getAlliancePermission($ref, $field, $mode)
    {
        if (!$mode) {
            $result = $this->selectFirst('ali_permission', [$field], ['uid' => $ref]);
        } else {
            $result = $this->selectFirst('ali_permission', [$field], ['username' => $ref]);
        }
        return $result ? $result[0][$field] : null;
    }

    public function changePos($id, $mode)
    {
        $forumArea = $this->selectFirst('forum_cat', ['forum_area'], ['id' => $id]);
        if (!$forumArea) return;

        if ($mode == '-1') {
            $prevCat = $this->selectFirst('forum_cat', ['id'], ['forum_area' => $forumArea[0]['forum_area'], 'id<' => $id], ['id DESC']);
            if ($prevCat) {
                $this->update('forum_cat', ['id' => 0], ['id' => $prevCat[0]['id']]);
                $this->update('forum_cat', ['id' => -1], ['id' => $id]);
                $this->update('forum_cat', ['id' => $id], ['id' => 0]);
                $this->update('forum_cat', ['id' => $prevCat[0]['id']], ['id' => -1]);
            }
        } elseif ($mode == 1) {
            $nextCat = $this->selectFirst('forum_cat', ['id'], ['forum_area' => $forumArea[0]['forum_area'], 'id>' => $id], ['id ASC']);
            if ($nextCat) {
                $this->update('forum_cat', ['id' => 0], ['id' => $nextCat[0]['id']]);
                $this->update('forum_cat', ['id' => -1], ['id' => $id]);
                $this->update('forum_cat', ['id' => $id], ['id' => 0]);
                $this->update('forum_cat', ['id' => $nextCat[0]['id']], ['id' => -1]);
            }
        }
    }

    public function forumCatAlliance($id)
    {
        $result = $this->selectFirst('forum_cat', ['alliance'], ['id' => $id]);
        return $result ? $result[0]['alliance'] : null;
    }

    public function createPoll($id, $name, $p1_name, $p2_name, $p3_name, $p4_name)
    {
        $this->insert('forum_poll', ['id' => $id, 'name' => $name, 'p1_name' => $p1_name, 'p2_name' => $p2_name, 'p3_name' => $p3_name, 'p4_name' => $p4_name]);
        return $this->lastInsertId();
    }

    public function createForumRules($aid, $id, $users_id, $users_name, $alli_id, $alli_name)
    {
        $this->insert('fpost_rules', ['aid' => $aid, 'id' => $id, 'users_id' => $users_id, 'users_name' => $users_name, 'alli_id' => $alli_id, 'alli_name' => $alli_name]);
        return $this->lastInsertId();
    }

    public function setAlliName($aid, $name, $tag)
    {
        if (!$aid) return false;
        return $this->update('alidata', ['name' => $name, 'tag' => $tag], ['id' => $aid]);
    }

    public function isAllianceOwner($id)
    {
        if (!$id) return false;
        $result = $this->selectFirst('alidata', ['id'], ['leader' => $id]);
        return $result ? true : false;
    }

    public function aExist($ref, $type)
    {
        $result = $this->selectFirst('alidata', [$type], [$type => $ref]);
        return $result ? true : false;
    }

    public function createAlliance($tag, $name, $uid, $max)
    {
        $this->insert('alidata', ['name' => $name, 'tag' => $tag, 'leader' => $uid, 'max' => $max]);
        return $this->lastInsertId();
    }

    public function insertAlliNotice($aid, $notice)
    {
        $time = time();
        $this->insert('ali_log', ['aid' => $aid, 'notice' => $notice, 'date' => $time]);
        return $this->lastInsertId();
    }

    public function deleteAlliance($aid)
    {
        $result = $this->selectFirst('users', ['id'], ['alliance' => $aid]);
        if (!$result) {
            return $this->delete('alidata', ['id' => $aid]);
        }
        return false;
    }

    public function readAlliNotice($aid)
    {
        return $this->selectAll('ali_log', ['*'], ['aid' => $aid], ['date' => 'DESC']);
    }

    public function createAlliPermissions($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8)
    {
        $this->insert('ali_permission', ['uid' => $uid, 'aid' => $aid, 'rank' => $rank, 'opt1' => $opt1, 'opt2' => $opt2, 'opt3' => $opt3, 'opt4' => $opt4, 'opt5' => $opt5, 'opt6' => $opt6, 'opt7' => $opt7, 'opt8' => $opt8]);
        return $this->lastInsertId();
    }

    public function deleteAlliPermissions($uid)
    {
        return $this->delete('ali_permission', ['uid' => $uid]);
    }

    public function updateAlliPermissions($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8 = 0)
    {
        return $this->update('ali_permission', ['rank' => $rank, 'opt1' => $opt1, 'opt2' => $opt2, 'opt3' => $opt3, 'opt4' => $opt4, 'opt5' => $opt5, 'opt6' => $opt6, 'opt7' => $opt7, 'opt8' => $opt8], ['uid' => $uid, 'alliance' => $aid]);
    }

    public function getAlliPermissions($uid, $aid)
    {
        return $this->selectFirst('ali_permission', ['*'], ['uid' => $uid, 'alliance' => $aid]);
    }

    public function submitAlliProfile($aid, $notice, $desc)
    {
        if (!$aid) return false;
        return $this->update('alidata', ['notice' => $notice, 'desc' => $desc], ['id' => $aid]);
    }

    public function diplomacyInviteAdd($alli1, $alli2, $type)
    {
        return $this->insert('diplomacy', ['alli1' => $alli1, 'alli2' => $alli2, 'type' => intval($type), 'accepted' => 0]);
    }

    public function diplomacyOwnOffers($session_alliance)
    {
        return $this->selectAll('diplomacy', ['*'], ['alli1' => $session_alliance, 'accepted' => 0]);
    }

    public function getAllianceID($name)
    {
        $result = $this->selectFirst('alidata', ['id'], ['tag' => $this->RemoveXSS($name)]);
        return $result ? $result['id'] : null;
    }

    public function RemoveXSS($val)
    {
        return htmlspecialchars($val, ENT_QUOTES);
    }

    public function diplomacyCancelOffer($id)
    {
        return $this->delete('diplomacy', ['id' => $id]);
    }

    public function diplomacyInviteAccept($id, $session_alliance)
    {
        return $this->update('diplomacy', ['accepted' => 1], ['id' => $id, 'alli2' => $session_alliance]);
    }

    public function diplomacyInviteDenied($id, $session_alliance)
    {
        return $this->delete('diplomacy', ['id' => $id, 'alli2' => $session_alliance]);
    }

    public function diplomacyInviteCheck($session_alliance)
    {
        return $this->select('diplomacy', ['alli2' => $session_alliance, 'accepted' => 0]);
    }

    public function diplomacyExistingRelationships($session_alliance)
    {
        return $this->selectAll('diplomacy', ['*'], ['alli2' => $session_alliance, 'accepted' => 1]);
    }

    public function diplomacyExistingRelationships2($session_alliance)
    {
        return $this->selectAll('diplomacy', ['*'], ['alli1' => $session_alliance, 'accepted' => 1]);
    }

    public function diplomacyCancelExistingRelationship($id, $session_alliance)
    {
        return $this->delete('diplomacy', ['id' => $id, 'alli2' => $session_alliance]);
    }

    public function getUserAlliance($id)
    {
        if (!$id) return false;
        $result = $this->selectFirst('users JOIN alidata', 'alidata.tag', 'users.alliance = alidata.id AND users.id = :id', ['id' => $id]);
        return $result ? $result['tag'] : "-";
    }


    public function modifyResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        $updateFields = [
            'wood' => ($mode ? '+' : '-') . $wood,
            'clay' => ($mode ? '+' : '-') . $clay,
            'iron' => ($mode ? '+' : '-') . $iron,
            'crop' => ($mode ? '+' : '-') . $crop
        ];
        return $this->update('vdata', $updateFields, ['wref' => $vid]);
    }

    public function modifyProduction($vid, $woodp, $clayp, $ironp, $cropp, $upkeep)
    {
        $updateFields = [
            'woodp' => $woodp,
            'clayp' => $clayp,
            'ironp' => $ironp,
            'cropp' => $cropp,
            'upkeep' => $upkeep
        ];
        return $this->update('vdata', $updateFields, ['wref' => $vid]);
    }

    public function modifyOasisResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        $updateFields = [
            'wood' => ($mode ? '+' : '-') . $wood,
            'clay' => ($mode ? '+' : '-') . $clay,
            'iron' => ($mode ? '+' : '-') . $iron,
            'crop' => ($mode ? '+' : '-') . $crop
        ];
        return $this->update('odata', $updateFields, ['wref' => $vid]);
    }

    public function getFieldType($vid, $field)
    {
        $result = $this->selectFirst('fdata', "f$field" . "t", 'vref = :vid', ['vid' => $vid]);
        return $result ? $result["f$field" . "t"] : null;
    }

    public function getVSumField($uid, $field)
    {
        $result = $this->selectFirst('vdata', "SUM($field)", 'owner = :uid', ['uid' => $uid]);
        return $result ? $result["SUM($field)"] : null;
    }


    public function updateVillage($vid)
    {
        $time = time();
        $updateFields = ['lastupdate' => $time];
        return $this->update('vdata', $updateFields, ['wref' => $vid]);
    }

    public function updateOasis($vid)
    {
        $time = time();
        $updateFields = ['lastupdated' => $time];
        return $this->update('odata', $updateFields, ['wref' => $vid]);
    }

    public function setVillageName($vid, $name)
    {
        if ($name == '') return false;
        $updateFields = ['name' => $name];
        return $this->update('vdata', $updateFields, ['wref' => $vid]);
    }

    public function modifyPop($vid, $pop, $mode)
    {
        $updateFields = ['pop' => ($mode ? '-' : '+') . $pop];
        return $this->update('vdata', $updateFields, ['wref' => $vid]);
    }

    public function addCP($ref, $cp)
    {
        $updateFields = ['cp' => 'cp + ' . $cp];
        return $this->update('vdata', $updateFields, ['wref' => $ref]);
    }

    public function addCel($ref, $cel, $type)
    {
        $updateFields = ['celebration' => $cel, 'type' => $type];
        return $this->update('vdata', $updateFields, ['wref' => $ref]);
    }

    public function getCel()
    {
        $time = time();
        $condition = "`celebration` < $time AND `celebration` != 0";
        return $this->select('vdata', '*', $condition);
    }

    public function getActiveGCel($vref)
    {
        $time = time();
        $condition = "`vref` = $vref AND `celebration` > $time AND `type` = 2";
        return $this->select('vdata', '*', $condition);
    }

    public function clearCel($ref)
    {
        $updateFields = ['celebration' => 0, 'type' => 0];
        return $this->update('vdata', $updateFields, ['wref' => $ref]);
    }

    public function setCelCp($user, $cp)
    {
        $fields = ['cp'];
        $values = [$cp];
        $conditions = ['id' => $user];
        return $this->update('users', $fields, $values, $conditions);
    }

    public function getInvitation($uid, $ally)
    {
        $conditions = "`uid` = ? AND `alliance` = ?";
        $values = [$uid, $ally];
        return $this->select('ali_invite', '*', $conditions, $values);
    }

    public function getInvitation2($uid)
    {
        $conditions = "`uid` = ?";
        $values = [$uid];
        return $this->select('ali_invite', '*', $conditions, $values);
    }

    public function getAliInvitations($aid)
    {
        $conditions = "`alliance` = ? AND `accept` = 0";
        $values = [$aid];
        return $this->select('ali_invite', '*', $conditions, $values);
    }

    public function sendInvitation($uid, $alli, $sender)
    {
        $fields = ['uid', 'alliance', 'sender', 'timestamp', 'accept'];
        $values = [$uid, $alli, $sender, time(), 0];
        return $this->insert('ali_invite', $fields, $values);
    }

    public function removeInvitation($id)
    {
        $conditions = "`id` = ?";
        $values = [$id];
        return $this->delete('ali_invite', $conditions, $values);
    }

    public function delMessage($id)
    {
        $conditions = "`id` = ?";
        $values = [$id];
        return $this->delete('mdata', $conditions, $values);
    }

    public function delNotice($id, $uid)
    {
        $conditions = "`id` = ? AND `uid` = ?";
        $values = [$id, $uid];
        return $this->delete('ndata', $conditions, $values);
    }

    public function sendMessage($client, $owner, $topic, $message, $send, $alliance, $player, $coor, $report)
    {
        $fields = ['client', 'owner', 'topic', 'message', 'send', 'alliance', 'player', 'coor', 'report', 'time'];
        $values = [$client, $owner, $topic, $message, $send, $alliance, $player, $coor, $report, time()];
        return $this->insert('mdata', $fields, $values);
    }

    public function setArchived($id)
    {
        $fields = ['archived'];
        $values = [1];
        $conditions = ['id' => $id];
        return $this->update('mdata', $fields, $values, $conditions);
    }

    public function setNorm($id)
    {
        $fields = ['archived'];
        $values = [0];
        $conditions = ['id' => $id];
        return $this->update('mdata', $fields, $values, $conditions);
    }

    public function getMessage($id, $mode)
    {
        global $session;
        switch ($mode) {
            case 1:
                $conditions = "`target` = ? AND `send` = 0 AND `archived` = 0";
                $values = [$id];
                break;
            case 2:
                $conditions = "`owner` = ?";
                $values = [$id];
                break;
            case 3:
            case 12:
                $conditions = "`id` = ?";
                $values = [$id];
                break;
            case 4:
                $fields = ['viewed' => 1];
                $conditions = ['id' => $id, 'target' => $session->uid];
                return $this->update('mdata', $fields, $conditions);
            case 5:
                $fields = ['deltarget' => 1, 'viewed' => 1];
                $conditions = ['id' => $id];
                return $this->update('mdata', $fields, $conditions);
            case 6:
                $conditions = "`target` = ? AND `send` = 0 AND `archived` = 1";
                $values = [$id];
                break;
            case 7:
                $fields = ['delowner' => 1];
                $conditions = ['id' => $id];
                return $this->update('mdata', $fields, $conditions);
            case 8:
                $fields = ['deltarget' => 1, 'delowner' => 1, 'viewed' => 1];
                $conditions = ['id' => $id];
                return $this->update('mdata', $fields, $conditions);
            case 9:
                $conditions = "`target` = ? AND `send` = 0 AND `archived` = 0 AND `deltarget` = 0 AND `viewed` = 0";
                $values = [$id];
                break;
            case 10:
                $conditions = "`owner` = ? AND `delowner` = 0";
                $values = [$id];
                break;
            case 11:
                $conditions = "`target` = ? AND `send` = 0 AND `archived` = 1 AND `deltarget` = 0";
                $values = [$id];
                break;
        }

        if ($mode <= 3 || $mode == 6 || $mode > 8) {
            return $this->select('mdata', '*', $conditions, $values, 'time DESC');
        } else {
            return false; // Indicar que a operação não foi realizada
        }
    }

    public function addBuilding($wid, $field, $type, $loop, $time, $master, $level)
    {
        $this->update('fdata', ["f$field" . "t" => $type], "vref = :wid", ['wid' => $wid]);

        return $this->insert('bdata', [
            'wid' => $wid,
            'field' => $field,
            'type' => $type,
            'loop' => $loop,
            'time' => $time,
            'master' => $master,
            'level' => $level
        ]);
    }

    public function getNotice($uid)
    {
        return $this->select('ndata', '*', 'uid = :uid', ['uid' => $uid]);
    }

    public function addNotice($uid, $toWref, $ally, $type, $topic, $data, $time = 0)
    {
        if ($time == 0) {
            $time = time();
        }

        return $this->insert('ndata', [
            'uid' => $uid,
            'toWref' => $toWref,
            'ally' => $ally,
            'topic' => $topic,
            'ntype' => $type,
            'data' => $data,
            'time' => $time,
            'viewed' => 0
        ]);
    }

    public function noticeViewed($id)
    {
        return $this->update('ndata', ['viewed' => 1], 'id = :id', ['id' => $id]);
    }

    public function removeNotice($id)
    {
        return $this->delete('ndata', 'id = :id', ['id' => $id]);
    }

    public function archiveNotice($id)
    {
        return $this->update('ndata', ['archive' => 1], 'id = :id', ['id' => $id]);
    }

    public function unarchiveNotice($id)
    {
        return $this->update('ndata', ['archive' => 0], 'id = :id', ['id' => $id]);
    }

    public function removeBuilding($d)
    {
        global $building;
        $jobs = $building->buildArray;

        $jobLoopconID = -1;
        $SameBuildCount = 0;

        // Encontrar o trabalho a ser removido e outras informações relevantes
        foreach ($jobs as $index => $job) {
            if ($job['id'] == $d) {
                $jobDeleted = $index;
            }
            if ($job['loopcon'] == 1) {
                $jobLoopconID = $index;
            }
            if ($job['master'] == 1) {
                $jobMaster = $index;
            }
        }

        // Determinar o número de construções iguais no mesmo campo
        foreach ($jobs as $index => $job) {
            for ($i = $index + 1; $i < count($jobs); $i++) {
                if ($job['field'] == $jobs[$i]['field']) {
                    $SameBuildCount++;
                }
            }
        }

        // Excluir o edifício da tabela 'bdata'
        $conditions = ['id' => $d];
        $this->delete('bdata', $conditions);

        return true;
    }

    public function addDemolition($wid, $field)
    {
        global $building, $village;

        // Excluir o registro da construção da tabela 'bdata'
        $conditions = ['field' => $field, 'wid' => $wid];
        $this->delete('bdata', $conditions);

        // Calcular os requisitos para demolir o edifício
        $uprequire = $building->resourceRequired($field - 1, $village->resarray['f' . $field . 't']);

        // Calcular o novo nível do campo
        $fieldLevel = $this->getFieldLevel($wid, $field) - 1;

        // Calcular o timestamp para demolir o edifício
        $timestamp = time() + floor($uprequire['time'] / 2);

        // Inserir os detalhes da demolição na tabela 'demolition'
        $data = [
            'wid' => $wid,
            'field' => $field,
            'field_level' => $fieldLevel,
            'timestamp' => $timestamp
        ];
        $this->insert('demolition', $data);

        return true;
    }

    public function getFieldLevel($vid, $field)
    {
        $conditions = ['vref' => $vid];
        $fields = ['f' . $field];
        $result = $this->selectFirst('fdata', $fields, $conditions);
        return $result['f' . $field];
    }

    public function getDemolition($wid = 0)
    {
        $conditions = ($wid) ? ['vref' => $wid] : 'timetofinish <= ' . time();
        $fields = ['vref', 'buildnumber', 'timetofinish'];
        $result = $this->select('demolition', $fields, $conditions);

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function finishDemolition($wid)
    {
        $conditions = ['vref' => $wid];
        $data = ['timetofinish' => 0];
        return $this->update('demolition', $data, $conditions);
    }

    public function delDemolition($wid)
    {
        $conditions = ['vref' => $wid];
        return $this->delete('demolition', $conditions);
    }

    public function getJobs($wid)
    {
        $conditions = ['wid' => $wid];
        $orderBy = 'ID ASC';
        return $this->select('bdata', '*', $conditions, $orderBy);
    }

    public function FinishWoodcutter($wid)
    {
        $time = time() - 1;
        $conditions = ['wid' => $wid, 'type' => 1];
        $orderBy = 'master, timestamp ASC';
        $job = $this->selectFirst('bdata', 'id', $conditions, $orderBy);
        if ($job) {
            $data = ['timestamp' => $time];
            $conditions = ['id' => $job['id']];
            $this->update('bdata', $data, $conditions);
        }
    }

    public function FinishCropLand($wid)
    {
        $time = $_SERVER['REQUEST_TIME'] - 1;

        // Atualizar o tempo do último edifício de tipo 4 (terras agrícolas)
        $conditions = ['wid' => $wid, 'type' => 4];
        $orderBy = 'master, timestamp ASC';
        $fields = ['id', 'timestamp'];
        $job = $this->selectFirst('bdata', $fields, $conditions, $orderBy);
        if ($job) {
            $data = ['timestamp' => $time];
            $conditions = ['id' => $job['id']];
            $this->update('bdata', $data, $conditions);
        }

        // Atualizar o tempo de todos os edifícios de loopcon = 1 e campo <= 18
        $conditions2 = ['wid' => $wid, 'loopcon' => 1, 'field' => ['<=', 18]];
        $orderBy2 = 'master, timestamp ASC';
        $fields2 = ['id', 'timestamp'];
        $jobs = $this->select('bdata', $fields2, $conditions2, $orderBy2);
        foreach ($jobs as $job) {
            $q2 = "UPDATE bdata SET timestamp = timestamp - {$job['timestamp']} WHERE id = '{$job['id']}'";
            $this->executeQuery($q2);
        }
    }

    public function finishBuildings($wid)
    {
        $time = time() - 1;

        // Selecionar todos os edifícios para o vilarejo especificado
        $conditions = ['wid' => $wid];
        $orderBy = 'master, timestamp ASC';
        $fields = ['id'];
        $buildings = $this->select('bdata', $fields, $conditions, $orderBy);

        // Atualizar o tempo de conclusão de cada edifício
        foreach ($buildings as $building) {
            $data = ['timestamp' => $time];
            $conditions = ['id' => $building['id']];
            $this->update('bdata', $data, $conditions);
        }
    }

    public function getMasterJobs($wid)
    {
        $conditions = ['wid' => $wid, 'master' => 1];
        $orderBy = 'master, timestamp ASC';
        $fields = ['id'];
        return $this->select('bdata', $fields, $conditions, $orderBy);
    }

    public function getBuildingByField($wid, $field)
    {
        $conditions = [
            'wid' => $wid,
            'field' => $field,
            'master' => 0
        ];
        $fields = ['id'];
        return $this->select('bdata', $fields, $conditions);
    }

    public function getBuildingByType($wid, $type)
    {
        $conditions = [
            'wid' => $wid,
            'type' => $type,
            'master' => 0
        ];
        $fields = ['id'];
        return $this->select('bdata', $fields, $conditions);
    }

    public function getDorf1Building($wid)
    {
        $conditions = [
            'wid' => $wid,
            'field' => '< 19',
            'master' => 0
        ];
        $fields = ['timestamp'];
        return $this->select('bdata', $fields, $conditions);
    }

    public function getDorf2Building($wid)
    {
        $conditions = [
            'wid' => $wid,
            'field' => '> 18',
            'master' => 0
        ];
        $fields = ['timestamp'];
        return $this->select('bdata', $fields, $conditions);
    }

    public function updateBuildingWithMaster($id, $time, $loop)
    {
        $data = [
            'master' => 0,
            'timestamp' => $time,
            'loopcon' => $loop
        ];
        $conditions = ['id' => $id];
        return $this->update('bdata', $data, $conditions);
    }

    public function getVillageByName($name)
    {
        $name = $this->escapeString($name);
        $conditions = ['name' => $name];
        $fields = ['wref'];
        $result = $this->selectFirst('vdata', $fields, $conditions);
        return $result['wref'];
    }

    public function setMarketAcc($id)
    {
        $data = ['accept' => 1];
        $conditions = ['id' => $id];
        return $this->update('market', $data, $conditions);
    }

    public function sendResource($wood, $clay, $iron, $crop, $merchant)
    {
        $data = [
            'wood' => $wood,
            'clay' => $clay,
            'iron' => $iron,
            'crop' => $crop,
            'merchant' => $merchant
        ];
        return $this->insert('send', $data);
    }

    public function sendResourceMORE($wood, $clay, $iron, $crop, $send)
    {
        $data = [
            'wood' => $wood,
            'clay' => $clay,
            'iron' => $iron,
            'crop' => $crop,
            'send' => $send
        ];
        return $this->insert('send', $data);
    }

    public function removeSend($ref)
    {
        $conditions = ['id' => $ref];
        return $this->delete('send', $conditions);
    }

    function getResourcesBack($vref, $gtype, $gamt)
    {
        // Xtype (1) = wood, (2) = clay, (3) = iron, (4) = crop
        $data = [];
        switch ($gtype) {
            case 1:
                $data['wood'] = "wood + $gamt";
                break;
            case 2:
                $data['clay'] = "clay + $gamt";
                break;
            case 3:
                $data['iron'] = "iron + $gamt";
                break;
            case 4:
                $data['crop'] = "crop + $gamt";
                break;
            default:
                return false; // Tipo de recurso inválido
        }
        $conditions = ['wref' => $vref];
        return $this->update('vdata', $data, $conditions);
    }

    public function getMarketField($vref, $field)
    {
        $conditions = ['vref' => $vref];
        $fields = [$field];
        $result = $this->selectFirst('market', $fields, $conditions);
        if ($result) {
            return $result[$field];
        } else {
            return false; // Campo não encontrado ou mercado não existente para o vref fornecido
        }
    }

    public function removeAcceptedOffer($id)
    {
        $conditions = ['id' => $id];
        return $this->delete('market', $conditions);
    }

    function addMarket($vid, $gtype, $gamt, $wtype, $wamt, $time, $alliance, $merchant, $mode)
    {
        if (!$mode) {
            $data = [
                'vref' => $vid,
                'gtype' => $gtype,
                'gamt' => $gamt,
                'wtype' => $wtype,
                'wamt' => $wamt,
                'accept' => 0,
                'expire' => $time,
                'alliance' => $alliance,
                'merchant' => $merchant
            ];
            return $this->insert('market', $data);
        } else {
            $conditions = ['id' => $gtype, 'vref' => $vid];
            return $this->delete('market', $conditions);
        }
    }

    public function getMarket($vid, $mode)
    {
        $alliance = $this->getUserField($this->getVillageField($vid, "owner"), "alliance", 0);
        if (!$mode) {
            $conditions = ['vref' => $vid, 'accept' => 0];
        } else {
            $conditions = [
                'vref[!]' => $vid,
                'alliance' => $alliance,
                'OR' => [
                    'alliance' => 0,
                    'accept' => 0
                ]
            ];
        }
        return $this->select('market', '*', $conditions, 'id DESC');
    }

    public function getUserField($ref, $field, $mode)
    {
        if (!$mode) {
            $conditions = ['id' => $ref];
        } else {
            $conditions = ['username' => $ref];
        }
        $result = $this->selectFirst('users', $field, $conditions);
        return $result[$field];
    }

    public function getVillageField($ref, $field)
    {
        $result = $this->selectFirst('vdata', $field, ['wref' => $ref]);
        return $result[$field];
    }

    public function getMarketInfo($id)
    {
        return $this->selectFirst('market', '`vref`,`gtype`,`wtype`,`merchant`,`wamt`', ['id' => $id]);
    }

    public function setMovementProc($moveid)
    {
        $data = ['proc' => 1];
        $where = ['moveid' => $moveid];
        return $this->update('movement', $data, $where);
    }

    public function totalMerchantUsed($vid)
    {
        $sql = "SELECT SUM(send.merchant)
            FROM send
            JOIN movement ON send.id = movement.ref
            WHERE movement.from = :vid AND movement.proc = 0 AND movement.sort_type = 0";

        $stmt = $this->executeQuery($sql, [':vid' => $vid]);
        $row = $stmt->fetchColumn();

        $sql2 = "SELECT SUM(send.merchant)
             FROM send
             JOIN movement ON send.id = movement.ref
             WHERE movement.to = :vid AND movement.proc = 0 AND movement.sort_type = 1";

        $stmt2 = $this->executeQuery($sql2, [':vid' => $vid]);
        $row2 = $stmt2->fetchColumn();

        $sql3 = "SELECT SUM(merchant)
             FROM market
             WHERE vref = :vid AND accept = 0";

        $stmt3 = $this->executeQuery($sql3, [':vid' => $vid]);
        $row3 = $stmt3->fetchColumn();

        return ($row + $row2 + $row3);
    }

    public function getMovementById($id)
    {
        $results = $this->select('movement', "`starttime`, `to`, `from`", "moveid = :id", [':id' => $id]);
        return !empty($results) ? $results[0] : [];
    }

    public function cancelMovement($id, $newfrom, $newto)
    {
        $refstr = '';
        $amove = $this->selectFirst("SELECT ref FROM movement WHERE moveid = :id", [':id' => $id]);

        if (!empty($amove)) {
            $mov = $amove[0];
            if ($mov['ref'] == 0) {
                $ref = $this->addAttack($newto, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, 0, 0, 0);
                $refstr = ', `ref` = ' . $ref;
            }

            $this->update('movement',
                ['from' => $newfrom, 'to' => $newto, 'sort_type' => 4, 'endtime' => time() - 'starttime', 'starttime' => time(), 'ref' => $ref],
                ['moveid' => $id]
            );
        }
    }

    public function addAttack($vid, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type, $ctar1, $ctar2, $spy)
    {
        $data = [
            'vid' => $vid,
            't1' => $t1,
            't2' => $t2,
            't3' => $t3,
            't4' => $t4,
            't5' => $t5,
            't6' => $t6,
            't7' => $t7,
            't8' => $t8,
            't9' => $t9,
            't10' => $t10,
            't11' => $t11,
            'type' => $type,
            'ctar1' => $ctar1,
            'ctar2' => $ctar2,
            'spy' => $spy
        ];

        return $this->insert('attacks', $data);
    }

    public function getAdvMovement($village)
    {
        $sql = "SELECT `moveid` FROM movement WHERE `from` = :village AND sort_type = 9";
        return $this->select($sql, [':village' => $village]);
    }

    public function getCompletedAdvMovement($village)
    {
        $sql = "SELECT `moveid` FROM movement WHERE `from` = :village AND sort_type = 9 AND proc = 1";
        return $this->select($sql, [':village' => $village]);
    }

    public function addA2b($ckey, $timestamp, $to, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type)
    {
        $data = [
            'ckey' => $ckey,
            'time_check' => $timestamp,
            'to_vid' => $to,
            'u1' => $t1,
            'u2' => $t2,
            'u3' => $t3,
            'u4' => $t4,
            'u5' => $t5,
            'u6' => $t6,
            'u7' => $t7,
            'u8' => $t8,
            'u9' => $t9,
            'u10' => $t10,
            'u11' => $t11,
            'type' => $type
        ];

        return $this->insert('a2b', $data);
    }

    public function getA2b($ckey, $check)
    {
        $sql = "SELECT * FROM a2b WHERE ckey = :ckey AND time_check = :check";
        return $this->selectFirst($sql, [':ckey' => $ckey, ':check' => $check]);
    }

    public function removeA2b($ckey, $check)
    {
        $where = "ckey = :ckey AND time_check = :check";
        return $this->delete('a2b', $where, [':ckey' => $ckey, ':check' => $check]);
    }

    public function addMovement($type, $from, $to, $ref, $data, $endtime)
    {
        $data = [
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'ref' => $ref,
            'data' => $data,
            'starttime' => time(),
            'endtime' => $endtime,
            'proc' => 0
        ];

        return $this->insert('movement', $data);
    }

    public function modifyAttack($aid, $unit, $amt)
    {
        $unit = 't' . $unit;
        $sql = "SELECT $unit FROM attacks WHERE id = :aid";
        $row = $this->selectFirst($sql, [':aid' => $aid]);
        if ($row) {
            $amt = min($row[$unit], $amt);
            $newAmt = $row[$unit] - $amt;
            $data = [$unit => $newAmt];
            $where = 'id = :aid';
            return $this->update('attacks', $data, $where, [':aid' => $aid]);
        }
        return false;
    }

    public function getRanking()
    {
        $sql = "SELECT id, username, alliance, ap, apall, dp, dpall, access FROM users WHERE tribe <= 3 AND access < :access";
        return $this->select($sql, [':access' => (INCLUDE_ADMIN ? 10 : 8)]);
    }

    public function getBuildList($type, $wid = 0)
    {
        $where = 'TRUE';
        $params = [];
        if ($type) {
            $where .= ' AND type = :type';
            $params[':type'] = $type;
        }
        if ($wid) {
            $where .= ' AND wid = :wid';
            $params[':wid'] = $wid;
        }
        $sql = "SELECT id FROM bdata WHERE $where";
        return $this->select($sql, $params);
    }

    public function getVRanking()
    {
        $sql = "SELECT v.wref, v.name, v.owner, v.pop FROM vdata AS v, users AS u WHERE v.owner = u.id AND u.tribe <= 3 AND v.wref != '' AND u.access < :access";
        return $this->select($sql, [':access' => (INCLUDE_ADMIN ? 10 : 8)]);
    }

    public function getARanking()
    {
        $sql = "SELECT id, name, tag FROM alidata WHERE id != ''";
        return $this->select($sql);
    }

    public function getHeroRanking($limit = '')
    {
        $sql = "SELECT uid, level, experience FROM hero ORDER BY experience DESC $limit";
        return $this->select($sql);
    }

    public function getAllMember($aid)
    {
        $sql = "SELECT id, username, timestamp FROM users WHERE alliance = :aid ORDER BY (SELECT SUM(pop) FROM vdata WHERE owner = users.id) DESC";
        return $this->select($sql, [':aid' => $aid]);
    }

    public function getUnit($vid)
    {
        $sql = "SELECT * FROM units WHERE vref = :vid";
        $row = $this->selectFirst($sql, [':vid' => $vid]);
        return $row ? $row : null;
    }

    public function getHUnit($vid)
    {
        $sql = "SELECT hero FROM units WHERE vref = :vid";
        $row = $this->selectFirst($sql, [':vid' => $vid]);
        return $row['hero'] != 0;
    }

    public function getHero($uid = 0, $id = 0, $dead = 2)
    {
        $where = 'TRUE';
        $params = [];
        if ($uid) {
            $where .= ' AND uid = :uid';
            $params[':uid'] = $uid;
        }
        if ($id) {
            $where .= ' AND id = :id';
            $params[':id'] = $id;
        }
        if ($dead != 2) {
            $where .= ' AND dead = :dead';
            $params[':dead'] = $dead;
        }
        $sql = "SELECT * FROM hero WHERE $where LIMIT 1";
        return $this->selectFirst($sql, $params);
    }

    public function modifyHero($uid = 0, $id = 0, $column, $value, $mode = 0)
    {
        $cmd = '';
        switch ($mode) {
            case 0:
                $cmd .= " $column = :value ";
                break;
            case 1:
                $cmd .= " $column = $column + :value ";
                break;
            case 2:
                $cmd .= " $column = $column - :value ";
                break;
            case 3:
                $cmd .= " $column = $column * :value ";
                break;
            case 4:
                $cmd .= " $column = $column / :value ";
                break;
        }

        switch ($column) {
            case 'r0':
            case 'r1':
            case 'r2':
            case 'r3':
            case 'r4':
                $cmd .= " ,rc = 1 ";
                break;
        }

        $where = 'TRUE';
        $params = [':value' => $value];
        if ($uid) {
            $where .= ' AND uid = :uid ';
            $params[':uid'] = $uid;
        }
        if ($id) {
            $where .= ' AND heroid = :id ';
            $params[':id'] = $id;
        }

        $sql = "UPDATE hero SET $cmd WHERE $where";
        return $this->executeQuery($sql, $params);
    }

    public function clearTech($vref)
    {
        $this->delete('tdata', 'vref = :vref', [':vref' => $vref]);
        return $this->addTech($vref);
    }

    public function addTech($vid)
    {
        return $this->insert('tdata', ['vref' => $vid]);
    }

    public function clearABTech($vref)
    {
        $this->delete('abdata', 'vref = :vref', [':vref' => $vref]);
        return $this->addABTech($vref);
    }

    public function addABTech($vid)
    {
        return $this->insert('abdata', ['vref' => $vid]);
    }

    public function getABTech($vid)
    {
        return $this->selectFirst('abdata', '*', 'vref = :vid', [':vid' => $vid]);
    }

    public function addResearch($vid, $tech, $time)
    {
        return $this->insert('research', ['vref' => $vid, 'tech' => $tech, 'time' => $time]);
    }

    public function getResearching($vid)
    {
        return $this->select('research', '*', 'vref = :vid', [':vid' => $vid]);
    }

    public function checkIfResearched($vref, $unit)
    {
        $row = $this->selectFirst('tdata', $unit, 'vref = :vref', [':vref' => $vref]);
        return $row[$unit];
    }

    public function getTech($vid)
    {
        return $this->selectFirst('tdata', '*', 'vref = :vid', [':vid' => $vid]);
    }

    public function getTraining($vid)
    {
        return $this->select('training', '*', 'vref = :vid ORDER BY id', [':vid' => $vid]);
    }

    public function trainUnit($vid, $unit, $amt, $pop, $each, $commence, $mode)
    {
        $technology = new Technology(); // Supondo que a classe Technology esteja disponível

        if (!$mode) {
            $barracks = [1, 2, 3, 11, 12, 13, 14, 21, 22, 31, 32, 33, 34, 41, 42, 43, 44];
            $greatbarracks = [61, 62, 63, 71, 72, 73, 84, 81, 82, 91, 92, 93, 94, 101, 102, 103, 104];
            $stables = [4, 5, 6, 15, 16, 23, 24, 25, 26, 35, 36, 45, 46];
            $greatstables = [64, 65, 66, 75, 76, 83, 84, 85, 86, 95, 96, 105, 106];
            $workshop = [7, 8, 17, 18, 27, 28, 37, 38, 47, 48];
            $greatworkshop = [67, 68, 77, 78, 87, 88, 97, 98, 107, 108];
            $residence = [9, 10, 19, 20, 29, 30, 39, 40, 49, 50];
            $trap = [199];

            if (in_array($unit, $barracks)) {
                $queued = $technology->getTrainingList(1);
            } elseif (in_array($unit, $stables)) {
                $queued = $technology->getTrainingList(2);
            } elseif (in_array($unit, $workshop)) {
                $queued = $technology->getTrainingList(3);
            } elseif (in_array($unit, $residence)) {
                $queued = $technology->getTrainingList(4);
            } elseif (in_array($unit, $greatbarracks)) {
                $queued = $technology->getTrainingList(5);
            } elseif (in_array($unit, $greatstables)) {
                $queued = $technology->getTrainingList(6);
            } elseif (in_array($unit, $greatworkshop)) {
                $queued = $technology->getTrainingList(7);
            } elseif (in_array($unit, $trap)) {
                $queued = $technology->getTrainingList(8);
            }
            $timestamp = time();

            if (!empty($queued) && $queued[count($queued) - 1]['unit'] == $unit) {
                $endat = $each * $amt / 1000;
                $q = "UPDATE training SET amt = amt + :amt, timestamp = :timestamp, endat = endat + :endat WHERE id = :id";
                $params = [
                    ':amt' => $amt,
                    ':timestamp' => $timestamp,
                    ':endat' => $endat,
                    ':id' => $queued[count($queued) - 1]['id']
                ];
            } else {
                $endat = $timestamp + ($each * $amt / 1000);
                $q = "INSERT INTO training (vref, unit, amt, pop, timestamp, eachtime, commence, endat) VALUES (:vid, :unit, :amt, :pop, :timestamp, :each, :commence, :endat)";
                $params = [
                    ':vid' => $vid,
                    ':unit' => $unit,
                    ':amt' => $amt,
                    ':pop' => $pop,
                    ':timestamp' => $timestamp,
                    ':each' => $each,
                    ':commence' => $commence,
                    ':endat' => $endat
                ];
            }
        } else {
            $q = "DELETE FROM training WHERE id = :id";
            $params = [':id' => $vid];
        }

        return $this->executeQuery($q, $params);
    }

    public function removeZeroTrain()
    {
        return $this->delete('training', ['unit[!]' => 0, 'amt[<]' => 1]);
    }

    public function getHeroTrain($vid)
    {
        return $this->selectFirst('training', ['id', 'eachtime'], ['vref' => $vid, 'unit' => 0]);
    }

    public function trainHero($vid, $each, $endat, $mode)
    {
        if (!$mode) {
            return $this->insert('training', ['vref' => $vid, 'amt' => 0, 'eachtime' => 1, 'unit' => 6, 'timestamp' => time(), 'timestamp' => $each, 'timestamp' => $endat]);
        } else {
            return $this->delete('training', ['id' => $vid]);
        }
    }

    public function updateTraining($id, $trained)
    {
        $time = time();
        return $this->update('training', ['amt' => 'GREATEST(amt - :trained, 0)', 'timestamp' => $time], ['id' => $id], [':trained' => $trained]);
    }

    function modifyUnit($vref, $unit, $amt, $mode)
    {
        if ($unit == 230) {
            $unit = 30;
        }
        if ($unit == 231) {
            $unit = 31;
        }
        if ($unit == 120) {
            $unit = 20;
        }
        if ($unit == 121) {
            $unit = 21;
        }
        if ($unit == 'hero') {
            $unit = 'hero';
        } else {
            $unit = 'u' . $unit;
        }
        switch ($mode) {
            case 0:
                $q = "SELECT $unit FROM units WHERE vref = :vref";
                $result = $this->executeQuery($q, [':vref' => $vref]);
                $row = $result->fetch(PDO::FETCH_ASSOC);
                $amt = min($row[$unit], $amt);
                $q = "UPDATE units SET $unit = ($unit - :amt) WHERE vref = :vref";
                break;
            case 1:
                $q = "UPDATE units SET $unit = $unit + :amt WHERE vref = :vref";
                break;
            case 2:
                $q = "UPDATE units SET $unit = :amt WHERE vref = :vref";
                break;
        }
        return $this->executeQuery($q, [':vref' => $vref, ':amt' => $amt]);
    }

    public function getFilledTrapCount($vref)
    {
        $result = 0;
        $trapped = $this->select('trapped', ['vref' => $vref]);
        foreach ($trapped as $trap) {
            for ($i = 1; $i <= 50; $i++) {
                $result += max(0, $trap['u' . $i]);
            }
            $result += max(0, $trap['hero']);
        }
        return $result;
    }

    public function getTrapped($id)
    {
        return $this->selectFirst('trapped', ['id' => $id]);
    }

    public function getTrappedIn($vref)
    {
        return $this->select('trapped', ['vref' => $vref]);
    }

    public function getTrappedFrom($from)
    {
        return $this->select('trapped', ['from' => $from]);
    }

    public function addTrapped($vref, $from)
    {
        $id = $this->hasTrapped($vref, $from);
        if (!$id) {
            return $this->insert('trapped', ['vref' => $vref, 'from' => $from]);
        }
        return $id;
    }

    public function hasTrapped($vref, $from)
    {
        return $this->select('trapped', 'id', ['vref' => $vref, 'from' => $from]);
    }

    public function modifyTrapped($id, $unit, $amt, $mode)
    {
        $columnName = ($unit == 'hero') ? 'hero' : 'u' . $unit;
        $operation = ($mode == 0) ? '-' : '+';
        $this->update('trapped', [$columnName => $columnName . $operation . $amt], ['id' => $id]);
    }

    public function removeTrapped($id)
    {
        $this->delete('trapped', ['id' => $id]);
    }

    public function removeAnimals($id)
    {
        $this->delete('enforcement', ['id' => $id]);
    }

    public function checkEnforce($vid, $from)
    {
        $enforce = $this->selectFirst('enforcement', 'id', ['`from`' => $from, 'vref' => $vid]);
        return $enforce ? $enforce['id'] : false;
    }

    public function addEnforce($data)
    {
        $id = $this->insert('enforcement', ['vref' => $data['to'], '`from`' => $data['from']]);

        // Obter informações do local de origem
        $fromVillage = $this->isVillageOasis($data['from']) ? $this->getOMInfo($data['from']) : $this->getMInfo($data['from']);
        $fromTribe = $this->getUserField($fromVillage["owner"], "tribe", 0);
        $start = ($fromTribe - 1) * 10 + 1;
        $end = ($fromTribe * 10);

        // Adicionar unidades
        $j = 1;
        for ($i = $start; $i <= $end; $i++) {
            $this->modifyEnforce($id, $i, $data['t' . $j], 1);
            $j++;
        }

        return $id;
    }

    public function getOMInfo($id)
    {
        return $this->join('wdata', 'odata', 'odata.wref = wdata.id')->selectFirst('wdata.*, odata.*', ['wdata.id' => $id]);
    }

    public function getMInfo($id)
    {
        return $this->join('wdata', 'vdata', 'vdata.wref = wdata.id')->selectFirst('wdata.*, vdata.*', ['wdata.id' => $id]);
    }

    public function modifyEnforce($id, $unit, $amt, $mode)
    {
        $columnName = ($unit == 'hero') ? 'hero' : 'u' . $unit;
        $operation = ($mode == 0) ? '-' : '+';

        $data = [$columnName => ["$columnName $operation :amt"]];
        $conditions = ['id' => $id];

        $this->update('enforcement', $data, $conditions, true, [':amt' => $amt]);
    }

    public function addHeroEnforce($data)
    {
        $this->insert("enforcement", ['vref' => $data['to'], 'from' => $data['from'], 'hero' => 1]);
    }

    public function getEnforceArray($id, $mode)
    {
        if (!$mode) {
            return $this->selectFirst("enforcement", "*", "id = :id", [':id' => $id]);
        } else {
            return $this->selectFirst("enforcement", "*", "`from` = :id", [':id' => $id]);
        }
    }

    public function getEnforceVillage($id, $mode)
    {
        if (!$mode) {
            return $this->select("enforcement", "*", "`vref` = :id", [':id' => $id]);
        } else {
            return $this->select("enforcement", "*", "`from` = :id", [':id' => $id]);
        }
    }

    public function getOasesEnforce($id)
    {
        $oasisowned = $this->getOasis($id);
        if (!empty($oasisowned) && count($oasisowned) > 0) {
            $inos = '(';
            foreach ($oasisowned as $oo) $inos .= $oo['wref'] . ',';
            $inos = substr($inos, 0, strlen($inos) - 1);
            $inos .= ')';
            return $this->select("enforcement", "*", "`from` = :id AND `vref` IN " . $inos, [':id' => $id]);
        } else {
            return null;
        }
    }

    public function getOasis($vid)
    {
        return $this->select("odata", "type, wref", "conqured = :vid", [":vid" => $vid]);
    }

    public function getVillageMovement($id)
    {
        $vinfo = $this->getVillage($id);
        if (isset($vinfo['owner'])) {
            $vtribe = $this->getUserField($vinfo['owner'], "tribe", 0);
            $movingunits = [];
            $outgoingarray = $this->getMovement(3, $id, 0);
            for ($i = 1; $i <= 10; $i++) $movingunits['u' . (($vtribe - 1) * 10 + $i)] = 0;
            $movingunits['hero'] = 0;
            if (!empty($outgoingarray)) {
                foreach ($outgoingarray as $out) {
                    for ($i = 1; $i <= 10; $i++) {
                        $movingunits['u' . (($vtribe - 1) * 10 + $i)] += $out['t' . $i];
                    }
                    $movingunits['hero'] += $out['t11'];
                }
            }
            $returningarray = $this->getMovement(4, $id, 1);
            if (!empty($returningarray)) {
                foreach ($returningarray as $ret) {
                    if ($ret['attack_type'] != 1) {
                        for ($i = 1; $i <= 10; $i++) {
                            $movingunits['u' . (($vtribe - 1) * 10 + $i)] += $ret['t' . $i];
                        }
                        $movingunits['hero'] += $ret['t11'];
                    }
                }
            }
            $settlerarray = $this->getMovement(5, $id, 0);
            if (!empty($settlerarray)) {
                $movingunits['u' . ($vtribe * 10)] += 3 * count($settlerarray);
            }
            $advarray = $this->getMovement(9, $id, 0);
            if (!empty($advarray)) {
                $movingunits['hero'] += 1;
            }
            return $movingunits;
        } else {
            return [];
        }
    }

    /**
     * Function to retrieve movement of village
     * Type 0: Send Resource
     * Type 1: Send Merchant
     * Type 2: Return Resource
     * Type 3: Attack
     * Type 4: Return
     * Type 5: Settler
     * Type 6: Bounty
     * Type 7: Reinf.
     * Type 9: Adventure
     * Mode 0: Send/Out
     * Mode 1: Recieve/In
     * References: Type, Village, Mode
     */
    public function getMovement($type, $village, $mode)
    {
        if (!$mode) {
            $where = "`from`";
        } else {
            $where = "`to`";
        }
        switch ($type) {
            case 0:
                $q = "SELECT * FROM movement, send where movement." . $where . " = $village and movement.ref = send.id and movement.proc = 0 and movement.sort_type = 0";
                break;
            case 1:
                $q = "SELECT * FROM movement, send where movement." . $where . " = $village and movement.ref = send.id and movement.proc = 0 and movement.sort_type = 1";
                break;
            case 2:
                $q = "SELECT * FROM movement where movement." . $where . " = $village and movement.proc = 0 and movement.sort_type = 2";
                break;
            case 3:
                $q = "SELECT * FROM movement, attacks where movement." . $where . " = $village and movement.ref = attacks.id and movement.proc = 0 and movement.sort_type = 3 ORDER BY endtime ASC";
                break;
            case 4:
                $q = "SELECT * FROM movement, attacks where movement." . $where . " = $village and movement.ref = attacks.id and movement.proc = 0 and movement.sort_type = 4 ORDER BY endtime ASC";
                break;
            case 5:
                $q = "SELECT * FROM movement where movement." . $where . " = $village and sort_type = 5 and proc = 0";
                break;
            case 6:
                $q = "SELECT * FROM movement,odata, attacks where odata.conqured = $village and movement.to = odata.wref and movement.ref = attacks.id and movement.proc = 0 and movement.sort_type = 3 ORDER BY endtime ASC";
                break;
            case 9:
                $q = "SELECT * FROM movement where movement." . $where . " = $village and sort_type = 9 and proc = 0";
                break;
            case 34:
                $q = "SELECT * FROM movement, attacks where (movement." . $where . " = $village and movement.ref = attacks.id and movement.proc = 0) and (movement.sort_type = 3 or  movement.sort_type = 4) ORDER BY endtime ASC";
                break;
        }
        $result = $this->executeQuery($q);
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function getVillageMovementArray(int $id)
    {
        $movingarray = [];
        $outgoingarray = $this->getMovement(3, $id, 0);
        if (!empty($outgoingarray)) {
            $movingarray = array_merge($movingarray, $outgoingarray);
        }
        $returningarray = $this->getMovement(4, $id, 1);
        if (!empty($returningarray)) {
            $movingarray = array_merge($movingarray, $returningarray);
        }
        return $movingarray;
    }

    public function getWW()
    {
        // Usando o método select() da classe Database para realizar a consulta SQL de forma segura
        $result = $this->select('fdata', 'vref', 'f99t = ?', [40]);

        // Verificando se há resultados retornados
        return !empty($result);
    }

    public function getWWLevel($vref)
    {
        // Usando o método selectFirst() da classe Database para obter a primeira linha que corresponde à condição
        $result = $this->selectFirst('fdata', 'f99', 'vref = ?', [$vref]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['f99'])) {
            return $result['f99']; // Retorna o valor de 'f99' como um inteiro
        }

        return null; // Retorna null se não houver resultados ou se 'f99' não estiver definido
    }

    public function getWWOwnerID($vref)
    {
        // Usando o método selectFirst() da classe Database para obter a primeira linha que corresponde à condição
        $result = $this->selectFirst('vdata', 'owner', 'wref = ?', [$vref]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['owner'])) {
            return (int)$result['owner']; // Retorna o valor de 'owner' como um inteiro
        }

        return null; // Retorna null se não houver resultados ou se 'owner' não estiver definido
    }

    public function getUserAllianceID($id): ?int
    {
        // Usando o método selectFirst() da classe Database para obter a primeira linha que corresponde à condição
        $result = $this->selectFirst('users', 'alliance', 'id = ?', [$id]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['alliance'])) {
            return (int)$result['alliance']; // Retorna o valor de 'alliance' como um inteiro
        }

        return null; // Retorna null se não houver resultados ou se 'alliance' não estiver definido
    }

    public function getWWName($vref): ?string
    {
        // Usando o método selectFirst() da classe Database para obter a primeira linha que corresponde à condição
        $result = $this->selectFirst('fdata', 'wwname', 'vref = ?', [$vref]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['wwname'])) {
            return $result['wwname']; // Retorna o valor de 'wwname'
        }

        return null; // Retorna null se não houver resultados ou se 'wwname' não estiver definido
    }

    public function submitWWname($vref, $name): bool
    {
        // Usando o método update() da classe Database para executar uma consulta UPDATE de forma segura
        return $this->update('fdata', ['wwname' => $name], 'vref = ?', [$vref]);
    }

    public function modifyCommence($id, $commence = 0): bool
    {
        if ($commence == 0) {
            $commence = time();
        }
        // Usando o método update() da classe Database para executar uma consulta UPDATE de forma segura
        return $this->update('training', ['commence' => $commence], 'id = ?', [$id]);
    }

    public function getTrainingList()
    {
        // Usando o método select() da classe Database para realizar uma consulta SELECT de forma segura
        $result = $this->select('training', '`id`,`vref`,`unit`,`eachtime`,`endat`,`commence`,`amt`', 'amt != ?', [0]);

        // Verificando se há resultados retornados
        if (!empty($result)) {
            return $result;
        } else {
            return []; // Retorna um array vazio se não houver resultados
        }
    }

    public function getNeedDelete(): array
    {
        $time = time();
        // Usando o método select() da classe Database para realizar uma consulta SELECT de forma segura
        $result = $this->select('deleting', 'uid', 'timestamp <= ?', [$time]);

        // Verificando se há resultados retornados
        if (!empty($result)) {
            return $result;
        } else {
            return []; // Retorna um array vazio se não houver resultados
        }
    }

    public function countUser(): int
    {
        // Usando o método selectFirst() da classe Database para obter a primeira linha que corresponde à condição
        $result = $this->selectFirst('users', 'COUNT(id) as total', 'id != ?', [0]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['total'])) {
            return (int)$result['total']; // Retorna o total como um inteiro
        }

        return 0; // Retorna 0 se não houver resultados ou se 'total' não estiver definido
    }

    public function countAlli(): int
    {
        // Usando o método selectFirst() da classe Database para obter a primeira linha que corresponde à condição
        $result = $this->selectFirst('alidata', 'COUNT(id) as total', 'id != ?', [0]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['total'])) {
            return (int)$result['total']; // Retorna o total como um inteiro
        }

        return 0; // Retorna 0 se não houver resultados ou se 'total' não estiver definido
    }

    function getWoodAvailable($wref)
    {
        // Usando o método selectFirst() da classe Database para obter o valor da madeira
        $result = $this->selectFirst('vdata', 'wood', 'wref = ?', [$wref]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['wood'])) {
            return (int)$result['wood']; // Retorna o valor da madeira como um inteiro
        }

        return 0; // Retorna 0 se não houver resultados ou se 'wood' não estiver definido
    }

    function getClayAvailable($wref)
    {
        // Usando o método selectFirst() da classe Database para obter o valor do barro
        $result = $this->selectFirst('vdata', 'clay', 'wref = ?', [$wref]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['clay'])) {
            return (int)$result['clay']; // Retorna o valor do barro como um inteiro
        }

        return 0; // Retorna 0 se não houver resultados ou se 'clay' não estiver definido
    }

    function getIronAvailable($wref)
    {
        // Usando o método selectFirst() da classe Database para obter o valor do ferro
        $result = $this->selectFirst('vdata', 'iron', 'wref = ?', [$wref]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['iron'])) {
            return (int)$result['iron']; // Retorna o valor do ferro como um inteiro
        }

        return 0; // Retorna 0 se não houver resultados ou se 'iron' não estiver definido
    }

    function getCropAvailable($wref)
    {
        // Usando o método selectFirst() da classe Database para obter o valor da safra
        $result = $this->selectFirst('vdata', 'crop', 'wref = ?', [$wref]);

        // Verificando se há um resultado retornado
        if ($result !== false && isset($result['crop'])) {
            return (int)$result['crop']; // Retorna o valor da safra como um inteiro
        }

        return 0; // Retorna 0 se não houver resultados ou se 'crop' não estiver definido
    }

    function populateOasisData()
    {
        $rows = $this->select('wdata', 'id', 'oasistype != 0');

        foreach ($rows as $row) {
            $wid = $row['id'];
            $time = time();
            $t1 = 750 * SPEED / 10;
            $t2 = 750 * SPEED / 10;
            $t3 = 750 * SPEED / 10;
            $t4 = 800 * SPEED / 10;
            $t5 = 750 * SPEED / 10;
            $t6 = 800 * SPEED / 10;
            $tt = "$t1,$t2,$t3,0,0,0,$t4,$t5,0,$t6,$time,$time,$time";

            $data = [
                "id" => $wid,
                "oasistype" => 3,
                "tt" => $tt,
                "conqured" => 100,
                "loyalty" => 3,
                "name" => 'Unoccupied oasis'
            ];
            $this->insert('odata', $data);
        }
    }

    public function getAvailableExpansionTraining()
    {
        $q = "SELECT (IF(exp1=0,1,0)+IF(exp2=0,1,0)+IF(exp3=0,1,0)) FROM vdata WHERE wref = ?";
        $result = $this->selectFirst('vdata', '(IF(exp1=0,1,0)+IF(exp2=0,1,0)+IF(exp3=0,1,0))', 'wref = ?', [$village->wid]);
        $maxslots = $result !== false ? (int)$result : 0;

        $residence = $building->getTypeLevel(25);
        $palace = $building->getTypeLevel(26);
        if ($residence > 0) {
            $maxslots -= (3 - floor($residence / 10));
        }
        if ($palace > 0) {
            $maxslots -= (3 - floor(($palace - 5) / 5));
        }

        $result = $this->selectFirst('units', '(u10+u20+u30)', 'vref = ?', [$village->wid]);
        $settlers = $result !== false ? (int)$result : 0;

        $result = $this->selectFirst('units', '(u9+u19+u29)', 'vref = ?', [$village->wid]);
        $chiefs = $result !== false ? (int)$result : 0;

        $current_movement = $this->getMovement(5, $village->wid, 0);
        $settlers += 3 * count($current_movement);
        foreach ($current_movement as $build) {
            $settlers += $build['t10'];
            $chiefs += $build['t9'];
        }

        $movements = [3, 4];
        foreach ($movements as $movement) {
            for ($i = 0; $i <= 1; $i++) {
                $current_movement = $this->getMovement($movement, $village->wid, $i);
                if (!empty($current_movement)) {
                    foreach ($current_movement as $build) {
                        $settlers += $build['t10'];
                        $chiefs += $build['t9'];
                    }
                }
            }
        }

        $q = "SELECT (u10+u20+u30) FROM enforcement WHERE `from` = ?";
        $result = $this->selectFirst('enforcement', '(u10+u20+u30)', '`from` = ?', [$village->wid]);
        if (!empty($result)) {
            $settlers += (int)$result;
        }

        $q = "SELECT (u10+u20+u30) FROM trapped WHERE `from` = ?";
        $result = $this->selectFirst('trapped', '(u10+u20+u30)', '`from` = ?', [$village->wid]);
        if (!empty($result)) {
            $settlers += (int)$result;
        }

        $q = "SELECT (u9+u19+u29) FROM enforcement WHERE `from` = ?";
        $result = $this->selectFirst('enforcement', '(u9+u19+u29)', '`from` = ?', [$village->wid]);
        if (!empty($result)) {
            $chiefs += (int)$result;
        }

        $q = "SELECT (u9+u19+u29) FROM trapped WHERE `from` = ?";
        $result = $this->selectFirst('trapped', '(u9+u19+u29)', '`from` = ?', [$village->wid]);
        if (!empty($result)) {
            $chiefs += (int)$result;
        }

        $trainlist = $technology->getTrainingList(4);
        if (!empty($trainlist)) {
            foreach ($trainlist as $train) {
                if ($train['unit'] % 10 == 0) {
                    $settlers += $train['amt'];
                }
                if ($train['unit'] % 10 == 9) {
                    $chiefs += $train['amt'];
                }
            }
        }

        $settlerslots = $maxslots * 3 - $settlers - $chiefs * 3;
        $chiefslots = $maxslots - $chiefs - floor(($settlers + 2) / 3);

        if (!$technology->getTech(($session->tribe - 1) * 10 + 9)) {
            $chiefslots = 0;
        }
        $slots = ["chiefs" => $chiefslots, "settlers" => $settlerslots];
        return $slots;
    }

    public function addArtefact($vref, $owner, $type, $size, $name, $desc, $effecttype, $effect, $aoe, $img)
    {
        $data = [
            "vref" => $vref,
            "owner" => $owner,
            "type" => $type,
            "size" => $size,
            "conquered" => time(),
            "name" => $name,
            "desc" => $desc,
            "effecttype" => $effecttype,
            "effect" => $effect,
            "aoe" => $aoe,
            "img" => $img
        ];

        return $this->insert("artefacts", $data);
    }


    public function getOwnArtefactInfo($vref)
    {
        return $this->select('artefacts', '*', 'vref = ?', [$vref]);
    }

    public function getArtefactInfo($sizes)
    {
        $conditions = "";
        $params = [];

        if (!empty($sizes)) {
            $placeholders = implode(",", array_fill(0, count($sizes), "?"));
            $conditions = " AND `size` IN ($placeholders)";
            $params = $sizes;
        }

        return $this->select("artefacts", "*", "1 $conditions ORDER BY type", $params);
    }

    public function getArtefactInfoByDistance($coor, $distance, $sizes)
    {
        if (count($sizes) != 0) {
            $sizestr = ' AND ( FALSE ';
            foreach ($sizes as $s) {
                $sizestr .= ' OR artefacts.size = ? ';
            }
            $sizestr .= ' ) ';
            $params = $sizes;
        } else {
            $sizestr = '';
            $params = [];
        }
        $q = "SELECT *, (ROUND(SQRT(POW(LEAST(ABS( ? - wdata.x), ABS( ? - ABS( ? - wdata.x))), 2) + POW(LEAST(ABS( ? - wdata.y), ABS( ? - ABS( ? - wdata.y))), 2)),3)) AS distance"
            . " FROM wdata, artefacts WHERE artefacts.vref = wdata.id"
            . " AND (ROUND(SQRT(POW(LEAST(ABS( ? - wdata.x), ABS( ? - ABS( ? - wdata.x))), 2) + POW(LEAST(ABS( ? - wdata.y), ABS( ? - ABS( ? - wdata.y))), 2)),3)) <= ?"
            . $sizestr
            . ' ORDER BY distance';
        $params = array_merge([$coor['x'], $coor['x'], $coor['max'], $coor['y'], $coor['y'], $coor['max'], $coor['x'], $coor['x'], $coor['max'], $coor['y'], $coor['y'], $coor['max'], $distance], $params);
        return $this->executeQuery($q, $params);
    }

    public function arteIsMine($id, $newvref, $newowner)
    {
        $this->update('artefacts', ['owner' => $newowner], 'id = ?', [$id]);
        $this->captureArtefact($id, $newvref, $newowner);
    }

    public function captureArtefact($id, $newvref, $newowner)
    {
        // get the artefact
        $currentArte = $this->getArtefactDetails($id);

        // set new active artes for new owner

        #---------first inactive large and uinque artes if this currentArte is large/unique
        if ($currentArte['size'] == 2 || $currentArte['size'] == 3) {
            $ulArts = $this->select('artefacts', '*', '`owner` = ? AND `status` = 1 AND `size` <> 1', [$newowner]);
            if (!empty($ulArts) && count($ulArts) > 0) {
                foreach ($ulArts as $art) {
                    $this->update('artefacts', ['status' => 2], 'id = ?', [$art['id']]);
                }
            }
        }
        #---------then check extra artes
        $vArts = $this->select('artefacts', '*', '`vref` = ? AND `status` = 1', [$newvref]);
        if (!empty($vArts) && count($vArts) > 0) {
            foreach ($vArts as $art) {
                $this->update('artefacts', ['status' => 2], 'id = ?', [$art['id']]);
            }
        } else {
            $uArts = $this->select('artefacts', '*', '`owner` = ? AND `status` = 1 ORDER BY conquered DESC', [$newowner]);
            if (!empty($uArts) && count($uArts) > 2) {
                for ($i = 2; $i < count($uArts); $i++) {
                    $this->update('artefacts', ['status' => 2], 'id = ?', [$uArts[$i]['id']]);
                }
            }
        }
        // set currentArte -> owner,vref,conquered,status
        $time = time();
        $this->update('artefacts', ['vref' => $newvref, 'owner' => $newowner, 'conquered' => $time, 'status' => 1], 'id = ?', [$id]);
        // set new active artes for old user
        if ($currentArte['status'] == 1) {
            #--- get olduser's active artes
            $ouaArts = $this->select('artefacts', '*', '`owner` = ? AND `status` = 1', [$currentArte['owner']]);
            $ouiArts = $this->select('artefacts', '*', '`owner` = ? AND `status` = 2 ORDER BY conquered DESC', [$currentArte['owner']]);
            if (!empty($ouaArts) && count($ouaArts) < 3 && !empty($ouiArts) && count($ouiArts) > 0) {
                $ouiaCount = count($ouiArts);
                for ($i = 0; $i < $ouiaCount; $i++) {
                    $ia = $ouiArts[$i];
                    if (count($ouaArts) < 3) {
                        $accepted = true;
                        foreach ($ouaArts as $aa) {
                            if ($ia['vref'] == $aa['vref']) {
                                $accepted = false;
                                break;
                            }
                            if (($ia['size'] == 2 || $ia['size'] == 3) && ($aa['size'] == 2 || $aa['size'] == 3)) {
                                $accepted = false;
                                break;
                            }
                        }
                        if ($accepted) {
                            $ouaArts[] = $ia;
                            $this->update('artefacts', ['status' => 1], 'id = ?', [$ia['id']]);
                        }
                    } else {
                        break;
                    }
                }
            }
        }
    }

    public function getArtefactDetails($id)
    {
        return $this->selectFirst('artefacts', '*', 'id = ?', [$id]);
    }

    public function getHeroFace($uid)
    {
        return $this->selectFirst('heroface', '*', 'uid = ?', [$uid]);
    }

    public function addHeroFace($uid, $bread, $ear, $eye, $eyebrow, $face, $hair, $mouth, $nose, $color)
    {
        return $this->insert('heroface', ['uid' => $uid, 'beard' => $bread, 'ear' => $ear, 'eye' => $eye, 'eyebrow' => $eyebrow, 'face' => $face, 'hair' => $hair, 'mouth' => $mouth, 'nose' => $nose, 'color' => $color]);
    }

    public function modifyHeroFace($uid, $column, $value)
    {
        $hash = md5("$uid" . time());
        return $this->update('heroface', [$column => $value, 'hash' => $hash], 'uid = ?', [$uid]);
    }

    public function modifyWholeHeroFace($uid, $face, $color, $hair, $ear, $eyebrow, $eye, $nose, $mouth, $beard)
    {
        $hash = md5("$uid" . time());
        return $this->update('heroface', ['face' => $face, 'color' => $color, 'hair' => $hair, 'ear' => $ear, 'eyebrow' => $eyebrow, 'eye' => $eye, 'nose' => $nose, 'mouth' => $mouth, 'beard' => $beard, 'hash' => $hash], 'uid = ?', [$uid]);
    }

    public function populateOasisUnitsLow()
    {
        $q2 = "SELECT * FROM wdata where oasistype != 0";
        $result2 = $this->query($q2);
        while ($row = $this->fetchArray($result2)) {
            $wid = $row['id'];
            $basearray = $this->getMInfo($wid);
            //each Troop is a Set for oasis type like mountains have rats spiders and snakes fields tigers elphants clay wolves so on stonger one more not so less
            switch ($basearray['oasistype']) {
                case 1:
                case 2:
                    // Oasis Random populate
                    $UP35 = rand(5, 30) * (SPEED / 10);
                    $UP36 = rand(5, 30) * (SPEED / 10);
                    $UP37 = rand(0, 30) * (SPEED / 10);
                    //+25% lumber per hour
                    $q = "UPDATE units SET u35 = u35 +  ?, u36 = u36 + ?, u37 = u37 + ? WHERE vref = ?";
                    $result = $this->query($q, [$UP35, $UP36, $UP37, $wid]);
                    break;
                case 3:
                    // Oasis Random populate
                    $UP35 = rand(5, 30) * (SPEED / 10);
                    $UP36 = rand(5, 30) * (SPEED / 10);
                    $UP37 = rand(1, 30) * (SPEED / 10);
                    $UP39 = rand(0, 10) * (SPEED / 10);
                    $fil = rand(0, 20);
                    if ($fil == 1) {
                        $UP40 = rand(0, 31) * (SPEED / 10);
                    } else {
                        $UP40 = 0;
                    }
                    //+25% lumber per hour
                    $q = "UPDATE units SET u35 = u35 + ?, u36 = u36 + ?, u37 = u37 + ?, u39 = u39 + ?, u40 = u40 + ? WHERE vref = ?";
                    $result = $this->query($q, [$UP35, $UP36, $UP37, $UP39, $UP40, $wid]);
                    break;
                case 4:
                case 5:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP32 = rand(5, 30) * (SPEED / 10);
                    $UP35 = rand(0, 25) * (SPEED / 10);
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 + ?, u32 = u32 + ?, u35 = u35 + ? WHERE vref = ?";
                    $result = $this->query($q, [$UP31, $UP32, $UP35, $wid]);
                    break;
                case 6:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP32 = rand(5, 30) * (SPEED / 10);
                    $UP35 = rand(1, 25) * (SPEED / 10);
                    $UP38 = rand(0, 15) * (SPEED / 10);
                    $fil = rand(0, 20);
                    if ($fil == 1) {
                        $UP40 = rand(0, 31) * (SPEED / 10);
                    } else {
                        $UP40 = 0;
                    }
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 + ?, u32 = u32 + ?, u35 = u35 + ?, u38 = u38 + ?, u40 = u40 + ? WHERE vref = ?";
                    $result = $this->query($q, [$UP31, $UP32, $UP35, $UP38, $UP40, $wid]);
                    break;
                case 7:
                case 8:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP32 = rand(5, 30) * (SPEED / 10);
                    $UP34 = rand(0, 25) * (SPEED / 10);
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 + ?, u32 = u32 + ?, u34 = u34 + ? WHERE vref = ?";
                    $result = $this->query($q, [$UP31, $UP32, $UP34, $wid]);
                    break;
                case 9:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP32 = rand(5, 30) * (SPEED / 10);
                    $UP34 = rand(1, 25) * (SPEED / 10);
                    $UP37 = rand(0, 15) * (SPEED / 10);
                    $fil = rand(0, 20);
                    if ($fil == 1) {
                        $UP40 = rand(0, 31) * (SPEED / 10);
                    } else {
                        $UP40 = 0;
                    }
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 + ?, u32 = u32 + ?, u34 = u34 + ?, u37 = u37 + ?, u40 = u40 + ? WHERE vref = ?";
                    $result = $this->query($q, [$UP31, $UP32, $UP34, $UP37, $UP40, $wid]);
                    break;
                case 10:
                case 11:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP33 = rand(5, 30) * (SPEED / 10);
                    $UP37 = rand(1, 25) * (SPEED / 10);
                    $UP39 = rand(0, 25) * (SPEED / 10);
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 + ?, u33 = u33 + ?, u37 = u37 + ?, u39 = u39 + ? WHERE vref = ?";
                    $result = $this->query($q, [$UP31, $UP33, $UP37, $UP39, $wid]);
                    break;
                case 12:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP33 = rand(5, 30) * (SPEED / 10);
                    $UP38 = rand(1, 25) * (SPEED / 10);
                    $UP39 = rand(0, 25) * (SPEED / 10);
                    $fil = rand(0, 20);
                    if ($fil == 1) {
                        $UP40 = rand(0, 31) * (SPEED / 10);
                    } else {
                        $UP40 = 0;
                    }
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 + ?, u33 = u33 + ?, u38 = u38 + ?, u39 = u39 + ?, u40 = u40 + ? WHERE vref = ?";
                    $result = $this->query($q, [$UP31, $UP33, $UP38, $UP39, $UP40, $wid]);
                    break;
            }
        }
    }

    public function hasBeginnerProtection($vid)
    {
        $q = "SELECT u.protect FROM users u INNER JOIN vdata v ON u.id=v.owner WHERE v.wref=?";
        $result = $this->query($q, [$vid]);
        $dbarray = $this->fetchArray($result);
        if (!empty($dbarray)) {
            if (time() < $dbarray[0]) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function addCLP($uid, $clp)
    {
        $data = ['clp' => 'clp + ?'];
        $condition = ['id' => $uid];
        return $this->update('users', $data, $condition, [$clp]);
    }

    public function sendWlcMessage($client, $owner, $topic, $message, $send)
    {
        $data = [
            'client' => $client,
            'owner' => $owner,
            'topic' => $topic,
            'message' => $message,
            'archived' => 1,
            'viewed' => 0,
            'send' => $send,
            'time' => time()
        ];
        return $this->insert('mdata', $data);
    }

    public function getLinks($id)
    {
        $fields = ['url', 'name'];
        $condition = ['userid' => $id];
        $orderBy = 'pos ASC';
        return $this->select($fields, 'links', $condition, null, $orderBy);
    }

    public function removeLinks($id, $uid)
    {
        $condition = ['id' => $id, 'userid' => $uid];
        return $this->delete('links', $condition);
    }

    public function hasFarmlist($uid)
    {
        $condition = ['owner' => $uid];
        $result = $this->selectFirst('id', 'farmlist', $condition, 'name ASC');
        return !empty($result);
    }

    public function getRaidList($id)
    {
        $condition = ['id' => $id];
        return $this->selectFirst('*', 'raidlist', $condition);
    }

    public function getAllAuction()
    {
        $condition = ['finish' => 0];
        return $this->selectFirst('*', 'auction', $condition);
    }

    public function hasVillageFarmlist($wref)
    {
        $condition = ['wref' => $wref];
        $result = $this->selectFirst('id', 'farmlist', $condition, 'wref ASC');
        return !empty($result);
    }

    public function deleteFarmList($id, $owner)
    {
        $condition = ['id' => $id, 'owner' => $owner];
        return $this->delete('farmlist', $condition);
    }

    public function deleteSlotFarm($id)
    {
        $condition = ['id' => $id];
        return $this->delete('raidlist', $condition);
    }

    public function createFarmList($wref, $owner, $name)
    {
        $data = [
            'wref' => $wref,
            'owner' => $owner,
            'name' => $name
        ];
        return $this->insert('farmlist', $data);
    }

    public function addSlotFarm($lid, $towref, $x, $y, $distance, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10)
    {
        $data = [
            'lid' => $lid,
            'towref' => $towref,
            'x' => $x,
            'y' => $y,
            'distance' => $distance,
            't1' => $t1,
            't2' => $t2,
            't3' => $t3,
            't4' => $t4,
            't5' => $t5,
            't6' => $t6,
            't7' => $t7,
            't8' => $t8,
            't9' => $t9,
            't10' => $t10
        ];
        return $this->insert('raidlist', $data);
    }

    public function editSlotFarm($eid, $lid, $wref, $x, $y, $dist, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10)
    {
        $data = [
            'lid' => $lid,
            'towref' => $wref,
            'x' => $x,
            'y' => $y,
            't1' => $t1,
            't2' => $t2,
            't3' => $t3,
            't4' => $t4,
            't5' => $t5,
            't6' => $t6,
            't7' => $t7,
            't8' => $t8,
            't9' => $t9,
            't10' => $t10
        ];
        $condition = ['id' => $eid];
        return $this->update('raidlist', $data, $condition);
    }

    public function removeOases($wref)
    {
        $data1 = [
            'conqured' => 0,
            'owner' => 3,
            'name' => UNOCCUPIEDOASES
        ];
        $condition1 = ['wref' => $wref];
        $r1 = $this->update('odata', $data1, $condition1);

        $data2 = ['occupied' => 0];
        $condition2 = ['id' => $wref];
        $r2 = $this->update('wdata', $data2, $condition2);

        return ($r1 && $r2);
    }

    public function getArrayMemberVillage($uid)
    {
        $fields = ['a.wref', 'a.name', 'a.capital', 'b.x', 'b.y'];
        $joinTables = ['vdata AS a', 'wdata AS b'];
        $joinConditions = ['b.id' => 'a.wref'];
        $condition = ['owner' => $uid];
        $orderBy = 'capital DESC, pop DESC';
        return $this->join($fields, $joinTables, $joinConditions, $condition, null, $orderBy);
    }

    public function getNoticeData($nid)
    {
        $fields = ['data'];
        $condition = ['id' => $nid];
        $result = $this->selectFirst($fields, 'ndata', $condition);
        return $result['data'];
    }

    public function getUsersNotice($uid, $ntype = -1, $viewed = -1)
    {
        $condition = ['uid' => $uid];
        if ($ntype >= 0) {
            $condition['ntype'] = $ntype;
        }
        if ($viewed >= 0) {
            $condition['viewed'] = $viewed;
        }
        return $this->select('*', 'ndata', $condition);
    }

    public function setSilver($uid, $silver, $mode)
    {
        if (!$mode) {
            $data = ['silver' => "silver - $silver"];
            $condition = ['id' => $uid];
            $this->update('users', $data, $condition);

            // Update used silver
            $data2 = ['usedsilver' => "usedsilver + $silver"];
            $this->update('users', $data2, $condition);
        } else {
            $data = ['silver' => "silver + $silver"];
            $condition = ['id' => $uid];
            $this->update('users', $data, $condition);

            // Update Add silver
            $data2 = ['Addsilver' => "Addsilver + $silver"];
            $this->update('users', $data2, $condition);
        }
    }

    public function getAuctionSilver($uid)
    {
        $condition = ['uid' => $uid, 'finish' => 0];
        return $this->select('*', 'auction', $condition);
    }

    public function delAuction($id)
    {
        $aucData = $this->getAuctionData($id);
        $usedtime = AUCTIONTIME - ($aucData['time'] - time());
        if (($usedtime < (AUCTIONTIME / 10)) && !$aucData['bids']) {
            $this->modifyHeroItem($aucData['itemid'], 'num', $aucData['num'], 1);
            $this->modifyHeroItem($aucData['itemid'], 'proc', 0, 0);
            $condition = ['id' => $id, 'finish' => 0];
            return $this->delete('auction', $condition);
        } else {
            return false;
        }
    }

    public function getAuctionData($id)
    {
        $condition = ['id' => $id];
        return $this->selectFirst('*', 'auction', $condition);
    }

    public function modifyHeroItem($id, $column, $value, $mode)
    {
        // mode=0 set; 1 add; 2 sub; 3 mul; 4 div
        switch ($mode) {
            case 0:
                $data = [$column => $value];
                break;
            case 1:
                $data = [$column => "$column + $value"];
                break;
            case 2:
                $data = [$column => "$column - $value"];
                break;
            case 3:
                $data = [$column => "$column * $value"];
                break;
            case 4:
                $data = [$column => "$column / $value"];
                break;
        }
        $condition = ['id' => $id];
        return $this->update('heroitems', $data, $condition);
    }

    public function getAuctionUser($uid)
    {
        $condition = ['owner' => $uid];
        return $this->selectFirst('auction', '*', $condition);
    }

    public function addAuction($owner, $itemid, $btype, $type, $amount)
    {
        $time = time() + AUCTIONTIME;
        $itemData = $this->getHeroItem($itemid);
        if ($amount >= $itemData['num']) {
            $amount = $itemData['num'];
            $this->modifyHeroItem($itemid, 'proc', 1, 0);
        }
        if ($amount <= 0) {
            return false;
        }
        $this->modifyHeroItem($itemid, 'num', $amount, 2);
        switch ($btype) {
            case 7:
            case 8:
            case 9:
            case 10:
            case 11:
            case 14:
                $silver = $amount;
                break;
            default:
                $silver = $amount * 100;
                break;
        }
        $data = [
            'owner' => $owner,
            'itemid' => $itemid,
            'btype' => $btype,
            'type' => $type,
            'num' => $amount,
            'uid' => 0,
            'bids' => 0,
            'silver' => $silver,
            'maxsilver' => $silver,
            'time' => $time,
            'finish' => 0
        ];
        return $this->insert('auction', $data);
    }

    public function getHeroItem($id = 0, $uid = 0, $btype = 0, $type = 0, $proc = 2)
    {
        $conditions = [];
        if ($id) {
            $conditions[] = "id = $id";
        }
        if ($uid) {
            $conditions[] = "uid = $uid";
        }
        if ($btype) {
            $conditions[] = "btype = $btype";
        }
        if ($type) {
            $conditions[] = "type = $type";
        }
        if ($proc != 2) {
            $conditions[] = "proc = $proc";
        }
        $conditionStr = implode(' AND ', $conditions);
        $q = "SELECT * FROM heroitems WHERE $conditionStr";
        $result = $this->query_return($q);
        if ($id) {
            return isset($result[0]) ? $result[0] : [];
        }
        return $result;
    }

    public function addBid($id, $uid, $silver, $maxsilver, $time)
    {
        $data = [
            'uid' => $uid,
            'silver' => $silver,
            'maxsilver' => $maxsilver,
            'bids' => 'bids + 1',
            'time' => $time
        ];
        $condition = ['id' => $id];
        return $this->update('auction', $data, $condition);
    }

    public function removeBidNotice($id)
    {
        $condition = ['id' => $id];
        return $this->delete('auction', $condition);
    }

    public function addHeroItem($uid, $btype, $type, $num)
    {
        $data = [
            'uid' => $uid,
            'btype' => $btype,
            'type' => $type,
            'num' => $num,
            'proc' => 0
        ];
        return $this->insert('heroitems', $data);
    }

    public function checkHeroItem($uid, $btype, $type = 0, $proc = 2)
    {
        $conditions = [];
        if ($uid) {
            $conditions[] = "uid = '$uid'";
        }
        if ($btype) {
            $conditions[] = "btype = '$btype'";
        }
        if ($type) {
            $conditions[] = "type = '$type'";
        }
        if ($proc != 2) {
            $conditions[] = "proc = '$proc'";
        }
        $conditionStr = implode(' AND ', $conditions);
        $q = "SELECT id, btype FROM heroitems WHERE $conditionStr";
        $result = $this->query_return($q);
        return isset($result['btype']) ? $result['id'] : false;
    }

    public function editBid($id, $maxsilver, $minsilver)
    {
        $data = [
            'maxsilver' => $maxsilver,
            'silver' => $minsilver
        ];
        $condition = ['id' => $id];
        return $this->update('auction', $data, $condition);
    }

    public function getBidData($id)
    {
        $condition = ['id' => $id];
        return $this->selectFirst('auction', '*', $condition);
    }

    public function getFLData($id)
    {
        $condition = ['id' => $id];
        return $this->selectFirst('farmlist', '*', $condition);
    }

    public function getHeroField($uid, $field)
    {
        $condition = ['uid' => $uid];
        $result = $this->selectFirst('hero', $field, $condition);
        return $result[$field] ?? null;
    }

    public function getCapBrewery($uid)
    {
        $capWref = $this->getVFH($uid);
        if ($capWref) {
            $condition = ['vref' => $capWref];
            $dbarray = $this->selectFirst('fdata', '*', $condition);
            if (!empty($dbarray)) {
                for ($i = 19; $i <= 40; $i++) {
                    if ($dbarray['f' . $i . 't'] == 35) {
                        return $dbarray['f' . $i];
                    }
                }
            }
        }
        return 0;
    }

    public function getVFH($uid)
    {
        $condition = ['owner' => $uid, 'capital' => 1];
        $result = $this->selectFirst('vdata', 'wref', $condition);
        return $result['wref'] ?? null;
    }

    public function getNotice2($id, $field)
    {
        $condition = ['id' => $id];
        $result = $this->selectFirst('ndata', $field, $condition);
        return $result[$field] ?? null;
    }

    public function addAdventure($wref, $uid, $time = 0, $dif = 0)
    {
        if ($time == 0) {
            $time = time();
        }

        // Get the last world ID
        $sql = "SELECT id FROM wdata ORDER BY id DESC LIMIT 1";
        $lastWorld = $this->selectFirst($sql)['id'];

        // Determine the adventure target world reference
        if (($wref - 10000) <= 10) {
            $w1 = rand(10, ($wref + 10000));
        } elseif (($wref + 10000) >= $lastWorld) {
            $w1 = rand(($wref - 10000), ($lastWorld - 10000));
        } else {
            $w1 = rand(($wref - 10000), ($wref + 10000));
        }

        // Insert the adventure record
        $data = [
            'wref' => $w1,
            'uid' => $uid,
            'dif' => $dif,
            'time' => $time,
            'end' => 0
        ];

        return $this->insert('adventure', $data);
    }

    public function addHero($uid)
    {
        // Obtenção do timestamp atual
        $time = time();

        // Geração de um hash a partir do timestamp
        $hash = md5($time);

        // Determinação da tribo do usuário
        $tribe = $this->getUserField($uid, 'tribe', 0);

        // Definição dos valores padrão dos atributos do herói com base na tribo
        switch ($tribe) {
            case 0:
                $cpproduction = 0;
                $speed = 7;
                $rob = 0;
                $fsperpoint = 100;
                $extraresist = 0;
                $vsnatars = 0;
                $autoregen = 10;
                $extraexpgain = 0;
                $accountmspeed = 0;
                $allymspeed = 0;
                $longwaymspeed = 0;
                $returnmspeed = 0;
                break;
            case 1:
                $cpproduction = 5;
                $speed = 6;
                $rob = 0;
                $fsperpoint = 100;
                $extraresist = 4;
                $vsnatars = 25;
                $autoregen = 20;
                $extraexpgain = 0;
                $accountmspeed = 0;
                $allymspeed = 0;
                $longwaymspeed = 0;
                $returnmspeed = 0;
                break;
            case 2:
                $cpproduction = 0;
                $speed = 8;
                $rob = 10;
                $fsperpoint = 90;
                $extraresist = 0;
                $vsnatars = 0;
                $autoregen = 10;
                $extraexpgain = 15;
                $accountmspeed = 0;
                $allymspeed = 0;
                $longwaymspeed = 0;
                $returnmspeed = 0;
                break;
            case 3:
                $cpproduction = 0;
                $speed = 10;
                $rob = 0;
                $fsperpoint = 80;
                $extraresist = 0;
                $vsnatars = 0;
                $autoregen = 10;
                $extraexpgain = 0;
                $accountmspeed = 30;
                $allymspeed = 15;
                $longwaymspeed = 25;
                $returnmspeed = 30;
                break;
            case 4:
                $cpproduction = 0;
                $speed = 7;
                $rob = 0;
                $fsperpoint = 100;
                $extraresist = 0;
                $vsnatars = 0;
                $autoregen = 10;
                $extraexpgain = 0;
                $accountmspeed = 0;
                $allymspeed = 0;
                $longwaymspeed = 0;
                $returnmspeed = 0;
                break;
            case 5:
                $cpproduction = 0;
                $speed = 7;
                $rob = 0;
                $fsperpoint = 100;
                $extraresist = 0;
                $vsnatars = 0;
                $autoregen = 10;
                $extraexpgain = 0;
                $accountmspeed = 0;
                $allymspeed = 0;
                $longwaymspeed = 0;
                $returnmspeed = 0;
                break;
            default:
                $cpproduction = 0;
                $speed = 7;
                $rob = 0;
                $fsperpoint = 100;
                $extraresist = 0;
                $vsnatars = 0;
                $autoregen = 10;
                $extraexpgain = 0;
                $accountmspeed = 0;
                $allymspeed = 0;
                $longwaymspeed = 0;
                $returnmspeed = 0;
                break;
        }

        // Montagem dos dados para inserção
        $heroData = [
            'uid' => $uid,
            'wref' => 0,
            'level' => 0,
            'speed' => $speed,
            'points' => 0,
            'experience' => 0,
            'dead' => 0,
            'health' => 100,
            'power' => 0,
            'fsperpoint' => $fsperpoint,
            'offBonus' => 0,
            'defBonus' => 0,
            'product' => 4,
            'r0' => 1,
            'autoregen' => $autoregen,
            'extraexpgain' => $extraexpgain,
            'cpproduction' => $cpproduction,
            'rob' => $rob,
            'extraresist' => $extraresist,
            'vsnatars' => $vsnatars,
            'accountmspeed' => $accountmspeed,
            'allymspeed' => $allymspeed,
            'longwaymspeed' => $longwaymspeed,
            'returnmspeed' => $returnmspeed,
            'lastupdate' => $time,
            'lastadv' => 0,
            'hash' => $hash
        ];

        return $this->insert('hero', $heroData);
    }

    public function addNewProc($uid, $npw, $nemail, $act, $mode)
    {
        $time = time();
        $data = [
            'uid' => $uid,
            'act' => $act,
            'time' => $time,
            'proc' => 0
        ];

        if (!$mode) {
            $data['npw'] = $npw;
        } else {
            $data['nemail'] = $nemail;
        }

        return $this->insert('newproc', $data);
    }

    public function checkProcExist($uid)
    {
        $conditions = [
            'uid' => $uid,
            'proc' => 0
        ];

        return !$this->selectFirst('newproc', $conditions);
    }

    public function removeProc($uid)
    {
        $conditions = ['uid' => $uid];
        return $this->delete('newproc', $conditions);
    }

    public function checkBan($uid)
    {
        $conditions = ['id' => $uid];
        $user = $this->selectFirst('users', $conditions);

        if (!empty($user) && ($user['access'] <= 1 /*|| $user['access']>=7*/)) {
            return true;
        } else {
            return false;
        }
    }

    public function getNewProc($uid)
    {
        $conditions = ['uid' => $uid];
        return $this->selectFirst('newproc', $conditions, ['npw', 'act']);
    }

    public function checkAdventure($uid, $wref, $end)
    {
        $conditions = [
            'uid' => $uid,
            'wref' => $wref,
            'end' => $end
        ];
        return $this->selectFirst('adventure', $conditions, 'id');
    }

    public function getAdventure($uid, $wref = 0, $end = 2)
    {
        $conditions = ['uid' => $uid];
        if ($wref != 0) {
            $conditions['wref'] = $wref;
        }
        if ($end != 2) {
            $conditions['end'] = $end;
        }
        return $this->selectFirst('adventure', $conditions, ['id', 'dif']);
    }

    public function editTableField($table, $field, $value, $refField, $ref)
    {
        $conditions = [$refField => $ref];
        $data = [$field => $value];
        return $this->update($table, $data, $conditions);
    }

    public function config()
    {
        return $this->selectFirst('config');
    }

    public function getAllianceDipProfile($aid, $type)
    {
        $conditions1 = ['alli1' => $aid, 'type' => $type, 'accepted' => 1];
        $conditions2 = ['alli2' => $aid, 'type' => $type, 'accepted' => 1];
        $allianceLinks = '';

        $alliances1 = $this->select('diplomacy', $conditions1, 'alli2');
        $alliances2 = $this->select('diplomacy', $conditions2, 'alli1');

        if (!empty($alliances1)) {
            foreach ($alliances1 as $row) {
                $alliance = $this->getAlliance($row['alli2']);
                $allianceLinks .= "<a href='allianz.php?aid={$alliance['id']}'>{$alliance['tag']}</a><br>";
            }
        }

        if (!empty($alliances2)) {
            foreach ($alliances2 as $row) {
                $alliance = $this->getAlliance($row['alli1']);
                $allianceLinks .= "<a href='allianz.php?aid={$alliance['id']}'>{$alliance['tag']}</a><br>";
            }
        }

        if (empty($allianceLinks)) {
            $allianceLinks = "-<br>";
        }

        return $allianceLinks;
    }

    public function getAlliance($id, $mod = 0)
    {
        if (!$id) return 0;

        switch ($mod) {
            case 0:
                $where = ['id' => $id];
                break;
            case 1:
                $where = ['name' => $id];
                break;
            case 2:
                $where = ['tag' => $id];
                break;
            default:
                return null;
        }

        return $this->selectFirst('alidata', $where, ['id', 'tag', 'desc', 'max', 'name', 'notice']);
    }

    public function canClaimArtifact($vref, $type)
    {
        $defenderFields = $this->getResourceLevel($vref);
        $attackerFields = $this->getResourceLevel($vref);

        $defCanClaim = true;
        $villageArtifact = false;
        $accountArtifact = false;

        for ($i = 19; $i <= 38; $i++) {
            if ($defenderFields['f' . $i . 't'] == 27) {
                $defCanClaim = false;
                break;
            }
        }

        for ($i = 19; $i <= 38; $i++) {
            if ($attackerFields['f' . $i . 't'] == 27) {
                $attTresuaryLevel = $attackerFields['f' . $i];
                if ($attTresuaryLevel >= 10) {
                    $villageArtifact = true;
                }
                if ($attTresuaryLevel == 20) {
                    $accountArtifact = true;
                }
                break;
            }
        }

        if ($type == 1 && $defCanClaim && $villageArtifact) {
            return true;
        } elseif (($type == 2 || $type == 3) && $defCanClaim && $accountArtifact) {
            return true;
        } else {
            return false;
        }
    }

    public function getCropProdstarv($wref)
    {
        global $bid4, $bid8, $bid9;

        $basecrop = $grainmill = $bakery = 0;
        $owner = $this->getVillageField($wref, 'owner');
        $bonus = $this->getUserField($owner, 'b4', 0);

        $buildarray = $this->getResourceLevel($wref);
        $cropholder = [];
        $crop = 0;

        // Percorre os edifícios do campo para calcular o total de produção de trigo
        foreach ($buildarray as $field => $value) {
            if (strpos($field, 'f') === 0 && is_numeric(substr($field, 1))) {
                $fieldType = $value['t'];
                if ($fieldType == 4) {
                    $cropholder[] = $field;
                    $basecrop += $bid4[$value]['prod'];
                } elseif ($fieldType == 8) {
                    $grainmill = $value;
                } elseif ($fieldType == 9) {
                    $bakery = $value;
                }
            }
        }

        // Calcula o bônus de produção de trigo dos oásis
        $cropo = 0;
        $oases = $this->query_return("SELECT type FROM `odata` WHERE conqured = $wref");
        foreach ($oases as $oasis) {
            switch ($oasis['type']) {
                case 3:
                case 6:
                case 9:
                case 10:
                case 11:
                    $cropo += 1;
                    break;
                case 12:
                    $cropo += 2;
                    break;
            }
        }

        // Aplica o bônus de produção dos moinhos de grãos e padarias
        if ($grainmill || $bakery) {
            $crop += ($basecrop / 100) * ($bid8[$grainmill]['attri'] + $bid9[$bakery]['attri']);
        }

        // Aplica o bônus de produção se o jogador tiver um bônus ativo
        if ($bonus > time()) {
            $crop *= 1.25;
        }

        // Aplica a velocidade do jogo à produção de trigo
        $crop *= SPEED;

        return $crop;
    }

    public function getNatarsProgress()
    {
        return $this->selectFirst("natarsprogress");
    }

    public function setNatarsProgress($field, $value)
    {
        return $this->update("natarsprogress", [$field => $value], "");
    }

    public function getNatarsCapital()
    {
        return $this->selectFirst("vdata", "`wref`", "owner = 2 AND capital = 1", [], "created ASC");
    }

    public function getNatarsWWVillages()
    {
        return $this->select("vdata", "`owner`", "owner = 2 AND name = 'WW Village'", [], "created ASC");
    }

    public function addNatarsVillage($wid, $uid, $username, $capital)
    {
        $total = $this->count("vdata", "*", "owner = '$uid'");
        $vname = sprintf("[%05d] Natars", $total + 1);
        $time = time();
        $data = [
            'wref' => $wid,
            'owner' => $uid,
            'name' => $vname,
            'capital' => $capital,
            'pop' => 2,
            'cp' => 1,
            'celebration' => 0,
            'wood' => 780,
            'clay' => 780,
            'iron' => 780,
            'maxstore' => 800,
            'crop' => 780,
            'maxcrop' => 800,
            'lastupdate' => $time,
            'created' => $time,
            'natar' => 1
        ];
        return $this->insert("vdata", $data);
    }

    public function instantTrain($vref)
    {
        $count = $this->count("training", "*", "vref = '$vref'");
        $this->update("training", ['commence' => 0, 'eachtime' => 1, 'endat' => 0, 'timestamp' => 0], "vref = '$vref'");
        return $count;
    }

    public function hasWinner()
    {
        return $this->count("fdata", "*", "f99 = '100' AND f99t = '40'") > 0;
    }

    public function getVillageActiveArte($vref)
    {
        $currentTime = time();
        $conqueredThreshold = $currentTime - max(86400 / SPEED, 600);
        return $this->select("artefacts", "*", "`vref` = :vref AND `status` = 1 AND `conquered` <= :conquered", [":vref" => $vref, ":conquered" => $conqueredThreshold]);
    }

    public function getAccountActiveArte($owner)
    {
        $currentTime = time();
        $conqueredThreshold = $currentTime - max(86400 / SPEED, 600);
        return $this->select("artefacts", "*", "`owner` = :owner AND `status` = 1 AND `conquered` <= :conquered", [":owner" => $owner, ":conquered" => $conqueredThreshold]);
    }

    public function getArtEffMSpeed($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 4);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public function getArteEffectByType($wref, $type)
    {
        $artEff = 0;
        $this->updateFoolArtes();
        $vinfo = $this->getVillage($wref);
        if (!empty($vinfo) && isset($vinfo['owner'])) {
            $owner = $vinfo['owner'];
            $currentTime = time();
            $conqueredThreshold = $currentTime - max(86400 / SPEED, 600);
            $result = $this->select("artefacts", "`vref`, `effect`, `aoe`", "`owner` = :owner AND `effecttype` = :type AND `status` = 1 AND `conquered` <= :conquered", [":owner" => $owner, ":type" => $type, ":conquered" => $conqueredThreshold], "conquered DESC");
            if (!empty($result) && count($result) > 0) {
                $i = 0;
                foreach ($result as $r) {
                    if ($r['vref'] == $wref) {
                        return $r['effect'];
                    }
                    if ($r['aoe'] == 3) {
                        return $r['effect'];
                    }
                    $i += 1;
                    if ($i >= 3) break;
                }
            }
        }
        return $artEff;
    }

    public function updateFoolArtes()
    {
        $currentTime = time();
        $conqueredThreshold = $currentTime - max(86400 / SPEED, 600);

        $sql = "SELECT `id`,`size` FROM artefacts WHERE `type` = 3 AND `status` = 1 AND `conquered` <= :conqueredThreshold AND `lastupdate` <= :currentTime";
        $params = [":conqueredThreshold" => $conqueredThreshold, ":currentTime" => $currentTime];

        $result = $this->executeQuery($sql, $params);

        if (!empty($result)) {
            foreach ($result as $r) {
                $effecttype = rand(3, 9);
                if ($effecttype == 3) $effecttype = 2;
                $aoerand = rand(1, 100);
                if ($aoerand <= 75) {
                    $aoe = 1;
                } elseif ($aoerand <= 95) {
                    $aoe = 2;
                } else {
                    $aoe = 3;
                }

                switch ($effecttype) {
                    case 2:
                        $effect = ($r['size'] == 1) ? rand(100, 500) / 100 : rand(100, 1000) / 100;
                        break;
                    case 4:
                        $effect = ($r['size'] == 1) ? rand(100, 300) / 100 : rand(100, 600) / 100;
                        break;
                    case 5:
                        $effect = ($r['size'] == 1) ? rand(100, 1000) / 100 : rand(100, 2000) / 100;
                        break;
                    case 6:
                        $effect = ($r['size'] == 1) ? rand(50, 100) / 100 : rand(25, 100) / 100;
                        break;
                    case 7:
                        $effect = ($r['size'] == 1) ? rand(100, 50000) / 100 : rand(100, 100000) / 100;
                        break;
                    case 8:
                        $effect = ($r['size'] == 1) ? rand(50, 100) / 100 : rand(25, 100) / 100;
                        break;
                    case 9:
                        $effect = ($r['size'] == 1) ? 1 : 0;
                        break;
                }

                if ($r['size'] == 1 && rand(1, 100) <= 50) {
                    $effect = 1 / $effect;
                }

                $updateSql = "UPDATE artefacts SET `effecttype` = :effecttype, `effect` = :effect, `aoe` = :aoe WHERE `id` = :id";
                $updateParams = [":effecttype" => $effecttype, ":effect" => $effect, ":aoe" => $aoe, ":id" => $r['id']];

                $this->executeQuery($updateSql, $updateParams);
            }
        }
    }

    public function getArtEffDiet($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 6);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public function getArtEffGrt($wref)
    {
        $artEff = 0;
        $res = $this->getArteEffectByType($wref, 9);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public function getArtEffArch($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 2);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public function getArtEffSpy($wref)
    {
        $artEff = 0;
        $res = $this->getArteEffectByType($wref, 5);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public function getArtEffTrain($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 8);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public function getArtEffConf($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 7);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public function getArtEffBP($wref)
    {
        $artEff = 0;
        $village = $this->getVillage($wref);
        $owner = $village['owner'];

        $conditions = ["owner" => $owner, "effecttype" => 11, "status" => 1, "conquered[<=]" => time() - max(86400 / SPEED, 600)];
        $result = $this->select("artefacts", "COUNT(*) as count", $conditions);

        if ($result['count'] > 0) {
            $artEff = 1;
        }

        return $artEff;
    }

    public function getArtEffAllyBP($uid)
    {
        $artEff = 0;
        $userAlli = $this->getUserField($uid, 'alliance', 0);
        $q = 'SELECT `alli1`,`alli2` FROM diplomacy WHERE alli1 = :userAlli OR alli2 = :userAlli AND accepted <> 0';
        $params = [":userAlli" => $userAlli];
        $diplos = $this->executeQuery($q, $params);

        if (!empty($diplos) && count($diplos) > 0) {
            $alliances = [];
            foreach ($diplos as $ds) {
                $alliances[] = $ds['alli1'];
                $alliances[] = $ds['alli2'];
            }
            $alliances = array_unique($alliances);
            $allianceStr = implode(',', $alliances);
            $q = 'SELECT `id` FROM users WHERE alliance IN (' . $allianceStr . ') AND id <> :uid';
            $params = [":uid" => $uid];
            $mate = $this->executeQuery($q, $params);

            if (!empty($mate) && count($mate) > 0) {
                $mateIds = [];
                foreach ($mate as $ms) {
                    $mateIds[] = $ms['id'];
                }
                $mateStr = implode(',', $mateIds);
                $q = 'SELECT `id` FROM artefacts WHERE `owner` IN (' . $mateStr . ') AND `effecttype` = 11 AND `status` = 1 AND `conquered` <= :conqueredThreshold ORDER BY `conquered` DESC';
                $result = $this->executeQuery($q, $params);

                if (!empty($result) && count($result) > 0) {
                    return 1;
                }
            }
        }
        return $artEff;
    }

    public function modifyExtraVillage($wid, $column, $value)
    {
        return $this->update('vdata', [$column => "$column + :value"], ['wref' => $wid], [':value' => $value]);
    }

    public function modifyFieldLevel($wid, $field, $level, $mode)
    {
        $columnName = 'f' . $field;
        $operation = $mode ? '+' : '-';
        $values = [$columnName => "$columnName $operation :level"];

        return $this->update('fdata', $values, ['vref' => $wid], [':level' => $level]);
    }

    public function modifyFieldType($wid, $field, $type)
    {
        return $this->update('fdata', ['f' . $field . 't' => $type], ['vref' => $wid]);
    }

    public function resendAct($mail)
    {
        return $this->select('users', '*', ['email' => $mail]);
    }

    public function changemail($mail, $id)
    {
        return $this->update('users', ['email' => $mail], ['id' => $id]);
    }

    public function register2($username, $password, $email, $act, $activateat)
    {
        $time = $_SERVER['REQUEST_TIME'];
        if (strtotime(START_TIME) > $_SERVER['REQUEST_TIME']) {
            $time = strtotime(START_TIME);
        }
        $timep = ($time + PROTECTION);
        $rand = rand(8900, 9000);

        $data = [
            'username' => $username,
            'password' => $password,
            'access' => USER,
            'email' => $email,
            'timestamp' => $time,
            'act' => $act,
            'protect' => $timep,
            'fquest' => '0,0,0,0,0,0,0,0,0,0,0',
            'clp' => $rand,
            'cp' => 1,
            'reg2' => 1,
            'activateat' => $activateat
        ];

        return $this->insert('users', $data) ? $this->getLastInsertId() : null;
    }

    public function checkname($id)
    {
        return $this->selectFirst('users', ['username', 'email'], ['id' => $id]);
    }

    public function settribe($tribe, $id)
    {
        return $this->update('users', ['tribe' => $tribe], ['id' => $id, 'reg2' => 1]);
    }

    public function checkreg($uid)
    {
        return $this->selectFirst('users', 'reg2', ['id' => $uid]);
    }

    public function checkReg2($name)
    {
        return $this->selectFirst('users', 'reg2', ['username' => $name]);
    }

    public function checkID($name)
    {
        return $this->selectFirst('users', 'id', ['username' => $name]);
    }

    public function setReg2($id)
    {
        return $this->update('users', ['reg2' => 0], ['id' => $id, 'reg2' => 1]);
    }

    public function getNotice5($uid)
    {
        return $this->select('ndata', 'id', ['uid' => $uid, 'viewed' => 0], 'time DESC');
    }

    public function setRef($id, $name)
    {
        $this->insert('reference', ['id' => $id, 'name' => $name]);
        return $this->getLastInsertId();
    }

    public function getAttackCasualties($time)
    {
        $result = $this->select('general', 'time, casualties', ['shown' => 1]);
        $casualties = 0;

        foreach ($result as $general) {
            if (date("j. M", $time) == date("j. M", strtotime($general['time']))) {
                $casualties += $general['casualties'];
            }
        }

        return $casualties;
    }


    public function getAttackByDate($time)
    {
        $result = $this->select('general', ['time'], ['shown' => 1]);
        $attack = 0;

        foreach ($result as $general) {
            if (date("j. M", $time) == date("j. M", strtotime($general['time']))) {
                $attack += 1;
            }
        }

        return $attack * 100;
    }


    public function getStatsinfo($uid, $time, $inf)
    {
        $result = $this->select('stats', [$inf, 'time'], ['owner' => $uid]);
        $t = 0;

        if ($inf == 'rank') {
            foreach ($result as $user) {
                if (date("j. M", $time) == date("j. M", strtotime($user['time']))) {
                    $t = $user[$inf];
                    break;
                }
            }
        } else {
            foreach ($result as $user) {
                if (date("j. M", $time) == date("j. M", strtotime($user['time']))) {
                    $t += $user[$inf];
                }
            }
        }

        return $t;
    }

    public function modifyHero2($column, $value, $uid, $mode)
    {
        switch ($mode) {
            case 1:
                $data = [$column => "$column + :value"];
                break;
            case 2:
                $data = [$column => "$column - :value"];
                break;
            default:
                $data = [$column => ":value"];
        }
        return $this->update('hero', $data, ['uid' => $uid], [":value" => $value]);
    }

    public function createTradeRoute($uid, $wid, $from, $r1, $r2, $r3, $r4, $start, $deliveries, $merchant, $time)
    {
        $this->update('users', ['gold' => 'gold - 2'], ['id' => $uid]);

        $data = [
            'uid' => $uid,
            'wid' => $wid,
            'from' => $from,
            'r1' => $r1,
            'r2' => $r2,
            'r3' => $r3,
            'r4' => $r4,
            'start' => $start,
            'deliveries' => $deliveries,
            'merchant' => $merchant,
            'time' => $time
        ];

        return $this->insert('route', $data);
    }

    public function getTradeRoute($uid)
    {
        return $this->select('route', ['uid' => $uid], 'timestamp ASC');
    }

    public function getTradeRoute2($id)
    {
        return $this->selectFirst('route', '*', ['id' => $id]);
    }

    public function getTradeRouteUid($id)
    {
        $routeData = $this->selectFirst('route', 'uid', ['id' => $id]);
        return $routeData['uid'];
    }

    public function editTradeRoute($id, $column, $value, $mode)
    {
        if (!$mode) {
            return $this->update('route', [$column => $value], "id = :id", [":id" => $id]);
        } else {
            return $this->update('route', [$column => "$column + :value"], "id = :id", [":id" => $id, ":value" => $value]);
        }
    }

    public function deleteTradeRoute($id)
    {
        return $this->delete('route', ['id' => $id]);
    }

    public function getHeroData($uid)
    {
        return $this->selectFirst("hero", "*", "uid = :uid", [":uid" => $uid]);
    }

    public function getHeroData2($uid)
    {
        return $this->selectFirst("hero", "heroid", "dead = 0 AND uid = :uid", [":uid" => $uid]);
    }

    public function getHeroInVillid($uid, $mode)
    {
        $villageData = $this->select("vdata", "wref, name", "owner = :uid", [":uid" => $uid]);
        $name = null;

        foreach ($villageData as $row) {
            $unitHero = $this->selectFirst("units", "hero", "vref = :wref", [":wref" => $row['wref']]);
            if ($unitHero['hero'] == 1) {
                $name = $mode ? $row['name'] : $row['wref'];
                break;
            }
        }

        return $name;
    }

}
