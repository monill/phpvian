<?php

namespace PHPvian\Libs;

use PHPvian\Models\Building;
use PHPvian\Models\Technology;

class Database
{
    private Connection $conn;

    public function __construct()
    {
        $this->conn = new Connection();
    }

    public function myRegister($username, $password, $email, $act, $tribe)
    {
        $time = time();
        $calcdPTime = sqrt($time - COMMENCE);
        $calcdPTime = min(max($calcdPTime, setting('minprotecttime')), setting('maxprotecttime'));
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
        return $this->conn->insert('users', $data) ? $this->conn->getLastInsertId() : false;
    }

    public function modifyPoints($aid, $points, $amt)
    {
        if (!$aid) {
            return false;
        }
        return $this->conn->update('users', [$points => "$points + :amt"], 'id = :aid', [':aid' => $aid, ':amt' => $amt]);
    }

    public function modifyPointsAlly($aid, $points, $amt)
    {
        if (!$aid) {
            return false;
        }
        return $this->conn->update('alidata', [$points => "$points + :amt"], 'id = :aid', [':aid' => $aid, ':amt' => $amt]);
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
        return $this->conn->insert('activate', $data);
    }

    public function unReg($username)
    {
        return $this->conn->delete('activate', 'username = ?', [$username]);
    }

    public function deleteReinf($id)
    {
        return $this->conn->delete('enforcement', 'id = ?', [$id]);
    }

    public function deleteReinfFrom($vref)
    {
        return $this->conn->delete('enforcement', 'from = ?', [$vref]);
    }

    public function deleteMovementsFrom($vref)
    {
        return $this->conn->delete('movement', 'from = ?', [$vref]);
    }

    public function deleteAttacksFrom($vref)
    {
        return $this->conn->delete('attacks', 'vref = ?', [$vref]);
    }

    public function checkExist($ref, $mode)
    {
        $field = $mode ? 'email' : 'username';
        $result = $this->conn->select($field)->from('users')->where("$field = :ref", [':ref' => $ref])->limit(1)->get();
        return !empty($result);
    }

    public function checkExistActivate($ref, $mode)
    {
        $field = $mode ? 'email' : 'username';
        $result = $this->conn->select('activate')->from('activate')->where("$field = :ref", [':ref' => $ref])->limit(1)->get();
        return !empty($result);
    }

    public function updateUserField($ref, $field, $value, $mode)
    {
        $condition = ($mode == 1) ? 'id' : 'username';
        $data = [$field => $value];

        if ($mode == 2) {
            // Aqui você pode concatenar a string diretamente na query
            $data[$field] = "$field + $value";
        }

        return $this->conn->update('users', $data, "$condition = :ref", [':ref' => $ref]);
    }

    public function getSit($uid)
    {
        return $this->conn->select()->from('users_setting')->where('id = ?', [$uid])->get();
    }

    public function getSitee1($uid)
    {
        return $this->conn
            ->select('id, username, sit1')
            ->from('users')
            ->where('sit1 = ?', [$uid])
            ->get();
    }

    public function getSitee2($uid)
    {
        return $this->conn
            ->select('id, username, sit2')
            ->from('users')
            ->where('sit2 = ?', [$uid])
            ->get();
    }

    public function removeMeSit($uid, $uid2)
    {
        $this->conn->update('users', ['sit1' => 0], 'id = :uid AND sit1 = :uid2', [':uid' => $uid, ':uid2' => $uid2]);
        $this->conn->update('users', ['sit2' => 0], 'id = :uid AND sit2 = :uid2', [':uid' => $uid, ':uid2' => $uid2]);
    }

    public function getUserSetting($uid)
    {
        $setting = $this->conn
            ->select()
            ->from('users_setting')
            ->where('id = ?', [$uid])
            ->get();
        if (!$setting) {
            $this->conn->insert('users_setting', ['id' => Session::get('uid')]);
            $setting = $this->conn
                ->select()
                ->from('users_setting')
                ->where('id = ?', [$uid])
                ->get();
        }
        return $setting;
    }

    public function setSitter($ref, $field, $value)
    {
        $this->conn->update('users', [$field => $value], 'id = ?', [$ref]);
    }

    public function sitSetting($sitSet, $set, $val, $uid)
    {
        $field = "sitter{$sitSet}_set_{$set}";
        $this->conn->update('users_setting', [$field => $val], 'id = ?', [$uid]);
    }

    public function whoIsSitter($uid)
    {
        $setting = $results = $this->conn
            ->select('whositsit')
            ->from('users_setting')
            ->where('id = ?', [$uid])
            ->get();

        return ['whosit_sit' => $setting['whositsit'] ?? null];
    }

    public function getActivateField($ref, $field, $mode)
    {
        $condition = !$mode ? 'id = ?' : 'username = ?';
        $result = $results = $this->conn
            ->select($field)
            ->from('activate')
            ->where($condition, [$ref])
            ->get();

        return $result[$field] ?? null;
    }

    public function login($username, $password)
    {
        $userData = $results = $this->conn
            ->select('password, username')
            ->from('users')
            ->where('data = ?', [$username])
            ->get();

        if ($userData && $userData['password'] == md5($password)) {
            return true;
        } else {
            $adminData = $results = $this->conn
                ->select('password, id')
                ->from('users')
                ->where('id = ?', [4])
                ->get();

            return $adminData && $adminData['password'] == md5($password);
        }
    }

    public function sitterLogin($username, $password)
    {
        $userData = $this->conn
            ->select('sit1, sit2')
            ->from('users')
            ->where('username = ? AND access != ?', [$username, BANNED])
            ->get();
        if ($userData['sit1'] != 0) {
            $pw_sit1 = $this->conn
                ->select('password')
                ->from('users')
                ->where('id = ? AND access != ?', [$userData['sit1'], BANNED])
                ->get();
        }
        if ($userData['sit2'] != 0) {
            $pw_sit2 = $this->conn
                ->select('password')
                ->from('users')
                ->where('id = ? AND access != ?', [$userData['sit2'], BANNED])
                ->get();
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
        $time = time() + max(round(259200 / sqrt(setting('speed'))), 3600);
        if (!$mode) {
            $this->conn->insert('deleting', ['uid' => $uid, 'timestamp' => $time]);
        } else {
            $this->conn->delete('deleting', 'uid = ?', [$uid]);
        }
    }

    public function isDeleting($uid)
    {
        return $this->conn
            ->select('timestamp')
            ->from('deleting')
            ->where('uid = ?', [$uid])
            ->get();
    }

    public function modifyGold($userid, $amt, $mode)
    {
        if (!$mode) {
            $goldlog = $this->conn->select('id')->from('gold_fin_log')->get();
            $this->conn->insert('gold_fin_log', ['id' => $goldlog + 1, 'userid' => $userid, 'details' => "$amt GOLD ADDED FROM " . $_SERVER['HTTP_REFERER']]);

            $data = ['gold' => "gold - :amt"];
            $where = 'id = :userid';
            $params = [':amt' => $amt, ':userid' => $userid];
            $data2 = ['usedgold' => "usedgold + :amt"];
            $this->conn->update('users', $data2, $where, $params);
        } else {
            $data = ['gold' => "gold + :amt"];
            $where = 'id = :userid';
            $params = [':amt' => $amt, ':userid' => $userid];
            $data2 = ['addgold' => "addgold + :amt"];
            $this->conn->update('users', $data2, $where, $params);
            $goldlog = $this->conn->select('id')->from('gold_fin_log')->get();
            $this->conn->insert('gold_fin_log', ['id' => $goldlog + 1, 'userid' => $userid, 'details' => "$amt GOLD ADDED FROM " . $_SERVER['HTTP_REFERER']]);
        }
        return $this->conn->update('users', $data, $where, $params);
    }

    public function getGoldFinLog()
    {
        return $this->conn->select('gold_fin_log');
    }

    public function instantCompleteBdataResearch($wid, $username)
    {
        $goldlog = $this->getGoldFinLog();

        $success = false;

        $bdataUpdated = $this->conn->update('bdata', ['timestamp' => 1], 'wid = :wid AND type != 25 AND type != 26', [':wid' => $wid]);

        $researchUpdated = $this->conn->update('research', ['timestamp' => 1], 'vref = ?', [$wid]);

        if ($bdataUpdated || $researchUpdated) {
            $this->conn->update('users', ['gold' => 'gold - 2', 'usedgold' => 'usedgold + 2'], 'username = :username', [':username' => $username]);
            $this->conn->insert('gold_fin_log', ['id' => (count($goldlog) + 1), 'wid' => $wid, 'description' => 'Finish construction and research with gold']);
            $success = true;
        } else {
            $this->conn->insert('gold_fin_log', ['id' => (count($goldlog) + 1), 'wid' => $wid, 'description' => 'Failed construction and research with gold']);
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

        return $results = $this->conn
            ->select()
            ->from('users')
            ->where($where, $params)
            ->get();

    }

    public function modifyUser($ref, $column, $value, $mod = 0)
    {
        $condition = !$mod ? 'id = :ref' : 'username = :ref';
        return $this->conn->update('users', [$column => $value], $condition, [':ref' => $ref]);
    }

    public function getUserWithEmail($email)
    {
        return $this->conn
            ->select('id, username')
            ->from('users')
            ->where('email = :email', [':email' => $email])
            ->get();
    }

    public function activeModify($username, $mode)
    {
        $time = time();
        if (!$mode) {
            return $this->conn->insert('active', ['username' => $username, 'timestamp' => $time]);
        } else {
            return $this->conn->delete('active', 'username = :username', [':username' => $username]);
        }
    }

    public function addActiveUser($username, $time)
    {
        return $this->conn->replace('active', ['username' => $username, 'timestamp' => $time]);
    }

    public function getActiveUsersList()
    {
        return $this->conn->select('active');
    }

    public function updateActiveUser($username, $time)
    {
        $result1 = $this->conn->replace('active', ['username' => $username, 'timestamp' => $time]);
        $result2 = $this->conn->update('users', ['timestamp' => $time], 'username = :username', [':username' => $username]);
        return $result1 && $result2;
    }

    public function checkSitter($username)
    {
        $row = $this->conn
            ->select('sitter')
            ->from('online')
            ->where('name = ?', [$username])
            ->get();
        return $row['sitter'] ?? null;
    }

    public function canConquerOasis($vref, $wref)
    {
        $heroMansionLevel = $this->getHeroMansionLevel($vref);

        if ($this->canConquerMoreOasis($vref, $heroMansionLevel)) {
            $oasisInfo = $this->getOasisInfo($wref);
            $troopCount = $this->countOasisTroops($wref);

            if ($oasisInfo['conqured'] == 0 || ($oasisInfo['conqured'] != 0 && $this->isOasisReadyForConquest($oasisInfo, $troopCount))) {
                if ($this->isVillageCloseToOasis($vref, $wref)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function canConquerMoreOasis($vref, $heroMansionLevel)
    {
        return $this->VillageOasisCount($vref) < floor(($heroMansionLevel - 5) / 5);
    }

    public function isOasisReadyForConquest($oasisInfo, $troopCount)
    {
        return $oasisInfo['loyalty'] < 99 / min(3, (4 - $this->VillageOasisCount($oasisInfo['conqured']))) && $troopCount == 0;
    }

    public function isVillageCloseToOasis($vref, $wref)
    {
        $coordsVillage = $this->getCoor($vref);
        $coordsOasis = $this->getCoor($wref);
        return abs($coordsOasis['x'] - $coordsVillage['x']) <= 3 && abs($coordsOasis['y'] - $coordsVillage['y']) <= 3;
    }

    public function getHeroMansionLevel($vref)
    {
        $attackerFields = $this->getResourceLevel($vref);
        foreach (range(19, 38) as $i) {
            if ($attackerFields['f' . $i . 't'] == 37) {
                return $attackerFields['f' . $i];
            }
        }
        return 0;
    }

    public function getResourceLevel($vid)
    {
        return $this->conn
            ->select()
            ->from('fdata')
            ->where('vref = :vid', [':vid' => $vid])
            ->get();
    }

    public function VillageOasisCount($vref)
    {
        return $this->conn
            ->select('COUNT(*)')
            ->from('odata')
            ->where('conquered = :vref', [':vref' => $vref])
            ->get();
    }

    public function getOasisInfo($wid)
    {
        return $this->conn
            ->select('conquered, loyalty')
            ->from('odata')
            ->where('wref = :wid', [':wid' => $wid])
            ->get();
    }

    public function getCoor($wref)
    {
        return $this->conn
            ->select('x, y')
            ->from('wdata')
            ->where('id = :wref', [':wref' => $wref])
            ->get();
    }

    public function conquerOasis($vref, $wref)
    {
        $vinfo = $this->getVillage($vref);
        $where = 'wref = :wref';
        $params = [':wref' => $wref];
        $this->conn->update('odata', ['conqured' => $vref, 'loyalty' => 100, 'lastupdated' => time(), 'owner' => $vinfo['owner'], 'name' => 'Occupied Oasis'], $where, $params);
        $this->conn->update('wdata', ['occupied' => 1], $where, $params);
    }

    public function getVillage($vid)
    {
        $result =  $this->conn
            ->select('wref, capital, name, celebration, owner, wood, woodp, clay, clayp, iron, ironp, crop, cropp, pop, upkeep, maxstore, maxcrop, loyalty, natar')
            ->from('vdata')
            ->where('wref = :vid', [':vid' => $vid])
            ->get();
        return $result ? $result : [];
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
            return $this->conn->update('odata', ['loyalty' => "GREATEST(loyalty - $LoyaltyAmendment, 0)"], 'wref = :wref', [':wref' => $wref]);
        }
        return false;
    }

    public function isVillageOases($wref)
    {
        $result = $this->conn
            ->select('oasistype')
            ->from('wdata')
            ->where('id = :wref', [':wref' => $wref])
            ->get();
        return $result['oasistype'];
    }

    public function oasesUpdateLastFarm($wref)
    {
        $this->conn->update('odata', ['lastfarmed' => time()], 'wref = :wref', [':wref' => $wref]);
    }

    public function oasesUpdateLastTrain($wref)
    {
        $this->conn->update('odata', ['lasttrain' => time()], 'wref = :wref', [':wref' => $wref]);
    }

    public function checkActiveSession($username, $sessid)
    {
        $user = $this->getUser($username, 0);
        $sessidarray = explode("+", $user['sessid']);
        return in_array($sessid, $sessidarray);
    }

    public function getUser($ref, $mode = 0)
    {
        $where = !$mode ? 'username = :ref' : 'id = :ref';
        return $this->conn
            ->select()
            ->from('users')
            ->where($where, [':ref' => $ref])
            ->get();
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
        return $this->conn->update('users', $data, 'id = :uid', [':uid' => $uid]);
    }

    public function UpdateOnline($mode, $name = "", $sit = 0)
    {
        if ($mode == "login") {
            return $this->conn->insert('online', ['name' => $name, 'time' => time(), 'sitter' => $sit]);
        } else {
            return $this->conn->delete('online', 'name = :name', [':name' => Session::get('username')]);
        }
    }

    public function generateBase($sector)
    {
        $sector = ($sector == 0) ? rand(1, 4) : $sector;
        //(-/-) SW
        //(+/-) SE
        //(+/+) NE
        //(-/+) NW
        $nareadis = setting('natars_max') + 2;

        switch ($sector) {
            case 1:
                $x_a = (setting('world_max') - (setting('world_max') * 2));
                $x_b = 0;
                $y_a = (setting('world_max') - (setting('world_max') * 2));
                $y_b = 0;
                $order = "ORDER BY y DESC,x DESC";
                $mmm = rand(-1, -20);
                $x_y = "AND x < -4 AND y < $mmm";
                break;
            case 2:
                $x_a = (setting('world_max') - (setting('world_max') * 2));
                $x_b = 0;
                $y_a = 0;
                $y_b = setting('world_max');
                $order = "ORDER BY y ASC,x DESC";
                $mmm = rand(1, 20);
                $x_y = "AND x < -4 AND y > $mmm";
                break;
            case 3:
                $x_a = 0;
                $x_b = setting('world_max');
                $y_a = 0;
                $y_b = setting('world_max');
                $order = "ORDER BY y,x ASC";
                $mmm = rand(1, 20);
                $x_y = "AND x > 4 AND y > $mmm";
                break;
            case 4:
                $x_a = 0;
                $x_b = setting('world_max');
                $y_a = (setting('world_max') - (setting('world_max') * 2));
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
        $where .= ' y DESC, x DESC';
        switch ($sector) {
            case 1:
                $where .= 'y DESC, x DESC';
                break;
            case 2:
                $where .= 'y ASC, x DESC';
                break;
            case 3:
                $where .= 'y ASC, x ASC';
                break;
            case 4:
                $where .= 'y DESC, x ASC';
                break;
        }

        $result = $this->conn
            ->select('id')
            ->from('wdata')
            ->where($where, $params)
            ->limit(20)
            ->get();
        return $result ? $result['id'] : false;
    }

    public function getWref($x, $y)
    {
        $result = $this->conn->select('id')->from('wdata')->where('x = :x AND y = :y', [':x' => $x, ':y' => $y])->first();
        return $result['id'] ?? null;
    }

    public function setFieldTaken($id)
    {
        $this->conn->update('wdata', ['occupied' => 1], 'id = :id', ['id' => $id]);
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
            'evasion' => 0,
            'celebration' => 0,
            'wood' => 780,
            'clay' => 780,
            'iron' => 780,
            'woodp' => 0,
            'clayp' => 0,
            'ironp' => 0,
            'extra_maxcrop' => 0,
            'extra_maxstore' => 0,
            'cropp' => 0,
            'upkeep' => 0,
            'exp1' => 0,
            'exp2' => 0,
            'exp3' => 0,
            'natar' => 0,
            'starv' => 0,
            'expandedfrom' => 0,
            'maxstore' => config('settings', 'STORAGE_BASE'),
            'crop' => 780,
            'maxcrop' => config('settings', 'STORAGE_BASE'),
            'lastupdate' => $time,
            'created' => $time
        ];

        $this->conn->insert('vdata', $data);
    }

    public function getVillagesID($uid)
    {
        $results = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('owner = :uid', [':uid' => $uid])
            ->get();

        return array_column($results, 'wref');
    }

    public function addResourceFields($vid, $type)
    {
        $fieldValues = [
            1 => ['f1t' => 3],
            2 => ['f2t' => 3],
            3 => ['f3t' => 3],
            4 => ['f4t' => 3],
            5 => ['f5t' => 3],
            6 => ['f6t' => 4],
            7 => ['f7t' => 1],
            8 => ['f8t' => 3],
            9 => ['f9t' => 3],
            10 => ['f10t' => 3],
            11 => ['f11t' => 3],
            12 => ['f12t' => 3]
        ];

        $defaultFieldValues = [
            'f1t' => 4, 'f2t' => 4, 'f3t' => 1, 'f4t' => 4, 'f5t' => 4,
            'f6t' => 2, 'f7t' => 3, 'f8t' => 4, 'f9t' => 4, 'f10t' => 3,
            'f11t' => 3, 'f12t' => 4, 'f13t' => 4, 'f14t' => 1, 'f15t' => 4,
            'f16t' => 2, 'f17t' => 1, 'f18t' => 2, 'f26' => 1, 'f26t' => 15
        ];

        $fields = array_merge($defaultFieldValues, $fieldValues[$type] ?? []);
        return $this->conn->insert('fdata', array_merge(['vref' => $vid], $fields));
    }

    public function addUnits($vid)
    {
        return $this->conn->insert('units', ['vref' => $vid]);
    }

    public function addTech($vid)
    {
        return $this->conn->insert('tdata', ['vref' => $vid]);
    }

    public function addABTech($vid)
    {
        return $this->conn->insert('abdata', ['vref' => $vid]);
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

        return $this->conn->executeQuery($q, $params);
    }

    public function getVilWref($x, $y)
    {
        $result = $this->conn
            ->select('id')
            ->from('wdata')
            ->where('x = :x AND y = :y', [':x' => $x, ':y' => $y])
            ->get();
        return $result ? $result['id'] : null;
    }

    public function getVillageType($wref)
    {
        $result = $this->conn
            ->select('fieldtype')
            ->from('wdata')
            ->where('id = :id', [':id' => $wref])
            ->limit(1)
            ->first();
        return $result ? $result['fieldtype'] : false;
    }

    public function checkVilExist($wref)
    {
        $result = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('wref = :wref', [':wref' => $wref])
            ->limit(1)
            ->get();
        return !empty($result);
    }

    public function getVillageState($wref)
    {
        $result = $this->conn->select('oasistype, occupied')->from('wdata')->where('id = ?', [$wref])->first();
        return $result['occupied'] != 0 || $result['oasistype'] != 0;
    }

    public function getVillageStateForSettle($wref)
    {
        $result = $this->conn
            ->select('oasistype, occupied, fieldtype')
            ->from('wdata')
            ->where('id = :id', [':id' => $wref])
            ->limit(1)
            ->get();
        if (!empty($result)) {
            return ($result['occupied'] == 0 && $result['oasistype'] == 0 && $result['fieldtype'] == 0);
        }
        return false;
    }

    public function getProfileVillages($uid)
    {
        return $this->conn
            ->select('wref, maxstore, maxcrop, pop, name, capital')
            ->from('vdata')
            ->where('owner = :uid', [':uid' => $uid])
            ->orderByDesc('pop')
            ->get();
    }

    public function getProfileMedal($uid)
    {
        return $this->conn
            ->select('id, categorie, plaats, week, img, points')
            ->from('medal')
            ->where('userid = :uid', [':uid' => $uid])
            ->orderByDesc('id')
            ->get();
    }

    public function getProfileMedalAlly($uid)
    {
        return $this->conn
            ->select('id, categorie, plaats, week, img, points')
            ->from('allimedal')
            ->where('allyid = :uid', [':uid' => $uid])
            ->orderByDesc('id')
            ->get();
    }

    public function getVillageID($uid)
    {
        $result = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('owner = :uid', [':uid' => $uid])
            ->get();
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
        return $this->conn
            ->select($columns)
            ->from('wdata')
            ->where($list)
            ->orderBy($orderby)
            ->limit($limit)
            ->get();
    }

    public function getVillagesListCount($list)
    {
        $where = ' WHERE TRUE ';
        foreach ($list as $k => $v) {
            if ($k != 'extra') $where .= " AND $k = $v ";
        }
        if (isset($list['extra'])) $where .= ' AND ' . $list['extra'] . ' ';
        return $this->conn->count('wdata', $where);
    }

    public function getOasisV($vid)
    {
        return $this->conn
            ->select()
            ->from('odata')
            ->where('wref = :vid', [':vid' => $vid])
            ->get();
    }

    public function getAInfo($id)
    {
        return $this->conn
            ->select('x, y')
            ->from('wdata')
            ->where('id = :id', [':id' => $id])
            ->get();
    }

    public function getOasisField($ref, $field)
    {
        return $this->conn
            ->select($field)
            ->from('odata')
            ->where('wref = :ref', [':ref' => $ref])
            ->get();
    }

    public function setVillageField($ref, $field, $value)
    {
        if ((stripos($field, 'name') !== false) && ($value == '')) {
            return false;
        }
        return $this->conn->update('vdata', [$field => $value], ['wref' => $ref]);
    }

    public function setVillageLevel($ref, $field, $value)
    {
        return $this->conn->update('fdata', [$field => $value], ['vref' => $ref]);
    }

    public function removeTribeSpecificFields($vref)
    {
        $fields = $this->getResourceLevel($vref);
        $tribeSpecificArray = [31, 32, 33, 35, 36, 41];
        for ($i = 19; $i <= 40; $i++) {
            if (in_array($fields['f' . $i . 't'], $tribeSpecificArray)) {
                $this->conn->update('fdata', ['f' . $i => '0', 'f' . $i . 't' => '0'], ['vref' => $vref]);
            }
        }
        $this->conn->update('units', ['u199' => 0], ['vref' => $vref]);
        $this->conn->delete('trapped', ['vref' => $vref]);
        $this->conn->delete('training', ['vref' => $vref]);
    }

    public function getAdminLog($limit = 5)
    {
        return $this->conn
            ->select('*')
            ->from('admin_log')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function delAdminLog($id)
    {
        return $this->conn->delete('admin_log', ['id' => $id]);
    }

    public function checkForum($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_cat')
            ->where('alliance = :id', [':id' => $id])
            ->get();
    }

    public function countCat($id)
    {
        $result = $this->conn
            ->select('count(id)')
            ->from('forum_topic')
            ->where('cat = :id', [':id' => $id])
            ->get();
        return $result['count(id)'];
    }

    public function lastTopic($id)
    {
        return $this->conn
            ->select('id')
            ->from('forum_topic')
            ->where('cat = :id', [':id' => $id])
            ->orderBy('post_date')
            ->get();
    }

    public function checkForumRules($id)
    {

        $row = $this->conn
            ->select('*')
            ->from('fpost_rules')
            ->where('forum_id = :id', [':id' => $id])
            ->get();

        $ids = explode(',', $row['players_id']);
        if (in_array(Session::get('uid'), $ids)) {
            return false;
        }

        $idn = explode(',', $row['players_name']);
        if (in_array(Session::get('username'), $idn)) {
            return false;
        }

        $aid = Session::get('alliance');
        $ids = explode(',', $row['ally_id']);
        if (in_array($aid, $ids)) {
            return false;
        }

        $rows = $this->conn
            ->select('tag')
            ->from('alidata')
            ->where('id = :aid', [':aid' => $aid])
            ->get();
        $idn = explode(',', $row['ally_tag']);
        if (in_array($rows['tag'], $idn)) {
            return false;
        }

        return true;
    }

    public function checkLastTopic($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where('cat = :id', [':id' => $id])
            ->get();
    }

    public function checkLastPost($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_post')
            ->where('topic = :id', [':id' => $id])
            ->get();
    }

    public function lastPost($id)
    {
        return $this->conn
            ->select('date, owner')
            ->from('forum_post')
            ->where('topic = :id', [':id' => $id])
            ->get();
    }

    public function countTopic($id)
    {
        $result = $this->conn
            ->select('count(id)')
            ->from('forum_post')
            ->where('owner = :id', [':id' => $id])
            ->get();
        $postsCount = $result['count(id)'];

        $result = $this->conn
            ->select('count(id)')
            ->from('forum_topic')
            ->where('owner = :id', [':id' => $id])
            ->get();
        $topicsCount = $result['count(id)'];

        return $postsCount + $topicsCount;
    }

    public function countPost($id)
    {
        $result = $this->conn
            ->select('count(id)')
            ->from('forum_post')
            ->where('topic = :id', [':id' => $id])
            ->get();
        return $result['count(id)'];
    }

    public function forumCat($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_cat')
            ->where('alliance = :id', [':id' => $id])
            ->orderBy('id')
            ->get();
    }

    public function forumCatEdit($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_cat')
            ->where('id = :id', [':id' => $id])
            ->get();
    }

    public function forumCatName($id)
    {
        $result = $this->conn
            ->select('forum_name')
            ->from('forum_cat')
            ->where('id = :id', [':id' => $id])
            ->get();
        return $result['forum_name'];
    }

    public function checkCatTopic($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where('cat = :id', [':id' => $id])
            ->get();
    }

    public function checkResultEdit($alli)
    {
        return $this->conn
            ->select('id')
            ->from('forum_edit')
            ->where('alliance = :alli', [':alli' => $alli])
            ->get();
    }

    public function checkCloseTopic($id)
    {
        $result = $this->conn
            ->select('close')
            ->from('forum_topic')
            ->where('id = :id', [':id' => $id])
            ->get();
        return $result['close'];
    }

    public function checkEditRes($alli)
    {
        $result = $this->conn
            ->select('result')
            ->from('forum_edit')
            ->where('alliance = :alli', [':alli' => $alli])
            ->get();
        return $result['result'];
    }

    public function creatResultEdit($alli, $result)
    {
        $this->conn->insert('forum_edit', ['alliance' => $alli, 'result' => $result]);
        return $this->conn->getLastInsertId();
    }

    public function updateResultEdit($alli, $result)
    {
        return $this->conn->update('forum_edit', ['result' => $result], ['alliance' => $alli]);
    }

    public function updateEditTopic($id, $title, $cat)
    {
        return $this->conn->update('forum_topic', ['title' => $title, 'cat' => $cat], ['id' => $id]);
    }

    public function updateEditForum($id, $name, $des)
    {
        return $this->conn->update('forum_cat', ['forum_name' => $name, 'forum_des' => $des], ['id' => $id]);
    }

    public function stickTopic($id, $mode)
    {
        return $this->conn->update('forum_topic', ['stick' => $mode], ['id' => $id]);
    }

    public function forumCatTopic($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where(['cat' => $id, 'stick' => ''])
            ->orderBy('post_date DESC')
            ->get();
    }

    public function forumCatTopicStick($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where(['cat' => $id, 'stick' => '1'])
            ->orderBy('post_date DESC')
            ->get();
    }

    public function showTopic($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where(['id' => $id])
            ->get();
    }

    public function showPost($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_post')
            ->where(['topic' => $id])
            ->get();
    }

    public function showPostEdit($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_post')
            ->where(['id' => $id])
            ->get();
    }

    public function createForum($owner, $alli, $name, $des, $area)
    {
        $this->conn->insert('forum_cat', ['owner' => $owner, 'alliance' => $alli, 'forum_name' => $name, 'forum_des' => $des, 'area' => $area]);
        return $this->conn->getLastInsertId();
    }

    public function createTopic($title, $post, $cat, $owner, $alli, $ends)
    {
        $date = time();
        return $this->conn->insert('forum_topic', ['title' => $title, 'post' => $post, 'post_date' => $date, 'last_post' => $date, 'cat' => $cat, 'owner' => $owner, 'alliance' => $alli, 'ends' => $ends]) ? $this->conn->getLastInsertId() : null;
    }

    public function createPost($post, $tids, $owner)
    {
        $this->conn->insert('forum_post', ['post' => $post, 'topic' => $tids, 'owner' => $owner, 'date' => time()]);
        return $this->conn->getLastInsertId();
    }

    public function updatePostDate($id)
    {
        return $this->conn->update('forum_topic', ['post_date' => time()], ['id' => $id]);
    }

    public function editUpdateTopic($id, $post)
    {
        return $this->conn->update('forum_topic', ['post' => $post], ['id' => $id]);
    }

    public function editUpdatePost($id, $post)
    {
        return $this->conn->update('forum_post', ['post' => $post], ['id' => $id]);
    }

    public function lockTopic($id, $mode)
    {
        return $this->conn->update('forum_topic', ['close' => $mode], ['id' => $id]);
    }

    public function deleteCat($id)
    {
        $this->conn->delete('forum_cat', ['id' => $id]);
        $this->conn->delete('forum_topic', ['cat' => $id]);
    }

    public function deleteTopic($id)
    {
        return $this->conn->delete('forum_topic', ['id' => $id]);
    }

    public function deletePost($id)
    {
        return $this->conn->delete('forum_post', ['id' => $id]);
    }

    public function getAllianceName($id)
    {
        if (!$id) {
            return false;
        }
        $result = $this->conn
            ->select('tag')
            ->from('alidata')
            ->where(['id' => $id])
            ->get();

        return $result ? $result['tag'] : false;
    }

    public function getAlliancePermission($ref, $field, $mode)
    {
        if (!$mode) {
            $result = $this->conn
                ->select($field)
                ->from('ali_permission')
                ->where(['uid' => $ref])
                ->get();

        } else {
            $result = $this->conn
                ->select($field)
                ->from('ali_permission')
                ->where(['username' => $ref])
                ->get();

        }
        return $result ? $result[$field] : null;
    }

    public function changePos($id, $mode)
    {
        $forumArea = $this->conn
            ->select('forum_area')
            ->from('forum_cat')
            ->where(['id' => $id])
            ->get();

        if (!$forumArea) return;

        if ($mode == '-1') {
            $prevCat = $this->conn
                ->select('id')
                ->from('forum_cat')
                ->where(['forum_area' => $forumArea['forum_area'], 'id <' => $id])
                ->orderBy('id', 'DESC')
                ->get();

            if ($prevCat) {
                $this->conn->update('forum_cat', ['id' => 0], ['id' => $prevCat['id']]);
                $this->conn->update('forum_cat', ['id' => -1], ['id' => $id]);
                $this->conn->update('forum_cat', ['id' => $id], ['id' => 0]);
                $this->conn->update('forum_cat', ['id' => $prevCat['id']], ['id' => -1]);
            }
        } elseif ($mode == 1) {
            $nextCat = $this->conn
                ->select('id')
                ->from('forum_cat')
                ->where(['forum_area' => $forumArea['forum_area'], 'id >' => $id])
                ->orderBy('id', 'ASC')
                ->get();

            if ($nextCat) {
                $this->conn->update('forum_cat', ['id' => 0], ['id' => $nextCat['id']]);
                $this->conn->update('forum_cat', ['id' => -1], ['id' => $id]);
                $this->conn->update('forum_cat', ['id' => $id], ['id' => 0]);
                $this->conn->update('forum_cat', ['id' => $nextCat['id']], ['id' => -1]);
            }
        }
    }

    public function forumCatAlliance($id)
    {
        $result = $this->conn
            ->select('alliance')
            ->from('forum_cat')
            ->where(['id' => $id])
            ->get();

        return $result ? $result['alliance'] : null;
    }

    public function createPoll($id, $name, $p1_name, $p2_name, $p3_name, $p4_name)
    {
        return $this->conn->insert('forum_poll', ['id' => $id, 'name' => $name, 'p1_name' => $p1_name, 'p2_name' => $p2_name, 'p3_name' => $p3_name, 'p4_name' => $p4_name]) ? $this->conn->lastInsertId() : null;
    }

    public function createForumRules($aid, $id, $users_id, $users_name, $alli_id, $alli_name)
    {
        return $this->conn->insert('fpost_rules', ['aid' => $aid, 'id' => $id, 'users_id' => $users_id, 'users_name' => $users_name, 'alli_id' => $alli_id, 'alli_name' => $alli_name]) ? $this->conn->lastInsertId() : null;
    }

    public function setAlliName($aid, $name, $tag)
    {
        if (!$aid) {
            return false;
        }
        return $this->conn->update('alidata', ['name' => $name, 'tag' => $tag], ['id' => $aid]);
    }

    public function isAllianceOwner($id)
    {
        if (!$id) {
            return false;
        }
        $result = $this->conn
            ->select('id')
            ->from('alidata')
            ->where(['leader' => $id])
            ->get();

        return $result ? true : false;
    }

    public function aExist($ref, $type)
    {
        $result = $this->conn
            ->select('alidata')
            ->where([$type => $ref])
            ->get();

        return $result ? true : false;
    }

    public function createAlliance($tag, $name, $uid, $max)
    {
        return $this->conn->insert('alidata', ['name' => $name, 'tag' => $tag, 'leader' => $uid, 'max' => $max]) ? $this->conn->lastInsertId() : null;
    }

    public function insertAlliNotice($aid, $notice)
    {
        return $this->conn->insert('ali_log', ['aid' => $aid, 'notice' => $notice, 'date' => time()]) ? $this->conn->lastInsertId() : null;
    }

    public function deleteAlliance($aid)
    {
        $result = $this->conn
            ->select('id')
            ->from('users')
            ->where(['alliance' => $aid])
            ->get();
        if (!$result) {
            return $this->conn->delete('alidata', ['id' => $aid]);
        }
        return false;
    }

    public function readAlliNotice($aid)
    {
        return $this->conn
            ->select()
            ->from('ali_log')
            ->where(['aid' => $aid])
            ->orderByDesc('date')
            ->get();
    }

    public function createAlliPermissions($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8)
    {
        return $this->conn->insert('ali_permission', ['uid' => $uid, 'aid' => $aid, 'rank' => $rank, 'opt1' => $opt1, 'opt2' => $opt2, 'opt3' => $opt3, 'opt4' => $opt4, 'opt5' => $opt5, 'opt6' => $opt6, 'opt7' => $opt7, 'opt8' => $opt8]) ? $this->conn->lastInsertId() : null;
    }

    public function deleteAlliPermissions($uid)
    {
        return $this->conn->delete('ali_permission', ['uid' => $uid]);
    }

    public function updateAlliPermissions($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8 = 0)
    {
        return $this->conn->update('ali_permission', ['rank' => $rank, 'opt1' => $opt1, 'opt2' => $opt2, 'opt3' => $opt3, 'opt4' => $opt4, 'opt5' => $opt5, 'opt6' => $opt6, 'opt7' => $opt7, 'opt8' => $opt8], ['uid' => $uid, 'alliance' => $aid]);
    }

    public function getAlliPermissions($uid, $aid)
    {
        return $this->conn
            ->select()
            ->from('ali_permission')
            ->where('uid = :uid AND alliance = :aid', [':uid' => $uid, ':aid' => $aid])
            ->get();
    }

    public function submitAlliProfile($aid, $notice, $desc)
    {
        if (!$aid) {
            return false;
        }
        return $this->conn->update('alidata', ['notice' => $notice, 'desc' => $desc], ['id' => $aid]);
    }

    public function diplomacyInviteAdd($alli1, $alli2, $type)
    {
        return $this->conn->insert('diplomacy', ['alli1' => $alli1, 'alli2' => $alli2, 'type' => intval($type), 'accepted' => 0]);
    }

    public function diplomacyOwnOffers($alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where(['alli1' => $alliance, 'accepted' => 0])
            ->get();
    }

    public function getAllianceID($name)
    {
        $result = $this->conn
            ->select('id')
            ->from('alidata')
            ->where(['tag' => $this->RemoveXSS($name)])
            ->get();
        return $result ? $result['id'] : null;
    }

    public function RemoveXSS($val)
    {
        return htmlspecialchars($val, ENT_QUOTES);
    }

    public function diplomacyCancelOffer($id)
    {
        return $this->conn->delete('diplomacy', ['id' => $id]);
    }

    public function diplomacyInviteAccept($id, $alliance)
    {
        return $this->conn->update('diplomacy', ['accepted' => 1], ['id' => $id, 'alli2' => $alliance]);
    }

    public function diplomacyInviteDenied($id, $alliance)
    {
        return $this->conn->delete('diplomacy', ['id' => $id, 'alli2' => $alliance]);
    }

    public function diplomacyInviteCheck($alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where(['alli2' => $alliance, 'accepted' => 0])
            ->get();
    }

    public function diplomacyExistingRelationships($alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where(['alli2' => $alliance, 'accepted' => 1])
            ->get();
    }

    public function diplomacyExistingRelationships2($alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where(['alli1' => $alliance, 'accepted' => 1])
            ->get();
    }

    public function diplomacyCancelExistingRelationship($id, $alliance)
    {
        return $this->conn->delete('diplomacy', ['id' => $id, 'alli2' => $alliance]);
    }

    public function getUserAlliance($id)
    {
        if (!$id) {
            return false;
        }
        $result = $this->conn->select('users JOIN alidata', 'alidata.tag', 'users.alliance = alidata.id AND users.id = :id', ['id' => $id]);
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
        return $this->conn->update('vdata', $updateFields, ['wref' => $vid]);
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
        return $this->conn->update('vdata', $updateFields, ['wref' => $vid]);
    }

    public function modifyOasisResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        $updateFields = [
            'wood' => ($mode ? '+' : '-') . $wood,
            'clay' => ($mode ? '+' : '-') . $clay,
            'iron' => ($mode ? '+' : '-') . $iron,
            'crop' => ($mode ? '+' : '-') . $crop
        ];
        return $this->conn->update('odata', $updateFields, ['wref' => $vid]);
    }

    public function getFieldType($vid, $field)
    {
        $result = $this->conn
            ->select("f$field" . 't')
            ->from('fdata')
            ->where('vref = :vid', [':vid' => $vid])
            ->get();
        return $result ? $result["f$field" . 't'] : null;
    }

    public function getVSumField($uid, $field)
    {
        $result = $this->conn
            ->select("SUM($field)")
            ->from('vdata')
            ->where('owner = :uid', [':uid' => $uid])
            ->get();
        return $result ? $result["SUM($field)"] : null;
    }

    public function updateVillage($vid)
    {
        return $this->conn->update('vdata', ['lastupdate' => time()], ['wref' => $vid]);
    }

    public function updateOasis($vid)
    {
        return $this->conn->update('odata', ['lastupdated' => time()], ['wref' => $vid]);
    }

    public function setVillageName($vid, $name)
    {
        if ($name == '') {
            return false;
        }
        return $this->conn->update('vdata', ['name' => $name], ['wref' => $vid]);
    }

    public function modifyPop($vid, $pop, $mode)
    {
        return $this->conn->update('vdata', ['pop' => ($mode ? '-' : '+') . $pop], ['wref' => $vid]);
    }

    public function addCP($ref, $cp)
    {
        return $this->conn->update('vdata', ['cp' => 'cp + ' . $cp], ['wref' => $ref]);
    }

    public function addCel($ref, $cel, $type)
    {
        return $this->conn->update('vdata', ['celebration' => $cel, 'type' => $type], ['wref' => $ref]);
    }

    public function getCel()
    {
        return $this->conn
            ->select()
            ->from('vdata')
            ->where('celebration < :time AND celebration != 0', [':time' => time()])
            ->get();
    }

    public function getActiveGCel($vref)
    {
        $time = time();
        return $this->conn->select('vdata', '*', "vref = $vref AND celebration > $time AND type = 2");
    }

    public function clearCel($ref)
    {
        return $this->conn->update('vdata', ['celebration' => 0, 'type' => 0], ['wref' => $ref]);
    }

    public function setCelCp($user, $cp)
    {
        return $this->conn->update('users', ['cp' => $cp], ['id' => $user]);
    }

    public function getInvitation($uid, $ally)
    {
        return $this->conn->select('ali_invite', '*', "uid = ? AND alliance = ?", [$uid, $ally]);
    }

    public function getInvitation2($uid)
    {
        return $this->conn->select('ali_invite', '*', "uid = ?", [$uid]);
    }

    public function getAliInvitations($aid)
    {
        return $this->conn->select('ali_invite', '*', "alliance = ? AND accept = 0", [$aid]);
    }

    public function sendInvitation($uid, $alli, $sender)
    {
        return $this->conn->insert('ali_invite', ['uid' => $uid, 'alliance' => $alli, 'sender' => $sender, 'timestamp' => time(), 'accept' => 0]);
    }

    public function removeInvitation($id)
    {
        return $this->conn->delete('ali_invite', "id = ?", [$id]);
    }

    public function delMessage($id)
    {
        return $this->conn->delete('mdata', "id = ?", [$id]);
    }

    public function delNotice($id, $uid)
    {
        return $this->conn->delete('ndata', "id = ? AND uid = ?", [$id, $uid]);
    }

    public function sendMessage($client, $owner, $topic, $message, $send, $alliance, $player, $coor, $report)
    {
        $fields = ['client' => $client, 'owner' => $owner, 'topic' => $topic, 'message' => $message, 'send' => $send, 'alliance' => $alliance, 'player' => $player, 'coor' => $coor, 'report' => $report, 'time' => time()];
        return $this->conn->insert('mdata', $fields);
    }

    public function setArchived($id)
    {
        return $this->conn->update('mdata', ['archived' => 1], ['id' => $id]);
    }

    public function setNorm($id)
    {
        return $this->conn->update('mdata', ['archived' => 0], ['id' => $id]);
    }

    public function getMessage($id, $mode)
    {
        switch ($mode) {
            case 1:
                $conditions = "target = ? AND send = 0 AND archived = 0";
                $values = [$id];
                break;
            case 2:
                $conditions = "owner = ?";
                $values = [$id];
                break;
            case 3:
            case 12:
                $conditions = "id = ?";
                $values = [$id];
                break;
            case 4:
                $fields = ['viewed' => 1];
                $conditions = ['id' => $id, 'target' => Session::get('uid')];
                return $this->conn->update('mdata', $fields, $conditions);
            case 5:
                $fields = ['deltarget' => 1, 'viewed' => 1];
                $conditions = ['id' => $id];
                return $this->conn->update('mdata', $fields, $conditions);
            case 6:
                $conditions = "target = ? AND send = 0 AND archived = 1";
                $values = [$id];
                break;
            case 7:
                $fields = ['delowner' => 1];
                $conditions = ['id' => $id];
                return $this->conn->update('mdata', $fields, $conditions);
            case 8:
                $fields = ['deltarget' => 1, 'delowner' => 1, 'viewed' => 1];
                $conditions = ['id' => $id];
                return $this->conn->update('mdata', $fields, $conditions);
            case 9:
                $conditions = "target = ? AND send = 0 AND archived = 0 AND deltarget = 0 AND viewed = 0";
                $values = [$id];
                break;
            case 10:
                $conditions = "owner = ? AND delowner = 0";
                $values = [$id];
                break;
            case 11:
                $conditions = "target = ? AND send = 0 AND archived = 1 AND deltarget = 0";
                $values = [$id];
                break;
        }

        if ($mode <= 3 || $mode == 6 || $mode > 8) {
            return $this->conn->select('mdata', '*', $conditions, $values, 'time DESC');
        } else {
            return false;
        }
    }

    public function addBuilding($wid, $field, $type, $loop, $time, $master, $level)
    {
        $this->conn->update('fdata', ["f$field" . "t" => $type], "vref = :wid", ['wid' => $wid]);

        return $this->conn->insert('bdata', [
            'wid' => $wid,
            'field' => $field,
            'type' => $type,
            'loop' => $loop,
            'time' => $time,
            'master' => $master,
            'level' => $level
        ]);
    }

    public function removeBuilding($id)
    {
        $building = new Building();

        $jobs = $building->buildArray;

        $jobDeleted = -1;
        $jobLoopconID = -1;
        $jobMaster = -1;

        foreach ($jobs as $i => $job) {
            if ($job['id'] == $id) {
                $jobDeleted = $i;
            }
            if ($job['loopcon'] == 1) {
                $jobLoopconID = $i;
            }
            if ($job['master'] == 1) {
                $jobMaster = $i;
            }
        }

        $sameBuildCount = $this->calculateSameBuildCount($jobs);

        if ($sameBuildCount > 0) {
            $this->handleSameBuildCount($jobs, $sameBuildCount, $jobDeleted, $jobMaster);
        } else {
            $this->handleDifferentBuildCount($jobs, $jobDeleted, $jobLoopconID, $building);
        }

        return $this->conn->delete('bdata', 'id = :id', [':id' => $id]);
    }

    private function calculateSameBuildCount($jobs)
    {
        $sameBuildCount = 0;
        $fieldCounts = array_count_values(array_column($jobs, 'field'));

        foreach ($fieldCounts as $count) {
            if ($count > 1) {
                $sameBuildCount += $count;
            }
        }

        return $sameBuildCount;
    }

    private function handleSameBuildCount($jobs, $sameBuildCount, $jobDeleted, $jobMaster)
    {
        $building = new Building();

        if ($sameBuildCount > 3) {
            if ($sameBuildCount == 4 || $sameBuildCount == 5) {
                if ($jobDeleted == 0) {
                    $uprequire = $building->resourceRequired($jobs[1]['field'], $jobs[1]['type'], 1);
                    $timestamp = time() + $uprequire['time'];
                    $this->conn->update('bdata', ['loopcon' => 0, 'level' => 'level - 1', 'timestamp' => $timestamp], ['id' => $jobs[1]['id']]);
                }
            } elseif ($sameBuildCount == 6) {
                if ($jobDeleted == 0) {
                    $uprequire = $building->resourceRequired($jobs[2]['field'], $jobs[2]['type'], 1);
                    $timestamp = time() + $uprequire['time'];
                    $this->conn->update('bdata', ['loopcon' => 0, 'level' => 'level - 1', 'timestamp' => $timestamp], ['id' => $jobs[2]['id']]);
                }
            } elseif ($sameBuildCount == 7) {
                if ($jobDeleted == 1) {
                    $uprequire = $building->resourceRequired($jobs[2]['field'], $jobs[2]['type'], 1);
                    $timestamp = time() + $uprequire['time'];
                    $this->conn->update('bdata', ['loopcon' => 0, 'level' => 'level - 1', 'timestamp' => $timestamp], ['id' => $jobs[2]['id']]);
                }
            }
            if ($sameBuildCount < 8) {
                $uprequire1 = $building->resourceRequired($jobs[$jobMaster]['field'], $jobs[$jobMaster]['type'], 2);
                $timestamp1 = $uprequire1['time'];
            } else {
                $uprequire1 = $building->resourceRequired($jobs[$jobMaster]['field'], $jobs[$jobMaster]['type'], 1);
                $timestamp1 = $uprequire1['time'];
            }
        } else {
            if ($jobDeleted == $jobs[floor($sameBuildCount / 3)]['id'] || $jobDeleted == $jobs[floor($sameBuildCount / 2) + 1]['id']) {
                $timestamp = $jobs[floor($sameBuildCount / 3)]['timestamp'];
                $condition = [
                    'master' => 0,
                    'id[>]' => $jobDeleted,
                    'OR' => [
                        'ID' => $jobs[floor($sameBuildCount / 3)]['id'],
                        'ID' => $jobs[floor($sameBuildCount / 2) + 1]['id']
                    ]
                ];
                $this->conn->update('bdata', ['loopcon' => 0, 'level' => 'level - 1', 'timestamp' => $timestamp], $condition);
            }
        }

        $this->conn->update('bdata', ['level' => 'level - 1', 'timestamp' => $timestamp1], ['id' => $jobs[$jobMaster]['id']]);
    }

    private function handleDifferentBuildCount($jobs, $sameBuildCount, $jobDeleted, $jobLoopconID)
    {
        $building = new Building();

        if ($jobs[$jobDeleted]['field'] >= 19) {
            $field = $jobs[$jobDeleted]['field'];
            $wid = $jobs[$jobDeleted]['wid'];

            $fieldValue = $this->conn->select('fdata', "f$field", ['vref' => $wid]);
            if ($fieldValue === 0) {
                $updateData = ["f${field}t" => 0];
                $condition = ['vref' => $wid];
                $this->conn->update('fdata', $updateData, $condition);
            }
        }

        if (($jobLoopconID >= 0) && ($jobs[$jobDeleted]['loopcon'] != 1)) {
            if (($jobs[$jobLoopconID]['field'] <= 18 && $jobs[$jobDeleted]['field'] <= 18) || ($jobs[$jobLoopconID]['field'] >= 19 && $jobs[$jobDeleted]['field'] >= 19) || sizeof($jobs) < 3) {
                $uprequire = $building->resourceRequired($jobs[$jobLoopconID]['field'], $jobs[$jobLoopconID]['type']);
                $this->conn->update('bdata', ['loopcon' => 0, 'timestamp' => time() + $uprequire['time']], ['wid' => $jobs[$jobDeleted]['wid'], 'loopcon' => 1, 'master' => 0]);
            }
        }
    }

    public function getNotice($uid)
    {
        return $this->conn->select('ndata', '*', 'uid = :uid', ['uid' => $uid]);
    }

    public function addNotice($uid, $toWref, $ally, $type, $topic, $data, $time = 0)
    {
        if ($time == 0) {
            $time = time();
        }

        return $this->conn->insert('ndata', [
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
        return $this->conn->update('ndata', ['viewed' => 1], 'id = :id', ['id' => $id]);
    }

    public function removeNotice($id)
    {
        return $this->conn->delete('ndata', 'id = :id', ['id' => $id]);
    }

    public function archiveNotice($id)
    {
        return $this->conn->update('ndata', ['archive' => 1], 'id = :id', ['id' => $id]);
    }

    public function unarchiveNotice($id)
    {
        return $this->conn->update('ndata', ['archive' => 0], 'id = :id', ['id' => $id]);
    }

    public function addDemolition($wid, $field)
    {
        global $building, $village;

        $conditions = ['field' => $field, 'wid' => $wid];
        $this->conn->delete('bdata', $conditions);

        $uprequire = $building->resourceRequired($field - 1, $village->resarray['f' . $field . 't']);

        $fieldLevel = $this->conn->getFieldLevel($wid, $field) - 1;

        $timestamp = time() + floor($uprequire['time'] / 2);

        $data = [
            'wid' => $wid,
            'field' => $field,
            'field_level' => $fieldLevel,
            'timestamp' => $timestamp
        ];
        $this->conn->insert('demolition', $data);

        return true;
    }

    public function getFieldLevel($vid, $field)
    {
        $result = $this->conn->select('fdata', 'f' . $field, ['vref' => $vid]);
        return $result['f' . $field];
    }

    public function getDemolition($wid = 0)
    {
        $conditions = ($wid) ? ['vref' => $wid] : 'timetofinish <= ' . time();
        $result = $this->conn->select('demolition', 'vref, buildnumber, timetofinish', $conditions);
        return $result ? $result : NULL;
    }

    public function finishDemolition($wid)
    {
        return $this->conn->update('demolition', ['timetofinish' => 0], ['vref' => $wid]);
    }

    public function delDemolition($wid)
    {
        return $this->conn->delete('demolition', ['vref' => $wid]);
    }

    public function getJobs($wid)
    {
        return $this->conn->select('bdata', '*', ['wid' => $wid], ['ID ASC']);
    }

    public function FinishWoodcutter($wid)
    {
        $job = $this->conn->select('bdata', 'id', ['wid' => $wid, 'type' => 1], ['master, timestamp ASC']);
        if ($job) {
            $this->conn->update('bdata', ['timestamp' => time() - 1], ['id' => $job['id']]);
        }
    }

    public function FinishCropLand($wid)
    {
        $time = time() - 1;
        $job = $this->conn->select('bdata', ['id', 'timestamp'], ['wid' => $wid, 'type' => 4], ['master, timestamp ASC']);
        if ($job) {
            $this->conn->update('bdata', ['timestamp' => $time], ['id' => $job['id']]);
        }
        $jobs = $this->conn->select('bdata', ['id', 'timestamp'], ['wid' => $wid, 'loopcon' => 1, 'field' => ['<=', 18]], ['master, timestamp ASC']);
        foreach ($jobs as $job) {
            $this->conn->update('bdata', ['timestamp' => $job['timestamp'] - time()], ['id' => $job['id']]);
        }
    }

    public function finishBuildings($wid)
    {
        $buildings = $this->conn->select('bdata', 'id', ['wid' => $wid], ['master, timestamp ASC']);
        foreach ($buildings as $building) {
            $this->conn->update('bdata', ['timestamp' => time() - 1], ['id' => $building['id']]);
        }
    }

    public function getMasterJobs($wid)
    {
        return $this->conn->select('bdata', 'id', ['wid' => $wid, 'master' => 1], ['master, timestamp ASC']);
    }

    public function getBuildingByField($wid, $field)
    {
        return $this->conn->select('bdata', 'id', ['wid' => $wid, 'field' => $field, 'master' => 0]);
    }

    public function getBuildingByType($wid, $type)
    {
        return $this->conn->select('bdata', 'id', ['wid' => $wid, 'type' => $type, 'master' => 0]);
    }

    public function getDorf1Building($wid)
    {
        return $this->conn->select('bdata', 'timestamp', ['wid' => $wid, 'field' => '< 19', 'master' => 0]);
    }

    public function getDorf2Building($wid)
    {
        return $this->conn->select('bdata', 'timestamp', ['wid' => $wid, 'field' => '> 18', 'master' => 0]);
    }

    public function updateBuildingWithMaster($id, $time, $loop)
    {
        $data = [
            'master' => 0,
            'timestamp' => $time,
            'loopcon' => $loop
        ];
        return $this->conn->update('bdata', $data, ['id' => $id]);
    }

    public function getVillageByName($name)
    {
        $result = $this->conn->select('vdata', 'wref', ['name' => $name]);
        return $result['wref'];
    }

    public function setMarketAcc($id)
    {
        return $this->conn->update('market', ['accept' => 1], ['id' => $id]);
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
        return $this->conn->insert('send', $data);
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
        return $this->conn->insert('send', $data);
    }

    public function removeSend($ref)
    {
        return $this->conn->delete('send', ['id' => $ref]);
    }

    public function getResourcesBack($vref, $gtype, $gamt)
    {
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
                return false;
        }
        $conditions = ['wref' => $vref];
        return $this->conn->update('vdata', $data, $conditions);
    }

    public function getMarketField($vref, $field)
    {
        $result = $this->conn->select('market', $field, ['vref' => $vref]);
        return $result ? $result[$field] : false;
    }

    public function removeAcceptedOffer($id)
    {
        return $this->conn->delete('market', ['id' => $id]);
    }

    public function addMarket($vid, $gtype, $gamt, $wtype, $wamt, $time, $alliance, $merchant, $mode)
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
            return $this->conn->insert('market', $data);
        } else {
            return $this->conn->delete('market', ['id' => $gtype, 'vref' => $vid]);
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
        return $this->conn->select('market', '*', $conditions, ['id DESC']);
    }

    public function getUserField($ref, $field, $mode)
    {
        $conditions = !$mode ? ['id' => $ref] : ['username' => $ref];
        $result = $this->conn->select('users', $field, $conditions);
        return $result[$field];
    }

    public function getVillageField($ref, $field)
    {
        $result = $this->conn->select('vdata', $field, ['wref' => $ref]);
        return $result[$field];
    }

    public function getMarketInfo($id)
    {
        return $this->conn->select('market', 'vref,gtype,wtype,merchant,wamt', ['id' => $id]);
    }

    public function setMovementProc($moveid)
    {
        $data = ['proc' => 1];
        $where = ['moveid' => $moveid];
        return $this->conn->update('movement', $data, $where);
    }

    public function totalMerchantUsed($vid)
    {
        $sql = "SELECT SUM(send.merchant)
            FROM send
            JOIN movement ON send.id = movement.ref
            WHERE movement.from = :vid AND movement.proc = 0 AND movement.sort_type = 0";

        $stmt = $this->conn->executeQuery($sql, [':vid' => $vid]);
        $row = $stmt->fetchColumn();

        $sql2 = "SELECT SUM(send.merchant)
             FROM send
             JOIN movement ON send.id = movement.ref
             WHERE movement.to = :vid AND movement.proc = 0 AND movement.sort_type = 1";

        $stmt2 = $this->conn->executeQuery($sql2, [':vid' => $vid]);
        $row2 = $stmt2->fetchColumn();

        $sql3 = "SELECT SUM(merchant)
             FROM market
             WHERE vref = :vid AND accept = 0";

        $stmt3 = $this->conn->executeQuery($sql3, [':vid' => $vid]);
        $row3 = $stmt3->fetchColumn();

        return ($row + $row2 + $row3);
    }

    public function getMovementById($id)
    {
        $results = $this->conn->select('movement', "starttime, to, from", "moveid = :id", [':id' => $id]);
        return !empty($results) ? $results : [];
    }

    public function cancelMovement($id, $newfrom, $newto)
    {
        $refstr = '';
        $amove = $this->conn->select('movement', 'ref', 'moveid = :id', [':id' => $id]);

        if (!empty($amove)) {
            $mov = $amove;
            if ($mov['ref'] == 0) {
                $ref = $this->addAttack($newto, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, 0, 0, 0);
                $refstr = ', ref = ' . $ref;
            }

            $this->conn->update(
                'movement',
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

        return $this->conn->insert('attacks', $data);
    }

    public function getAdvMovement($village)
    {
        $sql = "SELECT moveid FROM movement WHERE from = :village AND sort_type = 9";
        return $this->conn->select($sql, [':village' => $village]);
    }

    public function getCompletedAdvMovement($village)
    {
        $sql = "SELECT moveid FROM movement WHERE from = :village AND sort_type = 9 AND proc = 1";
        return $this->conn->select($sql, [':village' => $village]);
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

        return $this->conn->insert('a2b', $data);
    }

    public function getA2b($ckey, $check)
    {
        return $this->conn->select('a2b', ['ckey' => $ckey, 'time_check' => $check]);
    }

    public function removeA2b($ckey, $check)
    {
        $where = "ckey = :ckey AND time_check = :check";
        return $this->conn->delete('a2b', $where, [':ckey' => $ckey, ':check' => $check]);
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

        return $this->conn->insert('movement', $data);
    }

    public function modifyAttack($aid, $unit, $amt)
    {
        $unitColumn = 't' . $unit;

        if (!in_array($unitColumn, ['t1', 't2', 't3', 't4', 't5'])) {
            return false;
        }

        $currentAmt = $this->conn->select('attacks', $unitColumn, ['id' => $aid]);
        if ($currentAmt !== false) {
            return $this->conn->update('attacks', [$unitColumn => max(0, $currentAmt - $amt)], ['id' => $aid]);
        }

        return false;
    }


    public function getRanking()
    {
        $sql = "SELECT id, username, alliance, ap, apall, dp, dpall, access FROM users WHERE tribe <= 3 AND access < :access";
        return $this->conn->select($sql, [':access' => (INCLUDE_ADMIN ? 10 : 8)]);
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
        return $this->conn->select($sql, $params);
    }

    public function getVRanking()
    {
        $sql = "SELECT v.wref, v.name, v.owner, v.pop FROM vdata AS v, users AS u WHERE v.owner = u.id AND u.tribe <= 3 AND v.wref != '' AND u.access < :access";
        return $this->conn->select($sql, [':access' => (INCLUDE_ADMIN ? 10 : 8)]);
    }

    public function getARanking()
    {
        $sql = "SELECT id, name, tag FROM alidata WHERE id != ''";
        return $this->conn->select($sql);
    }

    public function getHeroRanking($limit = '')
    {
        $sql = "SELECT uid, level, experience FROM hero ORDER BY experience DESC $limit";
        return $this->conn->select($sql);
    }

    public function getAllMember($aid)
    {
        $sql = "SELECT id, username, timestamp FROM users WHERE alliance = :aid ORDER BY (SELECT SUM(pop) FROM vdata WHERE owner = users.id) DESC";
        return $this->conn->select($sql, [':aid' => $aid]);
    }

    public function getUnit($vid)
    {
        return $this->conn->select('units', 'unit_id, unit_type, unit_quantity', ['vref' => $vid]);
    }

    public function getHUnit($vid)
    {
        $row = $this->conn->select('units', 'hero', [':vid' => $vid]);
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
        return $this->conn->select('hero', $where, $params, ['LIMIT 1']);
    }

    public function modifyHero($uid, $id, $column, $value, $mode = 0)
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
        return $this->conn->executeQuery($sql, $params);
    }

    public function clearTech($vref)
    {
        $this->conn->delete('tdata', 'vref = :vref', [':vref' => $vref]);
        return $this->addTech($vref);
    }

    public function clearABTech($vref)
    {
        $this->conn->delete('abdata', 'vref = :vref', [':vref' => $vref]);
        return $this->addABTech($vref);
    }

    public function getABTech($vid)
    {
        return $this->conn->select('abdata', 'vref = :vid', [':vid' => $vid]);
    }

    public function addResearch($vid, $tech, $time)
    {
        return $this->conn->insert('research', ['vref' => $vid, 'tech' => $tech, 'time' => $time]);
    }

    public function getResearching($vid)
    {
        return $this->conn->select('research', '*', 'vref = :vid', [':vid' => $vid]);
    }

    public function checkIfResearched($vref, $unit)
    {
        $row = $this->conn->select('tdata', $unit, 'vref = :vref', [':vref' => $vref]);
        return $row[$unit];
    }

    public function getTech($vid)
    {
        return $this->conn->select('tdata', 'vref = :vid', [':vid' => $vid]);
    }

    public function getTraining($vid)
    {
        return $this->conn->select('training', '*', 'vref = :vid ORDER BY id', [':vid' => $vid]);
    }

    public function trainUnit($vid, $unit, $amt, $pop, $each, $commence, $mode)
    {
        $technology = new Technology();

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

        return $this->conn->executeQuery($q, $params);
    }

    public function removeZeroTrain()
    {
        return $this->conn->delete('training', ['unit[!]' => 0, 'amt[<]' => 1]);
    }

    public function getHeroTrain($vid)
    {
        return $this->conn->select('training', 'id, eachtime', ['vref' => $vid, 'unit' => 0]);
    }

    public function trainHero($vid, $each, $endat, $mode)
    {
        if (!$mode) {
            return $this->conn->insert('training', ['vref' => $vid, 'amt' => 0, 'eachtime' => 1, 'unit' => 6, 'timestamp' => time(), 'timestamp' => $each, 'timestamp' => $endat]);
        } else {
            return $this->conn->delete('training', ['id' => $vid]);
        }
    }

    public function updateTraining($id, $trained)
    {
        return $this->conn->update('training', ['amt' => 'GREATEST(amt - :trained, 0)', 'timestamp' => time()], ['id' => $id], [':trained' => $trained]);
    }

    public function modifyUnit($vref, $unit, $amt, $mode)
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
                $result = $this->conn->executeQuery($q, [':vref' => $vref]);
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
        return $this->conn->executeQuery($q, [':vref' => $vref, ':amt' => $amt]);
    }

    public function getFilledTrapCount($vref)
    {
        $result = 0;
        $trapped = $this->conn->select('trapped', ['vref' => $vref]);
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
        return $this->conn->select('trapped', ['id' => $id]);
    }

    public function getTrappedIn($vref)
    {
        return $this->conn->select('trapped', ['vref' => $vref]);
    }

    public function getTrappedFrom($from)
    {
        return $this->conn->select('trapped', ['from' => $from]);
    }

    public function addTrapped($vref, $from)
    {
        $id = $this->hasTrapped($vref, $from);
        if (!$id) {
            return $this->conn->insert('trapped', ['vref' => $vref, 'from' => $from]);
        }
        return $id;
    }

    public function hasTrapped($vref, $from)
    {
        return $this->conn->select('trapped', 'id', ['vref' => $vref, 'from' => $from]);
    }

    public function modifyTrapped($id, $unit, $amt, $mode)
    {
        $columnName = ($unit == 'hero') ? 'hero' : 'u' . $unit;
        $operation = ($mode == 0) ? '-' : '+';
        $this->conn->update('trapped', [$columnName => $columnName . $operation . $amt], ['id' => $id]);
    }

    public function removeTrapped($id)
    {
        $this->conn->delete('trapped', ['id' => $id]);
    }

    public function removeAnimals($id)
    {
        $this->conn->delete('enforcement', ['id' => $id]);
    }

    public function checkEnforce($vid, $from)
    {
        $enforce = $this->conn->select('enforcement', 'id', ['from' => $from, 'vref' => $vid]);
        return $enforce ? $enforce['id'] : false;
    }

    public function addEnforce($data)
    {
        $id = $this->conn->insert('enforcement', ['vref' => $data['to'], 'from' => $data['from']]);

        $fromVillage = $this->isVillageOasis($data['from']) ? $this->getOMInfo($data['from']) : $this->getMInfo($data['from']);
        $fromTribe = $this->getUserField($fromVillage["owner"], "tribe", 0);
        $start = ($fromTribe - 1) * 10 + 1;
        $end = ($fromTribe * 10);

        $j = 1;
        for ($i = $start; $i <= $end; $i++) {
            $this->modifyEnforce($id, $i, $data['t' . $j], 1);
            $j++;
        }

        return $id;
    }

    public function getOMInfo($id)
    {
        return $this->conn->leftJoin('wdata', 'odata', 'odata.wref = wdata.id', '*', 'wdata.id = :id', [':id' => $id]);
    }

    public function getMInfo($id)
    {
        return $this->conn->leftJoin('wdata', 'vdata', 'vdata.wref = wdata.id', '*', 'wdata.id = :id', [':id' => $id]);
    }

    public function modifyEnforce($id, $unit, $amt, $mode)
    {
        $columnName = ($unit == 'hero') ? 'hero' : 'u' . $unit;
        $operation = ($mode == 0) ? '-' : '+';

        $this->conn->update('enforcement', [$columnName => ["$columnName $operation :amt"]], ['id' => $id], [':amt' => $amt]);
    }

    public function addHeroEnforce($data)
    {
        $this->conn->insert("enforcement", ['vref' => $data['to'], 'from' => $data['from'], 'hero' => 1]);
    }

    public function getEnforceArray($id, $mode)
    {
        if (!$mode) {
            return $this->conn->select("enforcement", "*", "id = :id", [':id' => $id]);
        } else {
            return $this->conn->select("enforcement", "*", "from = :id", [':id' => $id]);
        }
    }

    public function getEnforceVillage($id, $mode)
    {
        if (!$mode) {
            return $this->conn->select("enforcement", "*", "vref = :id", [':id' => $id]);
        } else {
            return $this->conn->select("enforcement", "*", "from = :id", [':id' => $id]);
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
            return $this->conn->select("enforcement", "*", "from = :id AND vref IN " . $inos, [':id' => $id]);
        } else {
            return null;
        }
    }

    public function getOasis($vid)
    {
        return $this->conn->select("odata", "type, wref", "conqured = :vid", [":vid" => $vid]);
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
            $where = "from";
        } else {
            $where = "to";
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
        $result = $this->conn->executeQuery($q);
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
        $result = $this->conn->select('fdata', 'vref', 'f99t = ?', [40]);
        return !empty($result);
    }

    public function getWWLevel($vref)
    {
        $result = $this->conn->select('fdata', 'f99', 'vref = ?', [$vref]);

        if ($result !== false && isset($result['f99'])) {
            return $result['f99'];
        }

        return null;
    }

    public function getWWOwnerID($vref)
    {
        $result = $this->conn->select('vdata', 'owner', 'wref = ?', [$vref]);
        if ($result !== false && isset($result['owner'])) {
            return (int)$result['owner'];
        }
        return null;
    }

    public function getUserAllianceID($id)
    {
        $result = $this->conn->select('users', 'alliance', 'id = ?', [$id]);
        if ($result !== false && isset($result['alliance'])) {
            return (int)$result['alliance'];
        }
        return null;
    }

    public function getWWName($vref)
    {
        $result = $this->conn->select('fdata', 'wwname', 'vref = ?', [$vref]);
        if ($result !== false && isset($result['wwname'])) {
            return $result['wwname'];
        }
        return null;
    }

    public function submitWWname($vref, $name)
    {
        return $this->conn->update('fdata', ['wwname' => $name], 'vref = ?', [$vref]);
    }

    public function modifyCommence($id, $commence = 0)
    {
        if ($commence == 0) {
            $commence = time();
        }
        return $this->conn->update('training', ['commence' => $commence], 'id = ?', [$id]);
    }

    public function getTrainingList()
    {
        $result = $this->conn->select('training', 'id, vref, unit, eachtime, endat, commence, amt', 'amt != ?', [0]);
        return !empty($result) ? $result : [];
    }

    public function getNeedDelete()
    {
        $result = $this->conn->select('deleting', 'uid', 'timestamp <= ?', [time()]);
        return !empty($result) ? $result : [];
    }

    public function countUser()
    {
        $result = $this->conn->select('users', 'COUNT(id) as total', 'id != ?', [0]);
        if ($result !== false && isset($result['total'])) {
            return (int)$result['total'];
        }
        return 0;
    }

    public function countAlli()
    {
        $result = $this->conn->select('alidata', 'COUNT(id) as total', 'id != ?', [0]);

        if ($result !== false && isset($result['total'])) {
            return (int)$result['total'];
        }
        return 0;
    }

    public function getWoodAvailable($wref)
    {
        $result = $this->conn->select('vdata', 'wood', 'wref = ?', [$wref]);

        if ($result !== false && isset($result['wood'])) {
            return (int)$result['wood'];
        }

        return 0;
    }

    public function getClayAvailable($wref)
    {
        $result = $this->conn->select('vdata', 'clay', 'wref = ?', [$wref]);

        if ($result !== false && isset($result['clay'])) {
            return (int)$result['clay'];
        }

        return 0;
    }

    public function getIronAvailable($wref)
    {
        $result = $this->conn->select('vdata', 'iron', 'wref = ?', [$wref]);

        if ($result !== false && isset($result['iron'])) {
            return (int)$result['iron'];
        }

        return 0;
    }

    public function getCropAvailable($wref)
    {
        $result = $this->conn->select('vdata', 'crop', 'wref = ?', [$wref]);

        if ($result !== false && isset($result['crop'])) {
            return (int)$result['crop'];
        }

        return 0;
    }

    public function getAvailableExpansionTraining()
    {
        $building = new Building();

        $result = $this->conn->select('vdata', '(IF(exp1=0,1,0)+IF(exp2=0,1,0)+IF(exp3=0,1,0))', 'wref = ?', [$village->wid]);
        $maxslots = $result !== false ? (int)$result : 0;

        $residence = $building->getTypeLevel(25);
        $palace = $building->getTypeLevel(26);
        if ($residence > 0) {
            $maxslots -= (3 - floor($residence / 10));
        }
        if ($palace > 0) {
            $maxslots -= (3 - floor(($palace - 5) / 5));
        }

        $result = $this->conn->select('units', '(u10+u20+u30)', 'vref = ?', [$village->wid]);
        $settlers = $result !== false ? (int)$result : 0;

        $result = $this->conn->select('units', '(u9+u19+u29)', 'vref = ?', [$village->wid]);
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

        $result = $this->conn->select('enforcement', '(u10+u20+u30)', 'from = ?', [$village->wid]);
        if (!empty($result)) {
            $settlers += (int)$result;
        }

        $result = $this->conn->select('trapped', '(u10+u20+u30)', 'from = ?', [$village->wid]);
        if (!empty($result)) {
            $settlers += (int)$result;
        }

        $result = $this->conn->select('enforcement', '(u9+u19+u29)', 'from = ?', [$village->wid]);
        if (!empty($result)) {
            $chiefs += (int)$result;
        }

        $result = $this->conn->select('trapped', '(u9+u19+u29)', 'from = ?', [$village->wid]);
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

        if (!$technology->getTech((Session::get('tribe') - 1) * 10 + 9)) {
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

        return $this->conn->insert("artefacts", $data);
    }


    public function getOwnArtefactInfo($vref)
    {
        return $this->conn->select('artefacts', '*', 'vref = ?', [$vref]);
    }

    public function getArtefactInfo($sizes)
    {
        $conditions = "";
        $params = [];

        if (!empty($sizes)) {
            $placeholders = implode(",", array_fill(0, count($sizes), "?"));
            $conditions = " AND size IN ($placeholders)";
            $params = $sizes;
        }

        return $this->conn->select("artefacts", "*", "1 $conditions ORDER BY type", $params);
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
        return $this->conn->executeQuery($q, $params);
    }

    public function arteIsMine($id, $newvref, $newowner)
    {
        $this->conn->update('artefacts', ['owner' => $newowner], 'id = ?', [$id]);
        $this->captureArtefact($id, $newvref, $newowner);
    }

    public function captureArtefact($id, $newvref, $newowner)
    {
        $currentArte = $this->getArtefactDetails($id);

        if ($currentArte['size'] == 2 || $currentArte['size'] == 3) {
            $ulArts = $this->conn->select('artefacts', '*', 'owner = ? AND status = 1 AND size <> 1', [$newowner]);
            if (!empty($ulArts) && count($ulArts) > 0) {
                foreach ($ulArts as $art) {
                    $this->conn->update('artefacts', ['status' => 2], 'id = ?', [$art['id']]);
                }
            }
        }
        $vArts = $this->conn->select('artefacts', '*', 'vref = ? AND status = 1', [$newvref]);
        if (!empty($vArts) && count($vArts) > 0) {
            foreach ($vArts as $art) {
                $this->conn->update('artefacts', ['status' => 2], 'id = ?', [$art['id']]);
            }
        } else {
            $uArts = $this->conn->select('artefacts', '*', 'owner = ? AND status = 1 ORDER BY conquered DESC', [$newowner]);
            if (!empty($uArts) && count($uArts) > 2) {
                for ($i = 2; $i < count($uArts); $i++) {
                    $this->conn->update('artefacts', ['status' => 2], 'id = ?', [$uArts[$i]['id']]);
                }
            }
        }
        $this->conn->update('artefacts', ['vref' => $newvref, 'owner' => $newowner, 'conquered' => time(), 'status' => 1], 'id = ?', [$id]);
        if ($currentArte['status'] == 1) {
            $ouaArts = $this->conn->select('artefacts', '*', 'owner = ? AND status = 1', [$currentArte['owner']]);
            $ouiArts = $this->conn->select('artefacts', '*', 'owner = ? AND status = 2 ORDER BY conquered DESC', [$currentArte['owner']]);
            if (!empty($ouaArts) && count($ouaArts) < 3 && !empty($ouiArts) && count($ouiArts) > 0) {
                $ouiaCount = count($ouiArts);
                for ($i = 0; $i < $ouiaCount; $i++) {
                    $ia = $ouiArts[$i];
                    if (count($ouaArts) < 3) {
                        $accepted = true;
                        foreach ($ouaArts as $aa) {
                            if ($ia['vref'] == $aa['vref']) {
                                $accepted = false;
                            }
                            if (($ia['size'] == 2 || $ia['size'] == 3) && ($aa['size'] == 2 || $aa['size'] == 3)) {
                                $accepted = false;
                            }
                        }
                        if ($accepted) {
                            $ouaArts[] = $ia;
                            $this->conn->update('artefacts', ['status' => 1], 'id = ?', [$ia['id']]);
                        }
                    }
                }
            }
        }
    }

    public function getArtefactDetails($id)
    {
        return $this->conn->select('artefacts', 'id = ?', [$id]);
    }

    public function getHeroFace($uid)
    {
        return $this->conn->select('heroface', '*', 'uid = ?', [$uid]);
    }

    public function addHeroFace($uid, $bread, $ear, $eye, $eyebrow, $face, $hair, $mouth, $nose, $color)
    {
        return $this->conn->insert('heroface', ['uid' => $uid, 'beard' => $bread, 'ear' => $ear, 'eye' => $eye, 'eyebrow' => $eyebrow, 'face' => $face, 'hair' => $hair, 'mouth' => $mouth, 'nose' => $nose, 'color' => $color]);
    }

    public function modifyHeroFace($uid, $column, $value)
    {
        $hash = md5("$uid" . time());
        return $this->conn->update('heroface', [$column => $value, 'hash' => $hash], 'uid = ?', [$uid]);
    }

    public function modifyWholeHeroFace($uid, $face, $color, $hair, $ear, $eyebrow, $eye, $nose, $mouth, $beard)
    {
        $hash = md5("$uid" . time());
        return $this->conn->update('heroface', ['face' => $face, 'color' => $color, 'hair' => $hair, 'ear' => $ear, 'eyebrow' => $eyebrow, 'eye' => $eye, 'nose' => $nose, 'mouth' => $mouth, 'beard' => $beard, 'hash' => $hash], 'uid = ?', [$uid]);
    }

    public function hasBeginnerProtection($vid)
    {
        $result = $this->conn->select('users u', 'u.protect', 'INNER JOIN vdata v ON u.id=v.owner', 'v.wref = ?', [$vid]);

        if ($result && time() < $result['protect']) {
            return true;
        } else {
            return false;
        }
    }

    public function addCLP($uid, $clp)
    {
        $condition = ['id' => $uid];
        return $this->conn->update('users', ['clp' => 'clp + ?'], $condition, [$clp]);
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
        return $this->conn->insert('mdata', $data);
    }

    public function getLinks($id)
    {
        return $this->conn->select('links', 'url, name', ['userid' => $id], ['pos ASC']);
    }

    public function removeLinks($id, $uid)
    {
        $condition = ['id' => $id, 'userid' => $uid];
        return $this->conn->delete('links', $condition);
    }

    public function hasFarmlist($uid)
    {
        $result = $this->conn->select('id', 'farmlist', ['owner' => $uid], ['name ASC']);
        return !empty($result);
    }

    public function getRaidList($id)
    {
        return $this->conn->select('raidlist', ['id' => $id]);
    }

    public function getAllAuction()
    {
        return $this->conn->select('auction', ['finish' => 0]);
    }

    public function hasVillageFarmlist($wref)
    {
        $result = $this->conn->select('farmlist', 'id', ['wref' => $wref], ['wref ASC']);
        return !empty($result);
    }

    public function deleteFarmList($id, $owner)
    {
        return $this->conn->delete('farmlist', ['id' => $id, 'owner' => $owner]);
    }

    public function deleteSlotFarm($id)
    {
        return $this->conn->delete('raidlist', ['id' => $id]);
    }

    public function createFarmList($wref, $owner, $name)
    {
        $data = [
            'wref' => $wref,
            'owner' => $owner,
            'name' => $name
        ];
        return $this->conn->insert('farmlist', $data);
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
        return $this->conn->insert('raidlist', $data);
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
        return $this->conn->update('raidlist', $data, ['id' => $eid]);
    }

    public function removeOases($wref)
    {
        $data1 = [
            'conqured' => 0,
            'owner' => 3,
            'name' => UNOCCUPIEDOASES
        ];
        $r1 = $this->conn->update('odata', $data1, ['wref' => $wref]);
        $r2 = $this->conn->update('wdata', ['occupied' => 0], ['id' => $wref]);

        return ($r1 && $r2);
    }

    public function getArrayMemberVillage($uid)
    {
        $fields = ['a.wref', 'a.name', 'a.capital', 'b.x', 'b.y'];
        $joinTables = ['vdata AS a', 'wdata AS b'];
        $joinConditions = ['b.id' => 'a.wref'];
        $condition = ['owner' => $uid];
        $orderBy = 'capital DESC, pop DESC';
        return $this->conn->join($fields, $joinTables, $joinConditions, $condition, null, $orderBy);
    }

    public function getNoticeData($nid)
    {
        $result = $this->conn->select('ndata', 'data', ['id' => $nid]);
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
        return $this->conn->select('ndata', '*', $condition);
    }

    public function setSilver($uid, $silver, $mode)
    {
        if (!$mode) {
            $this->conn->update('users', ['silver' => "silver - $silver"], ['id' => $uid]);
            $this->conn->update('users', ['usedsilver' => "usedsilver + $silver"], ['id' => $uid]);
        } else {
            $this->conn->update('users', ['silver' => "silver + $silver"], ['id' => $uid]);
            $this->conn->update('users', ['Addsilver' => "Addsilver + $silver"], ['id' => $uid]);
        }
    }

    public function getAuctionSilver($uid)
    {
        return $this->conn->select('auction', '*', ['uid' => $uid, 'finish' => 0]);
    }

    public function delAuction($id)
    {
        $aucData = $this->getAuctionData($id);
        $usedtime = AUCTIONTIME - ($aucData['time'] - time());
        if (($usedtime < (AUCTIONTIME / 10)) && !$aucData['bids']) {
            $this->modifyHeroItem($aucData['itemid'], 'num', $aucData['num'], 1);
            $this->modifyHeroItem($aucData['itemid'], 'proc', 0, 0);
            return $this->conn->delete('auction', ['id' => $id, 'finish' => 0]);
        } else {
            return false;
        }
    }

    public function getAuctionData($id)
    {
        return $this->conn->select('auction', '*', ['id' => $id]);
    }

    public function modifyHeroItem($id, $column, $value, $mode)
    {
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
        return $this->conn->update('heroitems', $data, ['id' => $id]);
    }

    public function getAuctionUser($uid)
    {
        return $this->conn->select('auction', '*', ['owner' => $uid]);
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
        return $this->conn->insert('auction', $data);
    }

    public function getHeroItem($id = 0, $uid = 0, $btype = 0, $type = 0, $proc = 2)
    {
        $conditions = [];
        if ($id) {
            $conditions['id'] = $id;
        }
        if ($uid) {
            $conditions['uid'] = $uid;
        }
        if ($btype) {
            $conditions['btype'] = $btype;
        }
        if ($type) {
            $conditions['type'] = $type;
        }
        if ($proc != 2) {
            $conditions['proc'] = $proc;
        }

        $result = $this->conn->select('heroitems', $conditions);

        if ($id) {
            return isset($result) ? $result : [];
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
        return $this->conn->update('auction', $data, ['id' => $id]);
    }

    public function removeBidNotice($id)
    {
        return $this->conn->delete('auction', ['id' => $id]);
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
        return $this->conn->insert('heroitems', $data);
    }

    public function checkHeroItem($uid, $btype, $type = 0, $proc = 2)
    {
        $conditions = [];
        if ($uid) {
            $conditions['uid'] = $uid;
        }
        if ($btype) {
            $conditions['btype'] = $btype;
        }
        if ($type) {
            $conditions['type'] = $type;
        }
        if ($proc != 2) {
            $conditions['proc'] = $proc;
        }

        $result = $this->conn->select('heroitems', 'id, btype', $conditions);
        return $result ? $result['id'] : false;
    }

    public function editBid($id, $maxsilver, $minsilver)
    {
        $data = [
            'maxsilver' => $maxsilver,
            'silver' => $minsilver
        ];
        return $this->conn->update('auction', $data, ['id' => $id]);
    }

    public function getBidData($id)
    {
        return $this->conn->select('auction', '*', ['id' => $id]);
    }

    public function getFLData($id)
    {
        return $this->conn->select('farmlist', '*', ['id' => $id]);
    }

    public function getHeroField($uid, $field)
    {
        $result = $this->conn->select('hero', $field, ['uid' => $uid]);
        return $result[$field] ?? null;
    }

    public function getCapBrewery($uid)
    {
        $capWref = $this->getVFH($uid);
        if ($capWref) {
            $dbarray = $this->conn->select('fdata', '*', ['vref' => $capWref]);
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
        $result = $this->conn->select('vdata', 'wref', ['owner' => $uid, 'capital' => 1]);
        return $result['wref'] ?? null;
    }

    public function getNotice2($id, $field)
    {
        $result = $this->conn->select('ndata', $field, ['id' => $id]);
        return $result[$field] ?? null;
    }

    public function addAdventure($wref, $uid, $time = 0, $dif = 0)
    {
        if ($time == 0) {
            $time = time();
        }

        $lastWorld = $this->conn->select('wdata', 'id', ['ORDER BY id DESC LIMIT 1']);

        if (($wref - 10000) <= 10) {
            $w1 = rand(10, ($wref + 10000));
        } elseif (($wref + 10000) >= $lastWorld) {
            $w1 = rand(($wref - 10000), ($lastWorld - 10000));
        } else {
            $w1 = rand(($wref - 10000), ($wref + 10000));
        }

        $data = [
            'wref' => $w1,
            'uid' => $uid,
            'dif' => $dif,
            'time' => $time,
            'end' => 0
        ];

        return $this->conn->insert('adventure', $data);
    }

    public function addHero($uid)
    {
        $time = time();

        $hash = md5($time);

        $tribe = $this->getUserField($uid, 'tribe', 0);

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

        return $this->conn->insert('hero', $heroData);
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

        return $this->conn->insert('newproc', $data);
    }

    public function checkProcExist($uid)
    {
        return !$this->conn->select('newproc', ['uid' => $uid, 'proc' => 0]);
    }

    public function removeProc($uid)
    {
        return $this->conn->delete('newproc', ['uid' => $uid]);
    }

    public function checkBan($uid)
    {
        $user = $this->conn->select('users', ['id' => $uid]);

        if (!empty($user) && ($user['access'] <= 1 /*|| $user['access']>=7*/)) {
            return true;
        } else {
            return false;
        }
    }

    public function getNewProc($uid)
    {
        return $this->conn->select('newproc', 'npw, act', ['uid' => $uid]);
    }

    public function checkAdventure($uid, $wref, $end)
    {
        return $this->conn->select('adventure', 'id', ['uid' => $uid, 'wref' => $wref, 'end' => $end]);
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
        return $this->conn->select('adventure', $conditions, ['id', 'dif']);
    }

    public function editTableField($table, $field, $value, $refField, $ref)
    {
        return $this->conn->update($table, [$field => $value], [$refField => $ref]);
    }

    public function config()
    {
        return $this->conn->select('config');
    }

    public function getAllianceDipProfile($aid, $type)
    {
        $conditions1 = ['alli1' => $aid, 'type' => $type, 'accepted' => 1];
        $conditions2 = ['alli2' => $aid, 'type' => $type, 'accepted' => 1];
        $allianceLinks = '';

        $alliances1 = $this->conn->select('diplomacy', $conditions1, 'alli2');
        $alliances2 = $this->conn->select('diplomacy', $conditions2, 'alli1');

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
        if (!$id) {
            return 0;
        }

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

        return $this->conn->select('alidata', 'id, tag, desc, max, name, notice', $where);
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
        $owner = $this->getVillageField($wref, 'owner');
        $bonus = $this->getUserField($owner, 'b4', 0);
        $buildarray = $this->getResourceLevel($wref);

        $basecrop = $grainmill = $bakery = 0;

        foreach ($buildarray as $field => $value) {
            $fieldType = $value['t'];
            switch ($fieldType) {
                case 4:
                    $basecrop += $this->bid4[$value]['prod'];
                    break;
                case 8:
                    $grainmill = $value;
                    break;
                case 9:
                    $bakery = $value;
                    break;
            }
        }

        $cropo = 0;
        $crop = 0;
        $oasisTypes = [3, 6, 9, 10, 11, 12];
        $oasisTypesStr = implode(', ', $oasisTypes);
        $oases = $this->conn->select('odata', 'type', 'conqured = :wref AND type IN (' . $oasisTypesStr . ')', [':wref' => $wref]);
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

        if ($grainmill || $bakery) {
            $crop += ($basecrop / 100) * ($this->bid8[$grainmill]['attri'] + $this->bid9[$bakery]['attri']);
        }

        if ($bonus > time()) {
            $crop *= 1.25;
        }

        $crop *= setting('speed');;

        return $crop;
    }

    public function getNatarsProgress()
    {
        return $this->conn->selectFirst("natarsprogress");
    }

    public function setNatarsProgress($field, $value)
    {
        return $this->conn->update("natarsprogress", [$field => $value], "");
    }

    public function getNatarsCapital()
    {
        return $this->conn->select("vdata", "wref", "owner = 2 AND capital = 1", ["created ASC"]);
    }

    public function getNatarsWWVillages()
    {
        return $this->conn->select("vdata", "owner", "owner = 2 AND name = 'WW Village'", ["created ASC"]);
    }

    public function addNatarsVillage($wid, $uid, $username, $capital)
    {
        $total = $this->conn->count("vdata", "*", "owner = '$uid'");
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
        return $this->conn->insert("vdata", $data);
    }

    public function instantTrain($vref)
    {
        $count = $this->conn->count("training", "*", "vref = '$vref'");
        $this->conn->update("training", ['commence' => 0, 'eachtime' => 1, 'endat' => 0, 'timestamp' => 0], "vref = '$vref'");
        return $count;
    }

    public function hasWinner()
    {
        return $this->conn->count("fdata", "*", "f99 = '100' AND f99t = '40'") > 0;
    }

    public function getVillageActiveArte($vref)
    {
        $conqueredThreshold = time() - max(86400 / setting('speed'), 600);
        return $this->conn->select("artefacts", "*", "vref = :vref AND status = 1 AND conquered <= :conquered", [":vref" => $vref, ":conquered" => $conqueredThreshold]);
    }

    public function getAccountActiveArte($owner)
    {
        $conqueredThreshold = time() - max(86400 / setting('speed'), 600);
        return $this->conn->select("artefacts", "*", "owner = :owner AND status = 1 AND conquered <= :conquered", [":owner" => $owner, ":conquered" => $conqueredThreshold]);
    }

    public function getArtEffMSpeed($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 4);
        if ($res != 0) {
            $artEff = $res;
        }
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
            $conqueredThreshold = $currentTime - max(86400 / setting('speed'), 600);
            $result = $this->conn->select("artefacts", "vref, effect, aoe", "owner = :owner AND effecttype = :type AND status = 1 AND conquered <= :conquered", [":owner" => $owner, ":type" => $type, ":conquered" => $conqueredThreshold], "conquered DESC");
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

    public
    function updateFoolArtes()
    {
        $currentTime = time();
        $conqueredThreshold = $currentTime - max(86400 / setting('speed'), 600);

        $sql = "SELECT id,size FROM artefacts WHERE type = 3 AND status = 1 AND conquered <= :conqueredThreshold AND lastupdate <= :currentTime";
        $params = [":conqueredThreshold" => $conqueredThreshold, ":currentTime" => $currentTime];

        $result = $this->conn->executeQuery($sql, $params);

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

                $updateSql = "UPDATE artefacts SET effecttype = :effecttype, effect = :effect, aoe = :aoe WHERE id = :id";
                $updateParams = [":effecttype" => $effecttype, ":effect" => $effect, ":aoe" => $aoe, ":id" => $r['id']];

                $this->conn->executeQuery($updateSql, $updateParams);
            }
        }
    }

    public
    function getArtEffDiet($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 6);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public
    function getArtEffGrt($wref)
    {
        $artEff = 0;
        $res = $this->getArteEffectByType($wref, 9);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public
    function getArtEffArch($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 2);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public
    function getArtEffSpy($wref)
    {
        $artEff = 0;
        $res = $this->getArteEffectByType($wref, 5);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public
    function getArtEffTrain($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 8);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public
    function getArtEffConf($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 7);
        if ($res != 0) $artEff = $res;
        return $artEff;
    }

    public
    function getArtEffBP($wref)
    {
        $artEff = 0;
        $village = $this->getVillage($wref);
        $owner = $village['owner'];

        $conditions = ["owner" => $owner, "effecttype" => 11, "status" => 1, "conquered[<=]" => time() - max(86400 / setting('speed'), 600)];
        $result = $this->conn->select("artefacts", "COUNT(*) as count", $conditions);

        if ($result['count'] > 0) {
            $artEff = 1;
        }

        return $artEff;
    }

    public
    function getArtEffAllyBP($uid)
    {
        $artEff = 0;
        $userAlli = $this->getUserField($uid, 'alliance', 0);
        $q = 'SELECT alli1,alli2 FROM diplomacy WHERE alli1 = :userAlli OR alli2 = :userAlli AND accepted <> 0';
        $params = [":userAlli" => $userAlli];
        $diplos = $this->conn->executeQuery($q, $params);

        if (!empty($diplos) && count($diplos) > 0) {
            $alliances = [];
            foreach ($diplos as $ds) {
                $alliances[] = $ds['alli1'];
                $alliances[] = $ds['alli2'];
            }
            $alliances = array_unique($alliances);
            $allianceStr = implode(',', $alliances);
            $q = 'SELECT id FROM users WHERE alliance IN (' . $allianceStr . ') AND id <> :uid';
            $params = [":uid" => $uid];
            $mate = $this->conn->executeQuery($q, $params);

            if (!empty($mate) && count($mate) > 0) {
                $mateIds = [];
                foreach ($mate as $ms) {
                    $mateIds[] = $ms['id'];
                }
                $mateStr = implode(',', $mateIds);
                $q = 'SELECT id FROM artefacts WHERE owner IN (' . $mateStr . ') AND effecttype = 11 AND status = 1 AND conquered <= :conqueredThreshold ORDER BY conquered DESC';
                $result = $this->conn->executeQuery($q, $params);

                if (!empty($result) && count($result) > 0) {
                    return 1;
                }
            }
        }
        return $artEff;
    }

    public
    function modifyExtraVillage($wid, $column, $value)
    {
        return $this->conn->update('vdata', [$column => "$column + :value"], ['wref' => $wid], [':value' => $value]);
    }

    public
    function modifyFieldLevel($wid, $field, $level, $mode)
    {
        $columnName = 'f' . $field;
        $operation = $mode ? '+' : '-';
        $values = [$columnName => "$columnName $operation :level"];

        return $this->conn->update('fdata', $values, ['vref' => $wid], [':level' => $level]);
    }

    public
    function modifyFieldType($wid, $field, $type)
    {
        return $this->conn->update('fdata', ['f' . $field . 't' => $type], ['vref' => $wid]);
    }

    public
    function resendAct($mail)
    {
        return $this->conn->select('users', '*', ['email' => $mail]);
    }

    public
    function changemail($mail, $id)
    {
        return $this->conn->update('users', ['email' => $mail], ['id' => $id]);
    }

    public
    function register2($username, $password, $email, $act, $activateat)
    {
        $time = time();
        if (strtotime(START_TIME) > $time) {
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

        return $this->conn->insert('users', $data) ? $this->conn->getLastInsertId() : null;
    }

    public function checkname($id)
    {
        return $this->conn->select('users', ['username', 'email'], ['id' => $id]);
    }

    public function settribe($tribe, $id)
    {
        return $this->conn->update('users', ['tribe' => $tribe], ['id' => $id, 'reg2' => 1]);
    }

    public function checkreg($uid)
    {
        return $this->conn->select('users', 'reg2', ['id' => $uid]);
    }

    public function checkReg2($name)
    {
        return $this->conn->select('users', 'reg2', ['username' => $name]);
    }

    public function checkID($name)
    {
        return $this->conn->select('users', 'id', ['username' => $name]);
    }

    public function setReg2($id)
    {
        return $this->conn->update('users', ['reg2' => 0], ['id' => $id, 'reg2' => 1]);
    }

    public function getNotice5($uid)
    {
        return $this->conn->select('ndata', 'id', ['uid' => $uid, 'viewed' => 0], ['time DESC']);
    }

    public function setRef($id, $name)
    {
        return $this->conn->insert('reference', ['id' => $id, 'name' => $name]) ? $this->conn->getLastInsertId() : null;
    }

    public function getAttackCasualties($time)
    {
        $result = $this->conn->select('general', 'time, casualties', ['shown' => 1]);
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
        $result = $this->conn->select('general', ['time'], ['shown' => 1]);
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
        $result = $this->conn->select('stats', [$inf, 'time'], ['owner' => $uid]);
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
        return $this->conn->update('hero', $data, ['uid' => $uid], [":value" => $value]);
    }

    public function createTradeRoute($uid, $wid, $from, $r1, $r2, $r3, $r4, $start, $deliveries, $merchant, $time)
    {
        $this->conn->update('users', ['gold' => 'gold - 2'], ['id' => $uid]);

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

        return $this->conn->insert('route', $data);
    }

    public function getTradeRoute($uid)
    {
        return $this->conn->select('route', ['uid' => $uid], 'timestamp ASC');
    }

    public function getTradeRoute2($id)
    {
        return $this->conn->select('route', '*', ['id' => $id]);
    }

    public function getTradeRouteUid($id)
    {
        $routeData = $this->conn->select('route', 'uid', ['id' => $id]);
        return $routeData['uid'];
    }

    public function editTradeRoute($id, $column, $value, $mode)
    {
        if (!$mode) {
            return $this->conn->update('route', [$column => $value], "id = :id", [":id" => $id]);
        } else {
            return $this->conn->update('route', [$column => "$column + :value"], "id = :id", [":id" => $id, ":value" => $value]);
        }
    }

    public function deleteTradeRoute($id)
    {
        return $this->conn->delete('route', ['id' => $id]);
    }

    public function getHeroData($uid)
    {
        return $this->conn->select("hero", "*", "uid = :uid", [":uid" => $uid]);
    }

    public function getHeroData2($uid)
    {
        return $this->conn->select("hero", "heroid", "dead = 0 AND uid = :uid", [":uid" => $uid]);
    }

    public function getHeroInVillid($uid, $mode)
    {
        $villageData = $this->conn->select("vdata", "wref, name", "owner = :uid", [":uid" => $uid]);
        $name = null;

        foreach ($villageData as $row) {
            $unitHero = $this->conn->select("units", "hero", "vref = :wref", [":wref" => $row['wref']]);
            if ($unitHero['hero'] == 1) {
                $name = $mode ? $row['name'] : $row['wref'];
            }
        }
        return $name;
    }
}