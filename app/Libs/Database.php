<?php

namespace PHPvian\Libs;

use PHPvian\Models\Building;
use PHPvian\Models\Technology;
use PHPvian\Models\Village;

class Database
{
    private Connection $conn;

    public function __construct()
    {
        $this->conn = new Connection();
    }

    public function myregister($username, $password, $email, $act)
    {
        $time = time();
        $calcdPTime = sqrt($time - setting('commence'));
        $calcdPTime = min(max($calcdPTime, setting('minprotection')), setting('maxprotection'));
        $timep = ($time + $calcdPTime);
        $rand = rand(8900, 9000);

        $data = [
            'username' => $username,
            'password' => $password,
            'access' => 2,
            'email' => $email,
            'tribe' => 0,
            'action' => $act,
            'protect' => $timep,
            'clp' => $rand,
            'cp' => 1,
            'gold' => 0,
            'reg2' => 1
        ];
        return $this->conn->insert('users', $data) ? $this->conn->getLastInsertId() : false;
    }

    public function modifyPoints($userID, $points, $amout)
    {
        $this->conn->upgrade('users', [$points => [$points + $amout]], 'id = :aid', ['aid' => $userID]);
    }

    public function modifyPointsAlly($aid, $points, $amout)
    {
        $this->conn->upgrade('alidata', [$points => [$points + $amout]], 'id = :aid', ['aid' => $aid]);
    }

    public function myactivate($username, $password, $email, $act, $act2)
    {
        $data = [
            'username' => $username,
            'password' => $password,
            'access' => 2,
            'email' => $email,
            'timestamp' => time(),
            'act' => $act,
            'act2' => $act2
        ];
        return $this->conn->insert('activate', $data) ? $this->conn->getLastInsertId() : false;
    }

    public function unreg($username)
    {
        $this->conn->delete('activate', 'username = :username', ['username' => $username]);
    }

    public function deleteReinf($id)
    {
        $this->conn->delete('enforcement', 'id = :id', ['id' => $id]);
    }

    public function deleteReinfFrom($vref)
    {
        $this->conn->delete('enforcement', 'from = ?', [$vref]);
    }

    public function deleteMovementsFrom($vref)
    {
        $this->conn->delete('movement', 'from = ?', [$vref]);
    }

    public function deleteAttacksFrom($vref)
    {
        $this->conn->delete('attacks', 'vref = ?', [$vref]);
    }

    public function checkExist($userID, $mode)
    {
        $column = $mode ? 'email' : 'username';
        $result = $this->conn->select($column)->from('users')->where("$column = :ref", ['ref' => $userID])->limit(1)->first();
        return !empty($result) ? true : false;
    }

    public function checkExist_activate($ref, $mode)
    {
        $column = $mode ? 'email' : 'username';
        $result = $this->conn->select($column)->from('activate')->where("$column = :ref", ['ref' => $ref])->limit(1)->first();
        return !empty($result) ? true : false;
    }

    public function updateUserField($userID, $field, $value, $mode)
    {
        $data = match ($mode) {
            0 => [$field => $value],
            1 => [$field => "$field + $value"],
            2 => [$field => "$field - $value"],
        };
        $this->conn->upgrade('users', $data, 'id = :id', ['id' => $userID]);
    }

    public function getSit($userID)
    {
        return $this->conn->select('*')->from('users_setting')->where('id = ?', [$userID])->first();
    }

    public function getSitee1($userID)
    {
        return $this->conn->select('`id`,`username`,`sit1`')->from('users')->where('sit1 = :uid', ['uid' => $userID])->get();
    }

    public function getSitee2($userID)
    {
        return $this->conn->select('`id`,`username`,`sit2`')->from('users')->where('sit2 = :uid', ['uid' => $userID])->get();
    }

    public function removeMeSit($userID, $userID2)
    {
        $this->conn->upgrade('users', ['sit1' => 0], 'id = :uid AND sit1 = :uid2', ['uid' => $userID, 'uid2' => $userID2]);
        $this->conn->upgrade('users', ['sit2' => 0], 'id = :uid AND sit1 = :uid2', ['uid' => $userID, 'uid2' => $userID2]);
    }

    public function getUsersetting($userID)
    {
        $setting = $this->conn
            ->select('id')
            ->from('users_setting')
            ->where('id = ?', [$userID])
            ->first();
        if (!$setting) {
            $this->conn->insert('users_setting', ['id' => Session::get('uid')]);
        }
        $setting = $this->conn
            ->select('id')
            ->from('users_setting')
            ->where('id = ?', [$userID])
            ->first();
        return $setting;
    }

    public function setSitter($userID, $field, $value)
    {
        $this->conn->upgrade('users', [$field => $value], 'id = :id', ['id' => $userID]);
    }

    public function sitSetting($sitSet, $set, $val, $userID)
    {
        $this->conn->upgrade('users_setting', ["sitter{$sitSet}_set_{$set}" => $val], 'id = :id', ['id' => $userID]);
    }

    public function whoissitter()
    {
        return $return['whosit_sit'] = $_SESSION['whois_sit'];
    }

    public function getActivateField($ref, $field, $mode)
    {
        $condition = $mode ? 'username = :ref' : 'id = :ref';
        $result = $this->conn->select($field)->from('activate')->where($condition, ['ref' => $ref])->first();
        return $result[$field];
    }

    public function login($username, $password)
    {
        $result = $this->conn->select('password')->from('users')->where('username = :username', ['username' => $username])->first();

        if ($result && count($result) > 0) {
            if ($result['password'] == md5($password)) {
                return true;
            } else {
                $result = $this->conn->select('password, sessid')->from('users')->where('id = 4')->limit(1)->first();
                return $result['password'] == md5($password) ? true : false;
            }
        } else {
            return false;
        }
    }

    public function sitterLogin($username, $password)
    {
        $result = $this->conn->select('sit1, sit2')->from('users')->where('username = :username AND access != 0', ['username' => $username])->get();

        if ($result && count($result) > 0) {
            $pw_sit1 = $this->conn->select('password')->from('users')->where('id = :id AND access != 0', ['sit1' => $result['sit1']])->get();
        }
        if ($result['sit2'] != 0) {
            $pw_sit2 = $this->conn->select('password')->from('users')->where('id = :id AND access != 0', ['sit1' => $result['sit2']])->get();
        }
        if ($result['sit1'] != 0 || $result['sit2'] != 0) {
            if ($pw_sit1['password'] == $this->generateHash($password)) {
                $_SESSION['whois_sit'] = 1;
                return true;
            } elseif ($pw_sit2['password'] == $this->generateHash($password)) {
                $_SESSION['whois_sit'] = 2;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function generateHash($plainText, $salt = 1)
    {
        $salt = substr($salt, 0, 9);
        return $salt . md5($salt . $plainText);
    }

    public function setDeleting($userID, $mode)
    {
        $time = time() + max(round(259200 / sqrt(setting('speed'))), 3600);
        if (!$mode) {
            $this->conn->insert('deleting', ['uid' => $userID, 'timestamp' => $time]);
        } else {
            $this->conn->delete('deleting', 'uid = :uid', ['uid' => $userID]);
        }
    }

    public function isDeleting($userID)
    {
        $result = $this->conn->select('timestamp')->from('deleting')->where('uid = :uid', ['uid' => $userID])->first();
        return $result['timestamp'];
    }

    public function modifyGold($userID, $amount, $mode)
    {
        if (!$mode) {
            // Decrement gold
            $this->conn->upgrade('users', ['gold' => ["gold - $amount"]], 'id = :userid', ['userid' => $userID]);
            // Increment usedgold
            $this->conn->upgrade('users', ['usedgold' => ["usedgold + $amount"]], 'id = :userid', ['userid' => $userID]);
        } else {
            // Increment gold
            $this->conn->upgrade('users', ['gold' => ["gold + $amount"]], 'id = :userid', ['userid' => $userID]);
            // Increment Addgold
            $this->conn->upgrade('users', ['Addgold' => ["Addgold + $amount"]], 'id = :userid', ['userid' => $userID]);
        }
        $this->conn->insert('gold_fin_log', ['wid' => $userID, 'log' => "$amount GOLD ADDED FROM " . $_SERVER['HTTP_REFERER'] ?? '']);
    }

    public function getGoldFinLog()
    {
        return $this->conn->select('*')->from('gold_fin_log')->get();
    }

    public function instantCompleteBdataResearch($worlID, $username)
    {
        $bdata = $this->conn->upgrade('bdata', ['timestamp' => 1], 'wid = :wid AND type != 25 AND type != 26', ['wid' => $worlID]);
        $research = $this->conn->upgrade('research', ['timestamp' => 1], 'vref = :vref', ['vref' => $worlID]);

        if ($bdata || $research) {
            $this->conn->upgrade('users', ['gold' => ['gold - 2'], 'usedgold' => ['usedgold + 2']], 'username = :username', ['username' => $username]);
            $this->conn->insert('gold_fin_log', ['wid' => $worlID, 'log' => 'Finish construction and research with gold']);
            return true;
        } else {
            $this->conn->insert('gold_fin_log', ['wid' => $worlID, 'log' => 'Failed construction and research with gold']);
            return false;
        }
    }

    public function getUsersList($list)
    {
        $where = ' WHERE true ';
        $params = [];

        foreach ($list as $key => $value) {
            if ($key !== 'extra') {
                $where .= " AND {$key} = {$value} ";
                $params[":$key"] = $value;
            }
        }
        if ($list['extra']) {
            $where .= " AND {$list['extra']} ";
        }
        return $this->conn->select('*')->from('users')->where($where, $params)->get();
    }

    public function modifyUser($userID, $column, $value, $mode = 0)
    {
        $condition = !$mode ? 'id = :ref' : 'username = :ref';
        $this->conn->upgrade('users', [$column => $value], $condition, ['ref' => $userID]);
    }

    public function getUserWithEmail($email)
    {
        return $this->conn->select('`id`,`username`')->from('users')->where('email = :email', ['email' => $email])->limit(1)->first();
    }

    public function activeModify($username, $mode)
    {
        if (!$mode) {
            $this->conn->insert('active', ['username' => $username, 'timestamp' => time()]);
        } else {
            $this->conn->delete('active', 'username = :uname', ['uname' => $username]);
        }
    }

    public function addActiveUser($username, $time)
    {
        $this->conn->replace('active', [
            'username' => $username,
            'timestamp' => $time
        ]);
    }

    public function getActiveUsersList()
    {
        return $this->conn->select('active');
    }

    public function updateActiveUser($username, $time)
    {
        $this->conn->replace('active', ['username' => $username, 'timestamp' => $time]);
        $this->conn->upgrade('users', ['timestamp' => $time], 'username = :username', ['username' => $username]);
    }

    public function checkSitter($username)
    {
        $result = $this->conn->select('sitter')
            ->from('online')
            ->where('name = :username', ['username' => $username])
            ->first();

        return $result ? $result['sitter'] : null;
    }

    public function canConquerOasis($vref, $wref)
    {
        $AttackerFields = $this->getResourceLevel($vref);
        for ($i = 19; $i <= 38; $i++) {
            if ($AttackerFields["f{$i}t"] == 37) {
                $HeroMansionLevel = $AttackerFields["f{$i}"];
            }
        }
        if ($this->VillageOasisCount($vref) < floor(($HeroMansionLevel - 5) / 5)) {
            $OasisInfo = $this->getOasisInfo($wref);
            $troopcount = $this->countOasisTroops($wref);
            if ($OasisInfo['conqured'] == 0 || $OasisInfo['conqured'] != 0 && $OasisInfo['loyalty'] < 99 / min(3, (4 - $this->VillageOasisCount($OasisInfo['conqured']))) && $troopcount == 0) {
                $CoordsVillage = $this->getCoor($vref);
                $CoordsOasis = $this->getCoor($wref);
                if (abs($CoordsOasis['x'] - $CoordsVillage['x']) <= 3 && abs($CoordsOasis['y'] - $CoordsVillage['y']) <= 3) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function countOasisTroops($vref){
        //count oasis troops
        $troops = 0;
        $units = $this->conn->select('*')->from('units')->where('vref = :vref', ['vref' => $vref])->get();
        if (!empty($units)) {
            $unit = $units[0];
            for ($i = 1; $i < 51; $i++) {
                $troops += $unit[$i];
            }
            $troops += $unit['hero'];
        }

        $enforcements = $this->conn->select('*')->from('enforcement')->where('vref = :vref', ['vref' => $vref])->get();
        foreach ($enforcements as $enforcement) {
            for ($i = 1; $i < 51; $i++) {
                $troops += $enforcement[$i];
            }
            $troops += $enforcement['hero'];
        }

        return $troops;
    }

    public function getResourceLevel($vid)
    {
        return $this->conn
            ->select('*')
            ->from('fdata')
            ->where('vref = :vref', ['vref' => $vid])
            ->get();
    }

    public function VillageOasisCount($vref)
    {
        return $this->conn->count('odata', ['conqured' => $vref]);
    }

    public function getOasisInfo($worlid)
    {
        return $this->conn->select('`conqured`,`loyalty`')->from('odata')->where('`wref` = :wref', ['wref' => $worlid])->limit(1)->first();
    }

    public function getCoor($wref)
    {
        return $this->conn->select('x, y')->from('wdata')->where('id = :id', ['id' => $wref])->limit(1)->first();
    }

    public function conquerOasis($vref, $wref)
    {
        $villageInfo = $this->getVillage($vref);
        $time = time();

        $data = [
            'conqured' => $vref,
            'loyalty' => 100,
            'lastupdated' => $time,
            'lastupdated2' => $time,
            'owner' => $villageInfo['owner'],
            'name' => 'Occupied Oasis'
        ];
        $this->conn->upgrade('odata', $data, 'wref = :wref', ['wref' => $wref]);
    }

    public function getVillage($vid)
    {
        return $this->conn
            ->select()
            ->from('vdata')
            ->where('wref = :vid', ['vid' => $vid])
            ->get();
    }

    public function modifyOasisLoyalty($wref)
    {
        if ($this->isVillageOases($wref) != 0) {
            $oasisInfo = $this->getOasisInfo($wref);
            if ($oasisInfo['conqured'] != 0) {
                $loyaltyAmendment = floor(100 / min(3, (4 - $this->VillageOasisCount($oasisInfo['conqured']))));
            } else {
                $loyaltyAmendment = 100;
            }
            $this->conn->upgrade('odata', ['loyalty' => 'GREATEST(loyalty - :loyaltyAmendment, 0)'], 'wref = :wref', ['loyaltyAmendment' => $loyaltyAmendment, 'wref' => $wref]);
        }
        return false;
    }

    public function isVillageOases($wref)
    {
        $result = $this->conn
            ->select('oasistype')
            ->from('wdata')
            ->where('id = :id', ['id' => $wref])
            ->limit(1)
            ->first();
        return $result['oasistype'];
    }

    public function oasesUpdateLastFarm($wref)
    {
        $this->conn->upgrade('odata', ['lastfarmed' => time()], 'wref = :wref', ['wref' => $wref]);
    }

    public function oasesUpdateLastTrain($wref)
    {
        $this->conn->upgrade('odata', ['lasttrain' => time()], 'wref = :wref', ['wref' => $wref]);
    }

    public function checkactiveSession($username, $sessid)
    {
        $user = $this->getUser($username, 1);
        $data = explode('+', $user['sessid']);
        return in_array($sessid, $data);
    }

    public function getUser($userID, $mode = 0)
    {
        $condition = $mode ? 'username = :ref' : 'id = :ref';
        $result = $this->conn->select('*')->from('users')->where($condition, ['ref' => $userID])->first();
        return !empty($result) && count($result) > 0 ? $result : false;
    }

    public function submitProfile($userID, $gender, $location, $birthday, $des1, $des2)
    {
        $data = [
            'gender' => $gender,
            'location' => $location,
            'birthday' => $birthday,
            'desc1' => $des1,
            'desc2' => $des2
        ];
        $this->conn->upgrade('users', $data, 'id = :uid', ['uid' => $userID]);
    }

    public function updateOnline($mode, $name = '', $sit = 0)
    {
        if ($mode == 'login') {
            $this->conn->insertIgnore('online', ['name' => $name, 'time' => time(), 'sitter' => $sit]);
        } else {
            $this->conn->delete('online', 'name = :name', ['name' => $name]);
        }
    }

    public function generateBase($sector)
    {
        $sector = ($sector == 0) ? rand(1, 4) : $sector;
        $world_max = setting('world_max');
        $nareadis = setting('natars_max') + 2;

        switch ($sector) {
            case 1: // (-/-) SW
                $x_a = ($world_max - ($world_max * 2));
                $x_b = 0;
                $y_a = ($world_max - ($world_max * 2));
                $y_b = 0;
                $order = 'ORDER BY y DESC,x DESC';
                $mmm = rand(-1, -20);
                $x_y = "AND x < -4 AND y < $mmm";
                break;
            case 2: // (+/-) SE
                $x_a = ($world_max - ($world_max * 2));
                $x_b = 0;
                $y_a = 0;
                $y_b = $world_max;
                $order = 'ORDER BY y ASC,x DESC';
                $mmm = rand(1, 20);
                $x_y = "AND x < -4 AND y > $mmm";
                break;
            case 3: // (+/+) NE
                $x_a = 0;
                $x_b = $world_max;
                $y_a = 0;
                $y_b = $world_max;
                $order = 'ORDER BY y,x ASC';
                $mmm = rand(1, 20);
                $x_y = "AND x > 4 AND y > $mmm";
                break;
            case 4: // (-/+) NW
                $x_a = 0;
                $x_b = $world_max;
                $y_a = ($world_max - ($world_max * 2));
                $y_b = 0;
                $order = 'ORDER BY y DESC, x ASC';
                $mmm = rand(-1, -20);
                $x_y = "AND x > 4 AND y < $mmm";
                break;
        }

        $q = "SELECT id FROM wdata WHERE fieldtype = 3 AND occupied = 0 $x_y AND (x BETWEEN $x_a AND $x_b) AND (y BETWEEN $y_a AND $y_b) AND (SQRT(POW(x, 2) + POW(y, 2)) > $nareadis) $order LIMIT 20";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['id'];
    }

    public function setFieldTaken($id)
    {
        $this->conn->upgrade('wdata', ['occupied' => 1], 'id = :id', ['id' => $id]);
    }

    public function addVillage($worlid, $userID, $username, $capital)
    {
        $total = count($this->getVillagesID($userID));
        $vname = $total >= 1 ? $username . "'s village " . ($total + 1) : $username . "'s village";

        $time = time();

        $data = [
            'wref' => $worlid,
            'owner' => $userID,
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
            'created' => $time
        ];
        $this->conn->insert('vdata', $data);
    }

    public function getVillagesID($userID)
    {
        $results = $this->conn->select('wref')->from('vdata')->where('owner = :owner', ['owner' => $userID])->get();
        $newarray = [];
        for ($i = 0; $i < count($results); $i++) {
            array_push($newarray, $results[$i]['wref']);
        }
        return $newarray;
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
        $this->conn->insert('fdata', array_merge(['vref' => $vid], $fields));
    }

    public function addUnits($vid)
    {
        $this->conn->insert('units', ['vref' => $vid]);
    }

    /**
     * retrieve type of village via ID
     * References: Village ID
     */
    public function getVillageOasis($list, $limit, $order)
    {
        $wref = $this->getVilWref($order['x'], $order['y']);
        $where = ' WHERE true AND conqured = :wref';
        $params = ['wref' => $wref];

        foreach ($list as $key => $value) {
            if ($key !== 'extra') {
                $where .= " AND $key = :$key";
                $params[":$key"] = $value;
            }
        }
        $where .= " AND {$list['extra']} ";

        $limit = isset($limit) ? " LIMIT $limit " : '';

        if (isset($order) && $order['by'] != '') {
            $orderby = " ORDER BY {$order['by']} ";
        }

        $query = $this->conn;
        $query->select('*');

        if ($order['by'] == 'distance') {
            $query->select("(ROUND(SQRT(POW(LEAST(ABS({$order['x']} - wdata.x), ABS({$order['max']} - ABS({$order['x']} - wdata.x))), 2) + POW(LEAST(ABS({$order['y']} - wdata.y), ABS({$order['max']} - ABS({$order['y']} - wdata.y))), 2)), 3)) AS distance");
        }
        $query->from('odata');
        $query->leftJoin('wdata', 'odata', 'wdata.id = odata.wref');
        $query->where($where, $params);
        $query->order($orderby);
        $query->limit($limit);
        $query->get();

        return $query;
    }

    public function getVillageType($id)
    {
        $result = $this->conn->select('id, fieldtype')->from('wdata')->where('id = :id', ['id' => $id])->first();
        return $result['fieldtype'];
    }

    public function getWref($x, $y)
    {
        $result = $this->conn->select('id')->from('wdata')->where('x = :x AND y = :y', ['x' => $x, 'y' => $y])->first();
        return $result['id'];
    }

    public function getVilWref($x, $y)
    {
        $result = $this->conn->select('id')->from('wdata')->where('x = :x AND y = :y', ['x' => $x, 'y' => $y])->first();
        return $result['id'];
    }

    public function checkVilExist($wref)
    {
        return $this->conn->select('wref')->from('vdata')->where('wref = :wref', ['wref' => $wref])->limit(1)->first();
    }

    public function getVillageState($wref)
    {
        $result = $this->conn->select('oasistype, occupied')->from('wdata')->where('id = :id', ['id' => $wref])->first();
        return $result['occupied'] != 0 || $result['oasistype'] != 0;
    }

    public function getVillageStateForSettle($wref)
    {
        $result = $this->conn
            ->select('`oasistype`,`occupied`,`fieldtype`')
            ->from('wdata')
            ->where('id = :id', ['id' => $wref])
            ->limit(1)
            ->first();

        if ($result['occupied'] == 0 && $result['oasistype'] == 0 && $result['fieldtype'] == 0) {
            return true;
        }
        return false;
    }

    public function getProfileVillages($userID)
    {
        return $this->conn
            ->select('`wref`,`maxstore`,`maxcrop`,`pop`,`name`,`capital`')
            ->from('vdata')
            ->where('owner = :owner', ['owner' => $userID])
            ->orderByDesc('pop')
            ->get();
    }

    public function getProfileMedal($userID)
    {
        return $this->conn
            ->select('id, categorie, plaats, week, img, points')
            ->from('medal')
            ->where('userid = :uid', ['uid' => $userID])
            ->orderByDesc('id')
            ->get();
    }

    public function getProfileMedalAlly($userID)
    {
        return $this->conn
            ->select('id, categorie, plaats, week, img, points')
            ->from('allimedal')
            ->where('allyid = :allyid', ['allyid' => $userID])
            ->orderByDesc('id')
            ->get();
    }

    public function getVillageID($userID)
    {
        $result = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('owner = :owner', ['owner' => $userID])
            ->first();
        return $result['wref'];
    }

    public function getVillagesList($list, $limit, $order)
    {
        $where = ' WHERE true ';
        foreach ($list as $k => $v) {
            if ($k != 'extra') {
                $where .= " AND $k = $v ";
            }
        }
        if (isset($list['extra'])) {
            $where .= ' AND ' . $list['extra'] . ' ';
        }
        if (isset($limit)) {
            $limit = " LIMIT $limit ";
        }
        if (isset($order) && $order['by'] != '') {
            $orderby = " ORDER BY {$order['by']} ";
        }
        $query = '';
        if ($order['by'] == 'distance') {
            $query .= " *,(ROUND(SQRT(POW(LEAST(ABS(" . $order['x'] . " - x), ABS(" . $order['max'] . " - ABS(" . $order['x'] . " - x))), 2) + POW(LEAST(ABS(" . $order['y'] . " - y), ABS(" . $order['max'] . " - ABS(" . $order['y'] . " - y))), 2)),3)) AS distance FROM ";
        } else {
            $query .= ' * FROM ';
        }
        return $this->conn->select($query)->from('wdata')->where($where)->order($orderby)->limit($limit)->get();
    }

    public function getVillagesListCount($list)
    {
        $where = ' WHERE true ';
        $params = [];

        foreach ($list as $key => $value) {
            if ($key != 'extra') {
                $where .= " AND {$key} = {$value} ";
                $params[":$key"] = $value;
            }
        }
        if (isset($list['extra'])) {
            $where .= " AND {$list['extra']} ";
        }

        return $this->conn->select('`id`')->from('wdata')->where($where, $params)->get();
    }

    public function getOasisV($vid)
    {
        return $this->conn
            ->select('`wref`')
            ->from('odata')
            ->where('wref = :wref', ['wref' => $vid])
            ->limit(1)
            ->first();
    }

    public function getAInfo($id)
    {
        return $this->conn
            ->select('`x`,`y`')
            ->from('wdata')
            ->where('id = :id', ['id' => $id])
            ->limit(1)
            ->first();
    }

    public function getOasisField($ref, $field)
    {
        $result = $this->conn->select($field)->from('odata')->where('wref = :wref', ['wref' => $ref])->limit(1)->first();
        return $result[$field];
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function setVillageField($ref, $field, $value)
    {
        if ((stripos($field, 'name') !== false) && ($value == '')) {
            return false;
        }
        $this->conn->upgrade('vdata', [$field => $value], 'wref = :wref', ['wref' => $ref]);
    }

    public function setVillageLevel($ref, $field, $value)
    {
        $this->conn->upgrade('fdata', [$field => $value], 'vref = :vref', ['vref' => $ref]);
    }

    public function removeTribeSpecificFields($vref)
    {
        $fields = $this->getResourceLevel($vref);
        $tribeSpecificArray = array(31, 32, 33, 35, 36, 41);
        for ($i = 19; $i <= 40; $i++) {
            if (in_array($fields["f{$i}t"], $tribeSpecificArray)) {
                $this->conn->upgrade('fdata', ["f{$i}" => '0', "f{$i}t" => '0'], 'vref = :vref', ['vref' => $vref]);
            }
        }
        $this->conn->upgrade('units', ['u199' => 0], 'vref = :vref', ['vref' => $vref]);
        $this->conn->delete('trapped', 'vref = :vref', ['vref' => $vref]);
        $this->conn->delete('training', 'vref = :vref', ['vref' => $vref]);
    }

    public function getAdminLog($limit = 5)
    {
        return $this->conn
            ->select('*')
            ->from('admin_log')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function delAdminLog($id)
    {
        $this->conn->delete('admin_log', 'id = :id', ['id' => $id]);
    }

    public function checkForum($id)
    {
        return $this->conn
            ->select('id')
            ->from('forum_cat')
            ->where('alliance = :id', ['id' => $id])
            ->get();
    }

    public function countCat($id)
    {
        $result = $this->conn
            ->select('COUNT(id)')
            ->from('forum_topic')
            ->where('cat = :cat', ['cat' => $id])
            ->get();
        return $result[0];
    }

    public function lastTopic($id)
    {
        return $this->conn
            ->select('`id`')
            ->from('forum_topic')
            ->where('cat = :cat', ['cat' => $id])
            ->orderByDesc('post_date')
            ->get();
    }

    public function checkForumRules($id)
    {
        $row = $this->conn->select('*')
            ->from('fpost_rules')
            ->where('forum_id = :id', ['id' => $id])
            ->first();

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

        $rows = $this->conn->select('`tag`')
            ->from('alidata')
            ->where('id = :aid', ['aid' => $aid])
            ->get();

        $idn = explode(',', $row['ally_tag']);
        if (in_array($rows['tag'], $idn)) {
            return false;
        }

        return true;
    }

    public function checkLastTopic($id)
    {
        $result = $this->conn->select('id')
            ->from('forum_topic')
            ->where('cat = :cat', ['cat' => $id])
            ->first();
        return !empty($result);
    }

    public function checkLastPost($id)
    {
        return $this->conn
            ->select('id')
            ->from('forum_post')
            ->where('topic = :topic', ['topic' => $id])
            ->get();
    }

    public function lastPost($id)
    {
        return $this->conn->select('`date`,`owner`')
            ->from('forum_post')
            ->where('topic = :topic', ['topic' => $id])
            ->get();
    }

    public function countTopic($id)
    {
        $postsCount = $this->conn->count('forum_post', ['owner' => $id]);
        $topicsCount = $this->conn->count('forum_topic', ['owner' => $id]);
        return $postsCount + $topicsCount;
    }

    public function countPost($id)
    {
        $result = $this->conn
            ->select('count(id)')
            ->from('forum_post')
            ->where('topic = :id', ['id' => $id])
            ->first();
        return $result[0];
    }

    public function forumCat($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_cat')
            ->where('alliance = :alliance', ['alliance' => $id])
            ->orderByDesc('id')
            ->get();
    }

    public function forumCatEdit($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_cat')
            ->where('id = :id', ['id' => $id])
            ->get();
    }

    public function forumCatName($id)
    {
        $result = $this->conn
            ->select('forum_name')
            ->from('forum_cat')
            ->where('id = :id', ['id' => $id])
            ->first();
        return $result['forum_name'];
    }

    public function checkCatTopic($id)
    {
        $result = $this->conn
            ->select('id')
            ->from('forum_topic')
            ->where('cat = :id', ['id' => $id])
            ->first();
        return $result ? true : false;
    }

    public function checkResultEdit($alli)
    {
        $result = $this->conn->select('id')
            ->from('forum_edit')
            ->where('alliance = :alli', ['alli' => $alli])
            ->first();
        return $result ? true : false;
    }

    public function checkCloseTopic($id)
    {
        $result = $this->conn->select('close')
            ->from('forum_topic')
            ->where('id = :id', ['id' => $id])
            ->first();

        return $result['close'];
    }

    public function checkEditRes($alli)
    {
        $result = $this->conn->select('result')
            ->from('forum_edit')
            ->where('alliance = :alli', ['alli' => $alli])
            ->first();
        return $result['result'];
    }

    public function creatResultEdit($alli, $result)
    {
        $this->conn->insert('forum_edit', ['alliance' => $alli, 'result' => $result]);
    }

    public function updateResultEdit($alli, $result)
    {
        $this->conn->upgrade('forum_edit', ['result' => $result], 'alliance = :alliance', ['alliance' => $alli]);
    }

    public function UpdateEditTopic($id, $title, $cat)
    {
        $this->conn->upgrade('forum_topic', ['title' => $title, 'cat' => $cat], 'id = :id', ['id' => $id]);
    }

    public function UpdateEditForum($id, $name, $des)
    {
        $this->conn->upgrade('forum_cat', ['forum_name' => $name, 'forum_des' => $des], 'id = :id', ['id' => $id]);
    }

    public function StickTopic($id, $mode)
    {
        $this->conn->upgrade('forum_topic', ['stick' => $mode], 'id = :id', ['id' => $id]);
    }

    public function forumCatTopic($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where('cat = :cat AND stick = (NULL)', ['cat' => $id])
            ->orderByDesc('post_date')
            ->get();
    }

    public function forumCatTopicStick($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where('cat = :cat AND stick = 1', ['cat' => $id])
            ->orderByDesc('post_date')
            ->get();
    }

    public function showTopic($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where('id = :id', ['id' => $id])
            ->get();
    }

    public function showPost($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_post')
            ->where('topic = :topic', ['topic' => $id])
            ->get();
    }

    public function showPostEdit($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_post')
            ->where('id = :id', ['id' => $id])
            ->get();
    }

    public function createForum($owner, $alli, $name, $des, $area)
    {
        $data = [
            'owner' => $owner,
            'alliance' => $alli,
            'forum_name' => $name,
            'forum_des' => $des,
            'forum_area' => $area
        ];
        return $this->conn->insert('forum_cat', $data) ? $this->conn->getLastInsertId() : null;
    }

    public function createTopic($title, $post, $cat, $owner, $alli, $ends)
    {
        $date = time();
        $data = [
            'title' => $title,
            'post' => $post,
            'date' => $date,
            'post_date' => $date,
            'cat' => $cat,
            'owner' => $owner,
            'alliance' => $alli,
            'ends' => $ends,
            'close' => '',
            'sticky' => ''
        ];
        return $this->conn->insert('forum_topic', $data) ? $this->conn->getLastInsertId() : null;
    }

    public function createPost($post, $topic, $owner)
    {
        $data = [
            'post' => $post,
            'topic' => $topic,
            'owner' => $owner,
            'date' => time()
        ];
        return $this->conn->insert('forum_post', $data) ? $this->conn->getLastInsertId() : null;
    }

    public function updatePostDate($id)
    {
        $this->conn->upgrade('forum_topic', ['post_date' => time()], 'id = :id', ['id' => $id]);
    }

    public function editUpdateTopic($id, $post)
    {
        $this->conn->upgrade('forum_topic', ['post' => $post], 'id = :id', ['id' => $id]);
    }

    public function editUpdatePost($id, $post)
    {
        $this->conn->upgrade('forum_post', ['post' => $post], 'id = :id', ['id' => $id]);
    }

    public function lockTopic($id, $mode)
    {
        $this->conn->upgrade('forum_topic', ['close' => $mode], 'id = :id', ['id' => $id]);
    }

    public function deleteCat($id)
    {
        $this->conn->delete('forum_cat', 'id = :id', ['id' => $id]);
        $this->conn->delete('forum_topic', 'cat = :id', ['id' => $id]);
    }

    public function deleteTopic($id)
    {
        $this->conn->delete('forum_topic', 'id = :id', ['id' => $id]);
        $this->conn->delete('forum_post', 'topic = :id', ['id' => $id]);
    }

    public function deletePost($id)
    {
        $this->conn->delete('forum_post', 'id = :id', ['id' => $id]);
    }

    public function getAllianceName($id)
    {
        $result = $this->conn
            ->select('tag')
            ->from('alidata')
            ->where('id = :id', ['id' => $id])
            ->first();

        return $result['tag'];
    }

    public function getAlliancePermission($ref, $field, $mode)
    {
        $condicion = !$mode ? 'uid = :ref' : 'username = :ref';
        $result = $this->conn->select($field)->from('ali_permission')->where($condicion, ['ref' => $ref])->first();
        return $result[$field];
    }

    public function changePos($id, $mode)
    {
        $forum = $this->conn->select('forum_area')->from('forum_cat')->where('id = :id', ['id' => $id])->first();
        if ($mode == '-1') {
            $result1 = $this->conn->select('`id`')->from('forum_cat')->where('forum_area = :area AND id < :id', ['area' => $forum['forum_area'], 'id' => $id])->orderByDesc('id')->first();
            if ($result1) {
                $this->conn->upgrade('forum_cat', ['id' => 0], 'id = :id', ['id' => $result1['id']]);
                $this->conn->upgrade('forum_cat', ['id' => -1], 'id = :id', ['id' => $id]);
                $this->conn->upgrade('forum_cat', ['id' => $id], 'id = 0');
                $this->conn->upgrade('forum_cat', ['id' => $result1['id']], 'id = -1');
            }
        } elseif ($mode == 1) {
            $result2 = $this->conn->select('*')->from('forum_cat')->where('id > :id AND forum_area = :area', ['id' => $id, 'area' => $forum['forum_area']])->limit(0, 1)->first();
            if ($result2) {
                $this->conn->upgrade('forum_cat', ['id' => 0], 'id = :id', ['id' => $result2['id']]);
                $this->conn->upgrade('forum_cat', ['id' => -1], 'id = :id', ['id' => $id]);
                $this->conn->upgrade('forum_cat', ['id' => $id], 'id = 0');
                $this->conn->upgrade('forum_cat', ['id' => $result2['id']], 'id = -1');
            }
        }
    }

    public function forumCatAlliance($id)
    {
        $result = $this->conn
            ->select('alliance')
            ->from('forum_cat')
            ->where('id = :id', ['id' => $id])
            ->first();
        return $result['alliance'] ?? null;
    }

    public function creatPoll($id, $name, $p1_name, $p2_name, $p3_name, $p4_name)
    {
        $data = [
            'id' => $id,
            'name' => $name,
            'p1' => 0,
            'p2' => 0,
            'p3' => 0,
            'p4' => 0,
            'p1_name' => $p1_name,
            'p2_name' => $p2_name,
            'p3_name' => $p3_name,
            'p4_name' => $p4_name,
            'voters' => '(NULL)',
        ];
        return $this->conn->insert('forum_poll', $data) ? $this->conn->lastInsertId() : null;
    }

    public function creatForumRules($aid, $id, $users_id, $users_name, $alli_id, $alli_name)
    {
        $data = [
            'id' => $aid,
            'forum_id' => $id,
            'players_id' => $users_id,
            'players_name' => $users_name,
            'ally_id' => $alli_id,
            'ally_tag' => $alli_name
        ];
        return $this->conn->insert('fpost_rules', $data) ? $this->conn->lastInsertId() : null;
    }

    public function setAlliName($aid, $name, $tag)
    {
        $this->conn->upgrade('alidata', ['name' => $name, 'tag' => $tag], 'id = :id', ['id' => $aid]);
    }

    public function isAllianceOwner($id)
    {
        $result = $this->conn->select('id')
            ->from('alidata')
            ->where('leader = :leader', ['leader' => $id])
            ->first();
        return $result ? true : false;
    }

    public function aExist($ref, $type)
    {
        $result = $this->conn->select($type)->from('alidata')->where("$type = :ref", ['ref' => $ref])->first();
        return $result ? true : false;
    }

    public function createAlliance($tag, $name, $userID, $max)
    {
        $data = [
            'name' => $name,
            'tag' => $tag,
            'leader' => $userID,
            'coor' => 0,
            'advisor' => 0,
            'recruiter' => 0,
            'notice' => '(NULL)',
            'desc' => '(NULL)',
            'max' => $max
        ];
        $this->conn->insert('alidata', $data);
    }

    /**
     * insert an alliance new
     */
    public function insertAlliNotice($aid, $notice)
    {
        $data = [
            'aid' => $aid,
            'notice' => $notice,
            'date' => time()
        ];
        $this->conn->insert('ali_log', $data);
    }

    /**
     * delete alliance if empty
     */
    public function deleteAlliance($aid)
    {
        $result = $this->conn->select('id')
            ->from('users')
            ->where('alliance = :aid', ['aid' => $aid])
            ->first();
        if (count($result) == 0) {
            $this->conn->delete('alidata', 'id = :id', ['id' => $aid]);
        }
    }

    /**
     * read all alliance news
     */
    public function readAlliNotice($aid)
    {
        return $this->conn
            ->select('*')
            ->from('ali_log')
            ->where('aid = :aid', ['aid' => $aid])
            ->orderByDesc('date')
            ->get();
    }

    /**
     * create alliance permissions
     * References: ID, notice, description
     */
    public function createAlliPermissions($userID, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8)
    {
        $data = [
            'uid' => $userID,
            'aid' => $aid,
            'rank' => $rank,
            'opt1' => $opt1,
            'opt2' => $opt2,
            'opt3' => $opt3,
            'opt4' => $opt4,
            'opt5' => $opt5,
            'opt6' => $opt6,
            'opt7' => $opt7,
            'opt8' => $opt8
        ];
        $this->conn->insert('ali_permission', $data);
    }

    /**
     * update alliance permissions
     */
    public function deleteAlliPermissions($userID)
    {
        $this->conn->delete('ali_permission', 'uid = :uid', ['uid' => $userID]);
    }

    /**
     * update alliance permissions
     */
    public function updateAlliPermissions($userID, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8 = 0)
    {
        $data = [
            'rank' => $rank, 'opt1' => $opt1, 'opt2' => $opt2, 'opt3' => $opt3, 'opt4' => $opt4,
            'opt5' => $opt5, 'opt6' => $opt6, 'opt7' => $opt7, 'opt8' => $opt8
        ];
        $this->conn->upgrade('ali_permission', $data, 'uid = :uid AND alliance = :alliance', ['uid' => $userID, 'alliance' => $aid]);
    }

    /**
     * read alliance permissions
     * References: ID, notice, description
     */
    public function getAlliPermissions($userID, $aid)
    {
        return $this->conn->select('*')
            ->from('ali_permission')
            ->where('uid = :uid AND alliance = :aid', ['uid' => $userID, 'aid' => $aid])
            ->first();
    }

    /**
     * update an alliance description and notice
     * References: ID, notice, description
     */
    public function submitAlliProfile($aid, $notice, $desc)
    {
        $this->conn->upgrade('alidata', ['notice' => $notice, 'desc' => $desc], 'id = :id', ['id' => $aid]);
    }

    public function diplomacyInviteAdd($alli1, $alli2, $type)
    {
        $data = [
            'alli1' => $alli1,
            'alli2' => $alli2,
            'type' => $type,
            'accepted' => 0
        ];
        $this->conn->insert('diplomacy', $data);
    }

    public function diplomacyOwnOffers($alliance)
    {
        return $this->conn->select('*')
            ->from('diplomacy')
            ->where('alli1 = :session_alliance AND accepted = 0', ['session_alliance' => $alliance])
            ->get();
    }

    public function getAllianceID($name)
    {
        $result = $this->conn->select('id')
            ->from('alidata')
            ->where('tag = :tag', ['tag' => $this->RemoveXSS($name)])
            ->first();
        return $result['id'];
    }

    public function RemoveXSS($val)
    {
        return htmlspecialchars($val, ENT_QUOTES);
    }

    public function diplomacyCancelOffer($id)
    {
        $this->conn->delete('diplomacy', 'id = :id', ['id' => $id]);
    }

    public function diplomacyInviteAccept($id, $alliance)
    {
        $this->conn->upgrade('diplomacy', ['accepted' => 1], 'id = :id AND alli2 = :alli2', ['id' => $id, 'alli2' => $alliance]);
    }

    public function diplomacyInviteDenied($id, $alliance)
    {
        $this->conn->delete('diplomacy', 'id = :id AND alli2 = :alliance', ['id' => $id, 'alliance' => $alliance]);
    }

    public function diplomacyInviteCheck($alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli2 = :alliance AND accepted = 0', ['alliance' => $alliance])
            ->get();
    }

    public function diplomacyExistingRelationships($alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli2 = :alliance AND accepted = 1', ['alliance' => $alliance])
            ->get();
    }

    public function diplomacyExistingRelationships2($alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli1 = :alliance AND accepted = 1', ['alliance' => $alliance])
            ->get();
    }

    public function diplomacyCancelExistingRelationship($id, $alliance)
    {
        $this->conn->delete('diplomacy', 'id = :id AND alli2 = :alliance', ['id' => $id, 'alliance' => $alliance]);
    }

    public function getUserAlliance($id)
    {
        $result = $this->conn->select('alidata.tag')
            ->from('users JOIN alidata')
            ->where('users.alliance = alidata.id AND users.id = :id', ['id' => $id])
            ->first();
        return $result['tag'] == '' ? '-' : $result['tag'];
    }

    public function modifyResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        $fields = [
            'wood' => ($mode ? '+' : '-') . $wood,
            'clay' => ($mode ? '+' : '-') . $clay,
            'iron' => ($mode ? '+' : '-') . $iron,
            'crop' => ($mode ? '+' : '-') . $crop
        ];
        $this->conn->upgrade('vdata', $fields, 'wref = :wref', ['wref' => $vid]);
    }

    public function modifyProduction($vid, $woodp, $clayp, $ironp, $cropp, $upkeep)
    {
        $data = [
            'woodp' => $woodp,
            'clayp' => $clayp,
            'ironp' => $ironp,
            'cropp' => $cropp,
            'upkeep' => $upkeep
        ];
        $this->conn->upgrade('vdata', $data, 'wref = :wref', ['wref' => $vid]);
    }

    public function modifyOasisResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        $fields = [
            'wood' => ($mode ? '+' : '-') . $wood,
            'clay' => ($mode ? '+' : '-') . $clay,
            'iron' => ($mode ? '+' : '-') . $iron,
            'crop' => ($mode ? '+' : '-') . $crop
        ];
        $this->conn->upgrade('odata', $fields, 'wref = :wref', ['wref' => $vid]);
    }

    public function getFieldType($vid, $field)
    {
        return $this->conn->select("f{$field}t")->from('fdata')->where('vref = :vref', ['vref' => $vid])->first();
    }

    public function getVSumField($userID, $field)
    {
        return $this->conn->select("SUM($field)")->from('vdata')->where('owner = :owner', ['owner' => $userID])->first();
    }

    public function updateVillage($vid)
    {
        $this->conn->upgrade('vdata', ['lastupdate' => time()], 'wref = :wref', ['wref' => $vid]);
    }

    public function updateOasis($vid)
    {
        $this->conn->upgrade('odata', ['lastupdated' => time()], 'wref = :wref', ['wref' => $vid]);
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function setVillageName($vid, $name)
    {
        if ($name == '') {
            return false;
        }
        $this->conn->upgrade('vdata', ['name' => $name], 'wref = :wref', ['wref' => $vid]);
    }

    public function modifyPop($vid, $pop, $mode)
    {
        $this->conn->upgrade('vdata', ['pop' => 'pop ' . ($mode ? '-' : '+') . $pop], 'wref = :wref', ['wref' => $vid]);
    }

    public function addCP($ref, $cp)
    {
        $this->conn->upgrade('vdata', ['cp' => "cp + {$cp}"], 'wref = :wref', ['wref' => $ref]);
    }

    public function addCel($ref, $cel, $type)
    {
        $this->conn->upgrade('vdata', ['celebration' => $cel, 'type' => $type], 'wref = :wref', ['wref' => $ref]);
    }

    public function getCel()
    {
        return $this->conn
            ->select('`wref`,`type`,`owner`')
            ->from('vdata')
            ->where('celebration < :time AND celebration != 0', ['time' => time()])
            ->get();
    }

    public function getActiveGCel($vref)
    {
        return $this->conn->select('*')
            ->from('vdata')
            ->where('vref = :vref AND celebration > :time AND type = 2', ['vref' => $vref, 'time' => time()])
            ->get();
    }

    public function clearCel($ref)
    {
        $this->conn->upgrade('vdata', ['celebration' => 0, 'type' => 0], 'wref = :wref', ['wref' => $ref]);
    }

    public function setCelCp($userID, $cp)
    {
        $this->conn->upgrade('users', ['cp' => "cp + $cp"], 'id = :id', ['id' => $userID]);
    }

    public function getInvitation($userID, $ally)
    {
        return $this->conn->select('*')
            ->from('ali_invite')
            ->where('uid = :uid AND alliance = :alliance', ['uid' => $userID, 'alliance' => $ally])
            ->get();
    }

    public function getInvitation2($userID)
    {
        return $this->conn->select('*')
            ->from('ali_invite')
            ->where('uid = :uid ', ['uid' => $userID])
            ->get();
    }

    public function getAliInvitations($aid)
    {
        return $this->conn->select('*')
            ->from('ali_invite')
            ->where('alliance = :alliance AND accept = 0', ['alliance' => $aid])
            ->get();
    }

    public function sendInvitation($userID, $alli, $sender)
    {
        $data = ['uid' => $userID, 'alliance' => $alli, 'sender' => $sender, 'timestamp' => time(), 'accept' => 0];
        $this->conn->insert('ali_invite', $data);
    }

    public function removeInvitation($id)
    {
        $this->conn->delete('ali_invite', 'id = :id', ['id' => $id]);
    }

    public function delMessage($id)
    {
        $this->conn->delete('mdata', 'id = :id', ['id' => $id]);
    }

    public function delNotice($id, $userID)
    {
        $this->conn->delete('ndata', 'id = :id AND uid = :uid', ['id' => $id, 'uid' => $userID]);
    }

    public function sendMessage($client, $owner, $topic, $message, $send, $alliance, $player, $coor, $report)
    {
        $data = [
            'client' => $client, 'owner' => $owner, 'topic' => $topic, 'message' => $message, 'send' => $send,
            'alliance' => $alliance, 'player' => $player, 'coor' => $coor, 'report' => $report, 'time' => time()
        ];
        $this->conn->insert('mdata', $data);
    }

    public function setArchived($id)
    {
        $this->conn->upgrade('mdata', ['archived' => 1], 'id = :id', ['id' => $id]);
    }

    public function setNorm($id)
    {
        $this->conn->upgrade('mdata', ['archived' => 0], 'id = :id', ['id' => $id]);
    }

    public function getMessage($id, $mode)
    {
        $params = [];
        $where = $order = $limit = '';
        switch ($mode) {
            case 1:
                $where = 'target = :target AND send = 0 AND archived = 0';
                $params['target'] = $id;
                $order = 'ORDER BY time DESC';
                break;
            case 2:
                $where = 'owner = :owner';
                $params['owner'] = $id;
                $order = 'ORDER BY time DESC';
                break;
            case 3:
                $where = 'id = :id';
                $params['id'] = $id;
                break;
            case 4:
                $this->conn->upgrade('mdata', ['viewed' => 1], 'id = :id AND target = :target', ['id' => $id, 'target' => Session::get('uid')]);
                break;
            case 5:
                $this->conn->upgrade('mdata', ['deltarget' => 1, 'viewed' => 1], 'id = :id', ['id' => $id]);
                break;
            case 6:
                $where = 'target = :target AND send = 0 AND archived = 1';
                $params['target'] = $id;
                break;
            case 7:
                $this->conn->upgrade('mdata', ['delowner' => 1], 'id = :id', ['id' => $id]);
                break;
            case 8:
                $this->conn->upgrade('mdata', ['deltarget' => 1, 'delowner' => 1, 'viewed' => 1], 'id = :id', ['id' => $id]);
                break;
            case 9:
                $where = 'target = :target AND send = 0 AND archived = 0 AND deltarget = 0 AND viewed = 0';
                $params['target'] = $id;
                $order = 'ORDER BY time DESC';
                break;
            case 10:
                $where = 'owner = :owner AND delowner = 0';
                $params['owner'] = $id;
                $order = 'ORDER BY time DESC';
                break;
            case 11:
                $where = 'target = :target AND send = 0 AND archived = 1 AND deltarget = 0';
                $params['target'] = $id;
                break;
            case 12:
                $where = 'target = :target AND send = 0 AND archived = 0 AND deltarget = 0 AND viewed = 0';
                $params['target'] = $id;
                $order = 'ORDER BY time DESC';
                $limit = 1;
                break;
        }

        if ($mode <= 3 || $mode == 6 || $mode > 8) {
            return $this->conn->select('*')->from('mdata')->where($where, $params)->order($order)->limit($limit)->get();
        } else {
            return false;
        }
    }

    public function unarchiveNotice($id)
    {
        $this->conn->upgrade('ndata', ['archive' => 0], 'id = :id', ['id' => $id]);
    }

    public function archiveNotice($id)
    {
        $this->conn->upgrade('ndata', ['archive' => 1], 'id = :id', ['id' => $id]);
    }

    public function removeNotice($id)
    {
        $this->conn->delete('ndata', 'id = :id', ['id' => $id]);
    }

    public function noticeViewed($id)
    {
        $this->conn->upgrade('ndata', ['viewed' => 1], 'id = :id', ['id' => $id]);
    }

    public function addNotice($userID, $toWref, $ally, $type, $topic, $data, $time = 0)
    {
        if ($time == 0) {
            $time = time();
        }
        $values = [
            'uid' => $userID,
            'toWref' => $toWref,
            'ally' => $ally,
            'topic' => $topic,
            'ntype' => $type,
            'data' => $data,
            'time' => $time,
            'viewed' => 0
        ];
        $this->conn->insert('ndata', $values);
    }

    public function getNotice($userID)
    {
        return $this->conn->select('*')
            ->from('ndata')
            ->where('uid = :uid AND del = 0', ['uid' => $userID])
            ->orderByDesc('time')
            ->limit(99)
            ->get();
    }

    public function getNoticeReportBox($userID)
    {
        $result = $this->conn->select('COUNT(`id`) as maxreport')
            ->from('ndata')
            ->where('uid = :uid', ['uid' => $userID])
            ->orderByDesc('time')
            ->limit(200)
            ->first();
        return $result['maxreport'];
    }

    public function addBuilding($worlID, $field, $type, $loop, $time, $master, $level)
    {
        $this->conn->upgrade('fdata', ["f{$field}t" => $type], 'vref = :vref', ['vref' => $worlID]);
        $this->conn->insert('bdata', ['wid' => $worlID, 'field' => $field, 'type' => $type, 'loop' => $loop, 'time' => $time, 'master' => $master, 'level' => $level]);
    }

    public function removeBuilding($d)
    {
        $building = new Building();

        $jobLoopconID = -1;
        $SameBuildCount = 0;
        $jobs = $building->buildArray;
        for ($i = 0; $i < sizeof($jobs); $i++) {
            if ($jobs[$i]['id'] == $d) {
                $jobDeleted = $i;
            }
            if ($jobs[$i]['loopcon'] == 1) {
                $jobLoopconID = $i;
            }
            if ($jobs[$i]['master'] == 1) {
                $jobMaster = $i;
            }
        }
        if (count($jobs) > 1 && ($jobs[0]['field'] == $jobs[1]['field'])) {
            $SameBuildCount = 1;
        }
        if (count($jobs) > 2 && ($jobs[0]['field'] == $jobs[2]['field'])) {
            $SameBuildCount = 2;
        }
        if (count($jobs) > 2 && ($jobs[1]['field'] == $jobs[2]['field'])) {
            $SameBuildCount = 3;
        }
        if (count($jobs) > 2 && ($jobs[0]['field'] == ($jobs[1]['field'] == $jobs[2]['field']))) {
            $SameBuildCount = 4;
        }
        if (count($jobs) > 3 && ($jobs[0]['field'] == ($jobs[1]['field'] == $jobs[3]['field']))) {
            $SameBuildCount = 5;
        }
        if (count($jobs) > 3 && ($jobs[0]['field'] == ($jobs[2]['field'] == $jobs[3]['field']))) {
            $SameBuildCount = 6;
        }
        if (count($jobs) > 3 && ($jobs[1]['field'] == ($jobs[2]['field'] == $jobs[3]['field']))) {
            $SameBuildCount = 7;
        }
        if (count($jobs) > 3 && ($jobs[0]['field'] == $jobs[3]['field'])) {
            $SameBuildCount = 8;
        }
        if (count($jobs) > 3 && ($jobs[1]['field'] == $jobs[3]['field'])) {
            $SameBuildCount = 9;
        }
        if (count($jobs) > 3 && ($jobs[2]['field'] == $jobs[3]['field'])) {
            $SameBuildCount = 10;
        }
        if ($SameBuildCount > 0) {
            if ($SameBuildCount > 3) {
                if ($SameBuildCount == 4 or $SameBuildCount == 5) {
                    if ($jobDeleted == 0) {
                        $uprequire = $building->resourceRequired($jobs[1]['field'], $jobs[1]['type'], 1);
                        $this->conn->upgrade('bdata', ['loopcon' => 0, 'level' => 'level - 1', 'timestamp' => $uprequire['time'] + time()], 'id = :id', ['id' => $jobs[1]['id']]);
                    }
                } else if ($SameBuildCount == 6) {
                    if ($jobDeleted == 0) {
                        $uprequire = $building->resourceRequired($jobs[2]['field'], $jobs[2]['type'], 1);
                        $this->conn->upgrade('bdata', ['loopcon' => 0, 'level' => 'level - 1', 'timestamp' => $uprequire['time'] + time()], 'id = :id', ['id' => $jobs[2]['id']]);
                    }
                } else if ($SameBuildCount == 7) {
                    if ($jobDeleted == 1) {
                        $uprequire = $building->resourceRequired($jobs[2]['field'], $jobs[2]['type'], 1);
                        $this->conn->upgrade('bdata', ['loopcon' => 0, 'level' => 'level - 1', 'timestamp' => $uprequire['time'] + time()], 'id = :id', ['id' => $jobs[2]['id']]);
                    }
                }
                if ($SameBuildCount < 8) {
                    $uprequire1 = $building->resourceRequired($jobs[$jobMaster]['field'], $jobs[$jobMaster]['type'], 2);
                    $this->conn->upgrade('bdata', ['level' => 'level - 1', 'timestamp' => $uprequire1['time']], 'id = :id', ['id' => $jobs[$jobMaster]['id']]);
                } else {
                    $uprequire1 = $building->resourceRequired($jobs[$jobMaster]['field'], $jobs[$jobMaster]['type'], 1);
                    $this->conn->upgrade('bdata', ['level' => 'level - 1', 'timestamp' => $uprequire1['time']], 'id = :id', ['id' => $jobs[$jobMaster]['id']]);
                }
            } elseif ($d == $jobs[floor($SameBuildCount / 3)]['id'] || $d == $jobs[floor($SameBuildCount / 2) + 1]['id']) {
                $timestamp = $jobs[floor($SameBuildCount / 3)]['timestamp'];
                $data = ['loopcon' => 0, 'level' => 'level - 1', 'timestamp' => $timestamp];
                $v1 = $jobs[floor($SameBuildCount / 3)]['id'];
                $v2 = $jobs[floor($SameBuildCount / 2) + 1]['id'];
                $this->conn->upgrade('bdata', $data, "master = 0 AND id > $d AND (ID = $v1 OR ID = $v2)");
            }
        } else {
            if ($jobs[$jobDeleted]['field'] >= 19) {
                $fieldlevel = $this->conn->select("f{$jobs[$jobDeleted]['field']}")->from('fdata')->where('vref = :vref', ['vref' => $jobs[$jobDeleted]['wid']])->first();
                if ($fieldlevel[0] == 0) {
                    $this->conn->upgrade('fdata', ["f{$jobs[$jobDeleted]['field']}t" => 0], 'vref = :vref', ['vref' => $jobs[$jobDeleted]['wid']]);
                }
            }
            if (($jobLoopconID >= 0) && ($jobs[$jobDeleted]['loopcon'] != 1)) {
                if (($jobs[$jobLoopconID]['field'] <= 18 && $jobs[$jobDeleted]['field'] <= 18) || ($jobs[$jobLoopconID]['field'] >= 19 && $jobs[$jobDeleted]['field'] >= 19) || sizeof($jobs) < 3) {
                    $uprequire = $building->resourceRequired($jobs[$jobLoopconID]['field'], $jobs[$jobLoopconID]['type']);
                    $this->conn->upgrade('bdata', ['loopcon' => 0, 'timestamp' => time() + $uprequire['time']], 'wid = :wid AND loopcon=1 AND master=0', ['wid' => $jobs[$jobDeleted]['wid']]);
                }
            }
        }
        $this->conn->delete('bdata', 'id = :id', ['id' => $d]);
    }

    public function addDemolition($worlID, $field)
    {
        $building = new Building();
        $village = new Village();

        $this->conn->delete('bdata', 'field = :field AND wid = :wid', ['field' => $field, 'wid' => $worlID]);
        $uprequire = $building->resourceRequired($field - 1, $village->resarray["f{$field}t"]);

        $data = [
            'vref' => $worlID,
            'buildnumber' => $field,
            'lvl' => ($this->getFieldLevel($worlID, $field) - 1),
            'timetofinish' => (time() + floor($uprequire['time'] / 2))
        ];
        $this->conn->insert('demolition', $data);
    }

    public function getFieldLevel($vid, $field)
    {
        return $this->conn->select("f{$field}")->from('fdata')->where('vref = :vref', ['vref' => $vid])->first();
    }

    public function getDemolition($worlID = 0)
    {
        $conditions = ($worlID) ? ['vref' => $worlID] : 'timetofinish <= ' . time();
        return $this->conn->select('`vref`,`buildnumber`,`timetofinish`')
            ->from('demolition')
            ->where($conditions)
            ->get();
    }

    public function finishDemolition($worlID)
    {
        $this->conn->upgrade('demolition', ['timetofinish' => 0], 'vref = :vref', ['vref' => $worlID]);
    }

    public function delDemolition($worlID)
    {
        $this->conn->delete('demolition', 'vref = :vref', ['vref' => $worlID]);
    }

    public function getJobs($worlID)
    {
        return $this->conn->select('*')
            ->from('bdata')
            ->where('wid = :wid', ['wid' => $worlID])
            ->orderByAsc('id')
            ->get();
    }

    public function finishWoodCutter($worlID)
    {
        $bdata = $this->conn->select('id')
            ->from('bdata')
            ->where('wid = :wid AND type = 1', ['wid' => $worlID])
            ->order('ORDER BY master, timestamp ASC')
            ->first();
        $this->conn->upgrade('bdata', ['timestamp' => time() - 1], 'id = :id', ['id' => $bdata['id']]);
    }

    public function finishCropLand($worlID)
    {
        $result1 = $this->conn->select('`id`,`timestamp`')->from('bdata')->where('wid = :wid AND type = 4', ['wid' => $worlID])->order('ORDER BY master, timestamp ASC')->first();
        $this->conn->upgrade('bdata', ['timestamp' => time() - 1], 'id = :id', ['id' => $result1['id']]);

        $result2 = $this->conn->select('`id`')->from('bdata')->where('wid = :wid AND loopcon = 1 AND field <= 18', ['wid' => $worlID])->order('ORDER BY master, timestamp ASC')->first();
        $this->conn->upgrade('bdata', ['timestamp' => $result2['timestamp'] - time()], 'id = :id', ['id' => $result2['id']]);
    }

    public function finishBuildings($worlID)
    {
        $buildings = $this->conn->select('id')
            ->from('bdata')
            ->where('id = :id', ['id' => $worlID])
            ->order('ORDER BY master, timestamp ASC')
            ->get();

        foreach ($buildings as $building) {
            $this->conn->upgrade('bdata', ['timestamp' => time() - 1], 'id = :id', ['id' => $building['id']]);
        }
    }

    public function getMasterJobs($worlID)
    {
        return $this->conn->select('`id`')
            ->from('bdata')
            ->where('wid = :wid AND master = 1', ['wid' => $worlID])
            ->order('ORDER BY master, timestamp ASC')
            ->get();
    }

    public function getBuildingByField($worlID, $field)
    {
        return $this->conn->select('`id`')
            ->from('bdata')
            ->where('wid = :wid AND field = :field AND master = 0', ['wid' => $worlID, 'field' => $field])
            ->get();
    }

    public function getBuildingByType($worlID, $type)
    {
        return $this->conn->select('`id`')
            ->from('bdata')
            ->where('wid = :wid AND type = :type AND master = 0', ['wid' => $worlID, 'type' => $type])
            ->order('ORDER BY master, timestamp ASC')
            ->get();
    }

    public function getDorf1Building($worlID)
    {
        return $this->conn->select('`timestamp`')
            ->from('bdata')
            ->where('wid = :wid AND field < 19 AND master = 0', ['wid' => $worlID])
            ->get();
    }

    public function getDorf2Building($worlID)
    {
        return $this->conn->select('`timestamp`')
            ->from('bdata')
            ->where('wid = :wid AND field > 18 AND master = 0', ['wid' => $worlID])
            ->get();
    }

    public function updateBuildingWithMaster($id, $time, $loop)
    {
        $data = [
            'master' => 0,
            'timestamp' => $time,
            'loopcon' => $loop
        ];
        $this->conn->upgrade('bdata', $data, 'id = :id', ['id' => $id]);
    }

    public function getVillageByName($name)
    {
        $result = $this->conn->select('wref')
            ->from('vdata')
            ->where('name = :name', ['name' => $name])
            ->limit(1)
            ->first();
        return $result['wref'];
    }

    /**
     * set accept flag on market
     * References: id
     */
    public function setMarketAcc($id)
    {
        $this->conn->upgrade('market', ['accept' => 1], 'id = :id', ['id' => $id]);
    }

    /**
     * send resource to other village
     * Mode 0: Send
     * Mode 1: Cancel
     * References: Wood/ID, Clay, Iron, Crop, Mode
     */
    public function sendResource($wood, $clay, $iron, $crop, $merchant)
    {
        $data = [
            'wood' => $wood,
            'clay' => $clay,
            'iron' => $iron,
            'crop' => $crop,
            'merchant' => $merchant
        ];
        $this->conn->insert('send', $data);
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
        $this->conn->insert('send', $data);
    }

    public function removeSend($id)
    {
        $this->conn->delete('send', 'id = :id', ['id' => $id]);
    }

    /**
     * get resources back if you delete offer
     * References: VillageRef (vref)
     * @noinspection PhpInconsistentReturnPointsInspection
     */
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
        $this->conn->upgrade('vdata', $data, 'wref = :wref', ['wref' => $vref]);
    }

    /**
     * get info about offered resources
     * References: VillageRef (vref)
     */
    public function getMarketField($vref, $field)
    {
        $result = $this->conn->select($field)->from('market')->where('vref = :vref', ['vref' => $vref])->first();
        return $result[$field];
    }

    public function removeAcceptedOffer($id)
    {
        $this->conn->delete('market', 'id = :id', ['id' => $id]);
    }

    /**
     * add market offer
     * Mode 0: Add
     * Mode 1: Cancel
     * References: Village, Give, Amt, Want, Amt, Time, Alliance, Mode
     */
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
            $this->conn->insert('market', $data);
        } else {
            $this->conn->delete('market', 'id = :id AND vref = :vref', ['id' => $gtype, 'vref' => $vid]);
        }
    }

    /**
     * get market offer
     * References: Village, Mode
     */
    public function getMarket($vid, $mode)
    {
        $alliance = $this->getUserField($this->getVillageField($vid, 'owner'), 'alliance', 0);
        if (!$mode) {
            $result = $this->conn->select('*')->from('market')->where('vref = :vref AND accept = 0', ['vref' => $vid])->orderByDesc('id')->get();
        } else {
            $result = $this->conn->select('*')->from('market')->where('vref != :vref AND alliance = :alliance OR vref != :vref AND alliance = 0 AND accept = 0', ['vref' => $vid, 'alliance' => $alliance])->orderByDesc('id')->get();
        }
        return $result;
    }

    public function getUserField($userID, $field, $mode)
    {
        $column = !$mode ? 'id' : 'username';
        $result = $this->conn->select($field)->from('users')->where("$column = :ref", ['ref' => $userID])->first();
        return $result[$field];
    }

    public function getVillageField($ref, $field)
    {
        $result = $this->conn->select($field)->from('vdata')->where('wref = :wref', ['wref' => $ref])->limit(1)->first();
        return $result[$field];
    }

    /**
     * get market offer
     * References: ID
     */
    public function getMarketInfo($id)
    {
        return $this->conn->select('`vref`,`gtype`,`wtype`,`merchant`,`wamt`')
            ->from('market')
            ->where('id = :id', ['id' => $id])
            ->first();
    }

    public function setMovementProc($moveid)
    {
        $this->conn->upgrade('movement', ['proc' => 1], 'moveid = :moveid', ['moveid' => $moveid]);
    }

    /**
     * retrieve used merchant
     * References: Village
     */
    public function totalMerchantUsed($vid)
    {
        $result1 = $this->conn->select('sum(send.merchant)')->from('send, movement')->where('movement.from = :from AND send.id = movement.ref AND movement.proc = 0 AND sort_type = 0', ['from' => $vid])->first();
        $result2 = $this->conn->select('sum(send.merchant)')->from('send, movement')->where('movement.to = :to AND send.id = movement.ref AND movement.proc = 0 AND sort_type = 1', ['to' => $vid])->first();
        $result3 = $this->conn->select('sum(merchant)')->from('market')->where('vref = :vref AND accept = 0', ['vref' => $vid])->first();
        return $result1[0] + $result2[0] + $result3[0];
    }

    public function getMovementById($id)
    {
        return $this->conn->select('`starttime`,`to`,`from`')->from('movement')->where('moveid = :moveid', ['moveid' => $id])->get();
    }

    public function cancelMovement($id, $newfrom, $newto)
    {
        $ref = '';
        $amove = $this->conn->select('red')->from('movement')->where('moveid = :moveid', ['moveid' => $id])->first();
        if (!empty($amove)) {
            $mov = $amove[0];
            if ($mov['ref'] == 0) {
                $ref = $this->addAttack($newto, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, 0, 0, 0);
            }
            $this->conn->upgrade('movement', ['sort_type' => 4, 'from' => $newfrom, 'to' => $newto, 'ref' => $ref, 'starttime' => time(), 'endtime' => ((2 * time()) . ' - starttime')], 'moveid = :moveid', ['moveid' => $id]);
        }
    }

    public function addAttack($vid, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type, $ctar1, $ctar2, $spy)
    {
        $data = [
            'vref' => $vid,
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
            'attack_type' => $type,
            'ctar1' => $ctar1,
            'ctar2' => $ctar2,
            'spy' => $spy
        ];
        return $this->conn->insert('attacks', $data) ? $this->conn->lastInsertId() : false;
    }

    public function getAdvMovement($village)
    {
        return $this->conn->select('`moveid`')
            ->from('movement')
            ->where('movement.from = :from AND sort_type = 9', ['from' => $village])
            ->get();
    }

    public function getCompletedAdvMovement($village)
    {
        return $this->conn->select('`moveid`')
            ->from('movement')
            ->where('movement.from = :from AND sort_type = 9 AND proc = 1', ['from' => $village])
            ->get();
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
        $this->conn->insert('a2b', $data);
    }

    public function getA2b($ckey, $check)
    {
        return $this->conn->select('*')
            ->from('a2b')
            ->where('ckey = :ckey AND time_check = :time', ['ckey' => $ckey, 'time' => $check])
            ->first();
    }

    public function removeA2b($ckey, $check)
    {
        $this->conn->delete('a2b', 'ckey = :ckey AND time_check = :check', ['ckey' => $ckey, 'check' => $check]);
    }

    public function addMovement($type, $from, $to, $ref, $data, $endtime)
    {
        $data = [
            'sort_type' => $type,
            'from' => $from,
            'to' => $to,
            'ref' => $ref,
            'data' => $data,
            'starttime' => time(),
            'endtime' => $endtime,
            'proc' => 0
        ];
        $this->conn->insert('movement', $data);
    }

    public function modifyAttack($aid, $unit, $amount)
    {
        $unit = "t{$unit}";
        $result = $this->conn->select($unit)->from('attacks')->where('id = :id', ['id' => $aid])->get();

        $amount = min($result[$unit], $amount);
        $this->conn->upgrade('attacks', [$unit => "$unit - $amount"], 'id = :id', ['id' => $aid]);
    }

    public function getRanking()
    {
        return $this->conn->select('id, username, alliance, ap, apall, dp, dpall, access')->from('users')->where('tribe <= 3 AND access < 8')->get();
    }

    public function getBuildList($type, $worlID = 0)
    {
        $where = 'true';
        $params = [];

        if ($type) {
            $where .= ' AND type = :type';
            $params['type'] = $type;
        }
        if ($worlID) {
            $where .= ' AND wid = :wid';
            $params['wid'] = $worlID;
        }

        return $this->conn->select('`id`')
            ->from('bdata')
            ->where($where, $params)
            ->get();
    }

    public function getVRanking()
    {
        return $this->conn->select('v.wref, v.name, v.owner, v.pop')
            ->from('vdata AS v, users AS u')
            ->where('v.owner = u.id AND u.tribe <= 3 AND v.wref != NULL AND u.access <= 8')
            ->get();
    }

    public function getARanking()
    {
        return $this->conn->select('id, name, tag')
            ->from('alidata')
            ->where('id != NULL')
            ->get();
    }

    public function getHeroRanking($limit = '')
    {
        return $this->conn->select('`uid`,`level`,`experience`')
            ->from('hero')
            ->orderByDesc('experience')
            ->limit($limit)
            ->get();
    }

    public function getAllMember($aid)
    {
        return $this->conn->select('`id`,`username`,`timestamp`')
            ->from('users')
            ->where('alliance = :alliance', ['alliance' => $aid])
            ->orderBy('(SELECT SUM(pop) FROM vdata WHERE owner = users.id)', 'DESC')
            ->get();
    }

    public function getUnit($vid)
    {
        return $this->conn->select('*')
            ->from('units')
            ->where('vref = :vref', ['vref' => $vid])
            ->first();
    }

    public function getHUnit($vid)
    {
        $result = $this->conn->select('hero')
            ->from('units')
            ->where('vref = :vref', ['vref' => $vid])
            ->first();
        return $result['hero'] != 0;
    }

    public function getHero($userID = false, $id = false, $dead = 2)
    {

        $where = 'true';
        $params = [];
        if ($userID) {
            $where .= ' AND uid = :uid';
            $params['uid'] = $userID;
        }
        if ($id) {
            $where .= ' AND id = :id';
            $params['id'] = $id;
        }
        if ($dead != 2) {
            $where .= ' AND dead = :dead';
            $params['dead'] = $dead;
        }
        return $this->conn->select('*')->from('hero')->where($where, $params)->limit(1)->first();
    }

    public function modifyHero($userID, $id, $column, $value, $mode = 0)
    {
        $data = match ($mode) {
            0 => [$column => ":value"],             // mode=0 set
            1 => [$column => "$column + :value"],   // mode=1 add
            2 => [$column => "$column - :value"],   // mode=2 sub
            3 => [$column => "$column * :value"],   // mode=3 mul
            4 => [$column => "$column / :value"],   // mode=4 div
        };
        if (in_array($column, ['r0', 'r1', 'r2', 'r3', 'r4'])) {
            $data['rc'] = 1;
        }

        $where = 'true';
        $params = ['value' => $value];

        if ($userID) {
            $where .= ' AND uid = :uid';
            $params['uid'] = $userID;
        }
        if ($id) {
            $where .= ' AND heroid = :id';
            $params['id'] = $id;
        }

        $this->conn->upgrade('hero', $data, $where, $params);
    }

    public function clearTech($vref)
    {
        $this->conn->delete('tdata', 'vref = :vref', ['vref' => $vref]);
        $this->addTech($vref);
    }

    public function addTech($vid)
    {
        $this->conn->insert('tdata', ['vref' => $vid]);
    }

    public function clearABTech($vref)
    {
        $this->conn->delete('abdata', 'vref = :vref', ['vref' => $vref]);
        $this->addABTech($vref);
    }

    public function addABTech($vid)
    {
        $this->conn->insert('abdata', ['vref' => $vid]);
    }

    public function getABTech($vid)
    {
        return $this->conn->select('*')->from('abdata')->where('vref = :vref', ['vref' => $vid])->first();
    }

    public function addResearch($vid, $tech, $time)
    {
        $data = [
            'vref' => $vid,
            'tech' => $tech,
            'timestamp' => $time
        ];
        $this->conn->insert('research', $data);
    }

    public function getResearching($vid)
    {
        return $this->$this->conn->select('*')->from('research')->where('vref = :vref', ['vref' => $vid])->get();
    }

    public function checkIfResearched($vref, $unit)
    {
        $result = $this->conn->select($unit)->from('tdata')->where('vref = :vref', ['vref' => $vref])->first();
        return $result[$unit];
    }

    public function getTech($vid)
    {
        return $this->conn->select('*')->from('tdata')->where('vref = :vid', ['vid' => $vid])->first();
    }

    public function getTraining($vid)
    {
        return $this->conn->select('`amt`,`unit`,`endat`,`commence`,`id`,`vref`,`pop`,`timestamp`,`eachtime`')
            ->from('training')
            ->where('vref = :vref'. ['vref' => $vid])
            ->orderByDesc('id')
            ->get();
    }

    public function trainUnit($vid, $unit, $amount, $pop, $each, $commence, $mode)
    {
        $technology = new Technology();

        if (!$mode) {
            $barracks = [1, 2, 3, 11, 12, 13, 14, 21, 22, 31, 32, 33, 34, 41, 42, 43, 44];
            $greatbarracks = [61, 62, 63, 71, 72, 73, 84, 81, 82, 91, 92, 93, 94, 101, 102, 103, 104];
            $stables = [4, 5, 6, 15, 16, 23, 24, 25, 26, 35, 36, 45, 46];
            $greatstables = [64, 65, 66, 75, 76, 83, 84, 85, 86, 95, 96, 105, 106];
            $workshops = [7, 8, 17, 18, 27, 28, 37, 38, 47, 48];
            $greatworkshops = [67, 68, 77, 78, 87, 88, 97, 98, 107, 108];
            $residences = [9, 10, 19, 20, 29, 30, 39, 40, 49, 50];
            $trap = [199];

            if (in_array($unit, $barracks)) {
                $queued = $technology->getTrainingList(1);
            } elseif (in_array($unit, $stables)) {
                $queued = $technology->getTrainingList(2);
            } elseif (in_array($unit, $workshops)) {
                $queued = $technology->getTrainingList(3);
            } elseif (in_array($unit, $residences)) {
                $queued = $technology->getTrainingList(4);
            } elseif (in_array($unit, $greatbarracks)) {
                $queued = $technology->getTrainingList(5);
            } elseif (in_array($unit, $greatstables)) {
                $queued = $technology->getTrainingList(6);
            } elseif (in_array($unit, $greatworkshops)) {
                $queued = $technology->getTrainingList(7);
            } elseif (in_array($unit, $trap)) {
                $queued = $technology->getTrainingList(8);
            }
            $timestamp = time();

            if ($queued[count($queued) - 1]['unit'] == $unit) {
                $endat = $each * $amount / 1000;
                $this->conn->upgrade('training', ['amt' => "amt + {$amount}", 'timestamp' => time(), 'endat' => "endat + {$endat}"], 'id = :id', ['id' => $queued[count($queued) - 1]['id']]);
            } else {
                $this->conn->insert('training', ['vref' => $vid, 'unit' => $unit, 'amt' => $amount, 'pop' => $pop, 'timestamp' => $timestamp, 'eachtime' => $each, 'commence' => $commence, 'endat' => time() + ($each * $amount / 1000)]);
            }
        } else {
            $this->conn->delete('training', 'id = :id', ['id' => $vid]);
        }
    }

    public function removeZeroTrain()
    {
        $this->conn->delete('training', 'unit <> 0 AND amt <= 0');
    }

    public function getHeroTrain($vid)
    {
        return $this->conn->select('`id`,`eachtime`')
            ->from('training')
            ->where('vref = :vref AND unit = 0', ['vref' => $vid])
            ->first();
    }

    public function trainHero($vid, $each, $endat, $mode)
    {
        if (!$mode) {
            $this->conn->insert('training', ['vref' => $vid, 'unit' => 0, 'amt' => 1, 'pop' => 6, 'timestamp' => time(), 'eachtime' => $each, 'commence' => time(), 'endat' => $endat]);
        } else {
            $this->conn->delete('training', 'id = :id', ['id' => $vid]);
        }
    }

    public function updateTraining($id, $trained)
    {
        $this->conn->upgrade('training', ['amt' => 'GREATEST(amt - :trained, 0)', 'timestamp' => time()], 'id = :id', ['trained' => $trained, 'id' => $id]);
    }

    public function modifyUnit($vref, $unit, $amount, $mode)
    {
        if ($unit == 230) {
            $unit = 30;
        } elseif ($unit == 231) {
            $unit = 31;
        } elseif ($unit == 120) {
            $unit = 20;
        } elseif ($unit == 121) {
            $unit = 21;
        } elseif ($unit != 'hero') {
            $unit = 'u' . $unit;
        }

        switch ($mode) {
            case 0:
                $result = $this->conn->select($unit)->from('units')->where('vref = :vref', ['vref' => $vref])->first();
                $this->conn->upgrade('units', [$unit => ($unit - min($result[$unit], $amount))], 'vref = :vref', ['vref' => $vref]);
                break;
            case 1:
                $this->conn->upgrade('units', [$unit => ($unit + $amount)], 'vref = :vref', ['vref' => $vref]);
                break;
            case 2:
                $this->conn->upgrade('units', [$unit => $amount], 'vref = :vref', ['vref' => $vref]);
                break;
        }
    }

    public function getFilledTrapCount($vref)
    {
        $result = 0;
        $trapped = $this->conn->select('*')->from('trapped')->where('vref = :vref', ['vref' => $vref])->get();
        if (count($trapped) > 0) {
            foreach ($trapped as $key => $value) {
                for ($i = 1; $i <= 50; $i++) {
                    if ($value["u{$i}"] > 0) {
                        $result += $value["u{$i}"];
                    }
                }
                if ($value['hero'] > 0) {
                    $result += 1;
                }
            }
        }
        return $result;
    }

    public function getTrapped($id)
    {
        return $this->conn->select('*')->from('trapped')->where('id = :id', ['id' => $id])->first();
    }

    public function getTrappedIn($vref)
    {
        return $this->conn->select('*')->from('trapped')->where('vref = :vref', ['vref' => $vref])->first();
    }

    public function getTrappedFrom($from)
    {
        return $this->conn->select('*')->from('trapped')->where('from = :from', ['from' => $from])->first();
    }

    public function addTrapped($vref, $from)
    {
        $id = $this->hasTrapped($vref, $from);
        if (!$id) {
            $this->conn->insert('trapped', ['vref' => $vref, 'from' => $from]);
        }
        return $id;
    }

    public function hasTrapped($vref, $from)
    {
        $result = $this->conn->select('id')->from('trapped')
            ->where('vref = :vref AND from = :from', ['vref' => $vref, 'from' => $from])->first();

        if (isset($result['id'])) {
            return $result['id'];
        } else {
            return false;
        }
    }

    public function modifyTrapped($id, $unit, $amount, $mode)
    {
        if (!$mode) {
            $trapped = $this->conn->select($unit)->from('trapped')->where('id = :id', ['id' => $id])->get();
            $amount = min($trapped['u' . $unit], $amount);
            $this->conn->upgrade('trapped', [$unit => "$unit - $amount"], 'id = :id', ['id' => $id]);
        } else {
            $this->conn->upgrade('trapped', [$unit => "$unit + $amount"], 'id = :id', ['id' => $id]);
        }
    }

    public function removeTrapped($id)
    {
        $this->conn->delete('trapped', 'id = :id', ['id' => $id]);
    }

    public function removeAnimals($id)
    {
        $this->conn->delete('enforcement', 'id = :id', ['id' => $id]);
    }

    public function checkEnforce($vid, $from)
    {
        $result = $this->conn->select('id')->from('enforcement')->where('from = :from AND vref = :vref', ['from' => $from, 'vref' => $vid])->get();
        return count($result) ? $result : false;
    }

    public function addEnforce($data)
    {
        $id = $this->conn->insert('enforcement', ['vref' => $data['to'], 'from' => $data['from']]);

        $isoasis = $this->isVillageOases($data['from']);
        $fromVillage = $isoasis ? $this->getOMInfo($data['from']) : $this->getMInfo($data['from']);
        $fromTribe = $this->getUserField($fromVillage['owner'], 'tribe', 0);
        $start = ($fromTribe - 1) * 10 + 1;
        $end = ($fromTribe * 10);
        //add unit
        $j = '1';
        for ($i = $start; $i <= $end; $i++) {
            $this->modifyEnforce($id, $i, $data["t{$j}"], 1);
            $j++;
        }
        return $id;
    }

    public function getOMInfo($id)
    {
        return $this->conn->leftJoin('wdata', 'odata', 'odata.wref = wdata.id', '*', 'wdata.id = :id', ['id' => $id]);
    }

    public function getMInfo($id)
    {
        return $this->conn->leftJoin('wdata', 'vdata', 'vdata.wref = wdata.id', '*', 'wdata.id = :id', ['id' => $id]);
    }

    public function modifyEnforce($id, $unit, $amount, $mode)
    {
        if ($unit == 'hero') {
            $unit = 'hero';
        } else {
            $unit = 'u' . $unit;
        }
        if (!$mode) {
            $result = $this->conn->select($unit)->from('enforcement')->where('id = :id', ['id' => $id])->first();
            if (isset($result) && !empty($result) && count($result) > 0) {
                $this->conn->upgrade('enforcement', [$unit => ($unit - min($result[$unit], $amount))], 'id = :id', ['id' => $id]);
            }
        } else {
            $this->conn->upgrade('enforcement', [$unit => "$unit + $amount"], 'id = :id', ['id' => $id]);
        }
    }

    public function addHeroEnforce($data)
    {
        $this->conn->insert('enforcement', ['vref' => $data['to'], '`from`' => $data['from'], 'hero' => 1]);
    }

    public function getEnforceArray($id, $mode)
    {
        $column = !$mode ? '`id`' : '`from`';
        return $this->conn->select('*')->from('enforcement')->where("$column = :ref", ['ref' => $id])->first();
    }

    public function getEnforceVillage($id, $mode)
    {
        $column = !$mode ? 'vref' : 'from';
        return $this->conn->select('*')->from('enforcement')->where("$column = :ref", ['ref' => $id])->get();
    }

    public function getOasesEnforce($id)
    {
        $oasisowned = $this->getOasis($id);
        if (!empty($oasisowned) && count($oasisowned) > 0) {
            $inos = '(';
            foreach ($oasisowned as $oo) {
                $inos .= $oo['wref'] . ',';
            }
            $inos = substr($inos, 0, strlen($inos) - 1);
            $inos .= ')';

            return $this->conn->select('*')->from('enforcement')->where('`from` = :from AND `vref` IN :vref', ['from' => $id, 'vref' => $inos])->get();
        } else {
            return null;
        }
    }

    public function getOasis($vid)
    {
        return $this->conn->select('`type`,`wref`')->from('odata')->where('`conqured` = :conqured', ['conqured' => $vid])->get();
    }

    public function getVillageMovement($id)
    {
        $vinfo = $this->getVillage($id);
        if (isset($vinfo['owner'])) {
            $vtribe = $this->getUserField($vinfo['owner'], 'tribe', 0);
            $movingunits = array();
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
            $returning = $this->getMovement(4, $id, 1);
            if (!empty($returning)) {
                foreach ($returning as $ret) {
                    if ($ret['attack_type'] != 1) {
                        for ($i = 1; $i <= 10; $i++) {
                            $movingunits['u' . (($vtribe - 1) * 10 + $i)] += $ret["t{$i}"];
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
     * retrieve movement of village
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
        $where = !$mode ? '`from`' : '`to`';

        switch ($type) {
            case 0:
            case 1:
                $additionalJoin = 'JOIN send ON movement.ref = send.id';
                $sortTypeCondition = "movement.sort_type = {$type}";
                break;
            case 2:
            case 5:
            case 9:
                $additionalJoin = '';
                $sortTypeCondition = "movement.sort_type = {$type}";
                break;
            case 3:
            case 4:
                $additionalJoin = 'JOIN attacks ON movement.ref = attacks.id';
                $sortTypeCondition = "movement.sort_type = {$type}";
                $orderBy = 'ORDER BY endtime ASC';
                break;
            case 6:
                $additionalJoin = 'JOIN odata ON movement.to = odata.wref JOIN attacks ON movement.ref = attacks.id';
                $sortTypeCondition = 'movement.sort_type = 3';
                $whereCondition = "odata.conqured = {$village}";
                $orderBy = 'ORDER BY endtime ASC';
                break;
            case 34:
                $additionalJoin = 'JOIN attacks ON movement.ref = attacks.id';
                $sortTypeCondition = '(movement.sort_type = 3 OR movement.sort_type = 4)';
                $orderBy = 'ORDER BY endtime ASC';
                break;
            default:
                throw new \InvalidArgumentException('Invalid type: ' . $type);
        }

        $whereCondition = $whereCondition ?? "movement.$where = $village";
        $orderBy = $orderBy ?? '';

        $query = "SELECT * FROM movement $additionalJoin WHERE $whereCondition AND movement.proc = 0 AND $sortTypeCondition $orderBy";

        return $this->conn->executeQuery($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getVillageMovementArray($id)
    {
        $moving = [];
        $outgoing = $this->getMovement(3, $id, 0);
        if (!empty($outgoing)) {
            $moving = array_merge($moving, $outgoing);
        }
        $returning = $this->getMovement(4, $id, 1);
        if (!empty($returning)) {
            $moving = array_merge($moving, $returning);
        }
        return $moving;
    }

    public function getWW()
    {
        $result = $this->conn->select('vref')->from('fdata')->where('f99t = 40')->first();
        return !empty($result);
    }

    /**
     * get world wonder level!
     */
    public function getWWLevel($vref)
    {
        $result = $this->conn->select('f99')->from('fdata')->where('vref = :vref', ['vref' => $vref])->first();
        return $result['f99'];
    }

    /**
     * get world wonder owner ID!
     */
    public function getWWOwnerID($vref)
    {
        $result = $this->conn->select('owner')->from('vdata')->where('wref = :wref', ['wref' => $vref])->limit(1)->first();
        return (int)$result['owner'];
    }

    /**
     * get user alliance name!
     */
    public function getUserAllianceID($id)
    {
        $result = $this->conn->select('alliance')->from('users')->where('id = :id', ['id' => $id])->limit(1)->first();
        return $result['alliance'];
    }

    /**
     * get WW name
     */
    public function getWWName($vref)
    {
        $result = $this->conn->select( 'wwname')->from('fdata')->where('vref = :vref', ['vref' => $vref])->first();
        return $result['wwname'];
    }

    /**
     * change WW name
     */
    public function submitWWname($vref, $name)
    {
        $this->conn->upgrade('fdata', ['wwname' => $name], 'vref = :vref', ['vref' => $vref]);
    }

    public function modifyCommence($id, $commence = 0)
    {
        if ($commence == 0) {
            $commence = time();
        }
        $this->conn->upgrade('training', ['commence' => $commence], 'id = :id', ['id' => $id]);
    }

    public function getTrainingList()
    {
        return $this->conn->select('`id`,`vref`,`unit`,`eachtime`,`endat`,`commence`,`amt`')
            ->from('training')
            ->where('amt != 0')
            ->limit(500)
            ->get();
    }

    public function getNeedDelete()
    {
        return $this->conn->select('uid')->from('deleting')->where('timestamp <= :time', ['time' => time()])->get();
    }

    public function countUser()
    {
        return $this->conn->count('users');
    }

    public function countAlli()
    {
        return $this->conn->count('alidata');
    }

    //MARKET FIXES
    public function getWoodAvailable($wref)
    {
        $result = $this->conn->select('wood')->from('vdata')->where('wref = :wref', ['wref' => $wref])->limit(1)->first();
        return (int)$result['wood'];
    }

    public function getClayAvailable($wref)
    {
        $result = $this->conn->select('clay')->from('vdata')->where('wref = :wref', ['wref' => $wref])->limit(1)->first();
        return (int)$result['clay'];
    }

    public function getIronAvailable($wref)
    {
        $result = $this->conn->select('iron')->from('vdata')->where('wref = :wref', ['wref' => $wref])->limit(1)->first();
        return (int)$result['iron'];
    }

    public function getCropAvailable($wref)
    {
        $result = $this->conn->select('crop')->from('vdata')->where('wref = :wref', ['wref' => $wref])->limit(1)->first();
        return $result['crop'];
    }

    public function getAvailableExpansionTraining()
    {
        $building = new Building();
        $technology = new Technology();
        $village = new Village();

        $result1 = $this->conn->select('(IF(exp1=0,1,0)+IF(exp2=0,1,0)+IF(exp3=0,1,0))')->from('vdata')->where('wref = :wref', ['wref' => $village->wid])->first();
        $maxslots = $result1 !== false ? (int)$result1 : 0;

        $residence = $building->getTypeLevel(25);
        $palace = $building->getTypeLevel(26);

        if ($residence > 0) {
            $maxslots -= (3 - floor($residence / 10));
        }
        if ($palace > 0) {
            $maxslots -= (3 - floor(($palace - 5) / 5));
        }

        $result2 = $this->conn->select('(u10+u20+u30)')->from('units')->where('vref = :vref', ['vref' => $village->wid])->first();
        $settlers = $result2 !== false ? (int)$result2 : 0;

        $result3 = $this->conn->select('(u9+u19+u29)')->from('units')->where('vref = :vref', ['vref' => $village->wid])->first();
        $chiefs = $result3 !== false ? (int)$result3 : 0;

        $current_movement = $this->getMovement(3, $village->wid, 0);
        $settlers += 3 * count($current_movement);

        if (!empty($current_movement)) {
            foreach ($current_movement as $build) {
                $settlers += $build['t10'];
                $chiefs += $build['t9'];
            }
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

        $result4 = $this->conn->select('(u10+u20+u30)')->from('enforcement')->where('`from` = :from', ['from' => $village->wid])->get();
        if (!empty($result4)) {
            foreach ($result4 as $reinf) {
                $settlers += $reinf[0];
            }
        }

        $result5 = $this->conn->select('(u10+u20+u30)')->from('trapped')->where('`from` = :from', ['from' => $village->wid])->get();
        if (!empty($result5)) {
            foreach ($result5 as $trapped) {
                $settlers += $trapped[0];
            }
        }

        $result6 = $this->conn->select('(u9+u19+u29)')->from('enforcement')->where('`from` = :from', ['from' => $village->wid])->get();
        if (!empty($result6)) {
            foreach ($result6 as $reinf) {
                $chiefs += $reinf[0];
            }
        }

        $result7 = $this->conn->select('(u9+u19+u29)')
            ->from('trapped')
            ->where('`from` = :from', ['from' => $village->wid])
            ->get();

        if (!empty($result7)) {
            foreach ($result7 as $trapped) {
                $chiefs += $trapped[0];
            }
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
        // trapped settlers/chiefs calculation required
        $settlerslots = $maxslots * 3 - $settlers - $chiefs * 3;
        $chiefslots = $maxslots - $chiefs - floor(($settlers + 2) / 3);

        if (!$technology->getTech((Session::get('tribe') - 1) * 10 + 9)) {
            $chiefslots = 0;
        }
        return ['chiefs' => $chiefslots, 'settlers' => $settlerslots];
    }

    public function addArtefact($vref, $owner, $type, $size, $name, $desc, $effecttype, $effect, $aoe, $img)
    {
        $data = [
            'vref' => $vref,
            'owner' => $owner,
            'type' => $type,
            'size' => $size,
            'conquered' => time(),
            'name' => $name,
            'desc' => $desc,
            'effecttype' => $effecttype,
            'effect' => $effect,
            'aoe' => $aoe,
            'img' => $img
        ];
        $this->conn->insert('artefacts', $data);
    }

    public function getOwnArtefactInfo($vref)
    {
        return $this->conn->select('*')->from('artefacts')->where('vref = :vref', ['vref' => $vref])->first();
    }

    public function getArtefactInfo($sizes)
    {
        if (count($sizes) != 0) {
            $size = ' AND ( FALSE ';
            foreach ($sizes as $s) {
                $size .= ' OR `artefacts`.`size` = ' . $s . ' ';
            }
            $size .= ' ) ';
        } else {
            $size = '';
        }
        return $this->conn->select('*')->from('artefacts')->where('true ' . $size, [$sizes])->orderByDesc('type')->get();
    }

    public function getArtefactInfoByDistance($coor, $distance, $sizes)
    {
        $params = [];
        if (count($sizes) != 0) {
            $size = ' AND ( FALSE ';
            foreach ($sizes as $s) {
                $size .= ' OR artefacts.size = :size ';
            }
            $size .= ' ) ';
            $params['size'] = $s;
        } else {
            $size = '';
        }
        $query = "SELECT *, (ROUND(SQRT(POW(LEAST(ABS( ? - wdata.x), ABS( ? - ABS( ? - wdata.x))), 2) + POW(LEAST(ABS( ? - wdata.y), ABS( ? - ABS( ? - wdata.y))), 2)),3)) AS distance"
            . " FROM wdata, artefacts WHERE artefacts.vref = wdata.id"
            . " AND (ROUND(SQRT(POW(LEAST(ABS( ? - wdata.x), ABS( ? - ABS( ? - wdata.x))), 2) + POW(LEAST(ABS( ? - wdata.y), ABS( ? - ABS( ? - wdata.y))), 2)),3)) <= ?"
            . $size
            . ' ORDER BY distance';
        $params = array_merge([$coor['x'], $coor['x'], $coor['max'], $coor['y'], $coor['y'], $coor['max'], $coor['x'], $coor['x'], $coor['max'], $coor['y'], $coor['y'], $coor['max'], $distance], $params);
        return $this->conn->executeQuery($query, $params);
    }

    public function arteIsMine($id, $newvref, $newowner)
    {
        $this->conn->upgrade('artefacts', ['owner' => $newowner], 'id = :id', ['id' => $id]);
        $this->captureArtefact($id, $newvref, $newowner);
    }

    public function captureArtefact($id, $newvref, $newowner)
    {
        // get the artefact
        $currentArte = $this->getArtefactDetails($id);

        // set new active artes for new owner
        // first inactive large and uinque artes if this currentArte is large/unique
        if ($currentArte['size'] == 2 || $currentArte['size'] == 3) {
            $ulArts = $this->conn->select('*')->from('artefacts')->where('owner = :owner AND status = 1 AND size <> 1', ['owner' => $newowner])->get();
            if (!empty($ulArts) && count($ulArts) > 0) {
                foreach ($ulArts as $art) {
                    $this->conn->upgrade('artefacts', ['status' => 2], 'id = :id', ['id' => $art['id']]);
                }
            }
        }
        // then check extra artes
        $vArts = $this->conn->select('*')->from('artefacts')->where('vref = :vref AND status = 1', ['vref' => $newowner])->get();
        if (!empty($vArts) && count($vArts) > 0) {
            foreach ($vArts as $art) {
                $this->conn->upgrade('artefacts', ['status' => 2], 'id = :id', ['id' => $art['id']]);
            }
        } else {
            $uArts = $this->conn->select('*')->from('artefacts')->where('owner = :owner AND status = 1', ['owner' => $newowner])->orderByDesc('conquered')->get();
            if (!empty($uArts) && count($uArts) > 2) {
                for ($i = 2; $i < count($uArts); $i++) {
                    $this->conn->upgrade('artefacts', ['status' => 2], 'id = :id', ['id' => $uArts[$i]['id']]);
                }
            }
        }
        // set currentArte -> owner, vref, conquered, status
        $this->conn->upgrade('artefacts', ['vref' => $newvref, 'owner' => $newowner, 'conquered' => time(), 'status' => 1], 'id = :id', ['id' => $id]);
        // set new active artes for old user
        if ($currentArte['status'] == 1) {
            #--- get olduser's active artes
            $ouaArts = $this->conn->select('*')->from('artefacts')->where('owner = :owner AND status = 1', ['owner' => $currentArte['owner']])->get();
            $ouiArts = $this->conn->select('*')->from('artefacts')->where('owner = :owner AND status = 2', ['owner' => $currentArte['owner']])->orderByDesc('conquered')->get();

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
                            $this->conn->upgrade('artefacts', ['status' => 1], 'id = :id', ['id' => $ia['id']]);
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
        return $this->conn->select('*')->from('artefacts')->where('id = :id', ['id' => $id])->first();
    }

    public function getHeroFace($userID)
    {
        return $this->conn->select('*')->from('heroface')->where('uid = :uid', ['uid' => $userID])->first();
    }

    public function addHeroFace($userID)
    {
        $data = [
            'uid' => $userID,
            'beard' => rand(0, 3),
            'ear' => rand(0, 3),
            'eye' => rand(0, 4),
            'eyebrow' => rand(0, 3),
            'face' => rand(0, 4),
            'hair' => rand(0, 4),
            'mouth' => rand(0, 3),
            'nose' => rand(0, 3),
            'color' => rand(0, 4)
        ];
        $this->conn->insert('heroface', $data);
    }

    public function modifyHeroFace($userID, $column, $value)
    {
        $hash = md5($userID . time());
        $this->conn->upgrade('heroface', [$column => $value, 'hash' => $hash], 'uid = :uid', ['id' => $userID]);
    }

    public function modifyWholeHeroFace($userID, $face, $color, $hair, $ear, $eyebrow, $eye, $nose, $mouth, $beard)
    {
        $hash = md5($userID . time());
        $this->conn->upgrade('heroface', ['face' => $face, 'color' => $color, 'hair' => $hair, 'ear' => $ear, 'eyebrow' => $eyebrow, 'eye' => $eye, 'nose' => $nose, 'mouth' => $mouth, 'beard' => $beard, 'hash' => $hash], 'uid = :uid', ['uid' => $userID]);
    }

    public function hasBeginnerProtection($vid)
    {
        $result = $this->conn->select('u.protect')->from('users u, vdata v')->where('u.id = v.owner AND v.wref = :wref', ['wref' => $vid])->first();
        return $result && time() < $result['protect'] ? true : false;
    }

    public function addCLP($userID, $clp)
    {
        $this->conn->upgrade('users', ['clp' => "clp + $clp"], 'id = :id', ['id' => $userID]);
    }

    public function sendwlcMessage($client, $owner, $topic, $message, $send)
    {
        $data = [
            'target' => $client,
            'owner' => $owner,
            'topic' => $topic,
            'message' => $message,
            'viewed' => 1,
            'archived' => 0,
            'send' => $send,
            'time' => time()
        ];
        $this->conn->insert('mdata', $data);
    }

    public function getLinks($userID)
    {
        return $this->conn->select('`url`,`name`')->from('links')->where('`userid` = :userid', ['userid' => $userID])->orderByAsc('pos')->get();
    }

    public function removeLinks($id, $userID)
    {
        $this->conn->delete('links', 'id = :id, userid = :userid', ['id' => $id, 'userid' => $userID]);
    }

    public function getFarmlist($userID)
    {
        $result = $this->conn->select('id')->from('farmlist')->where('owner = :owner', ['owner' => $userID])->orderByAsc('name')->first();
        return $result['id'] != 0 ? true : false;
    }

    public function getRaidList($id)
    {
        return $this->conn->select('*')->from('raidlist')->where('id = :id', ['id' => $id])->get();
    }

    public function getAllAuction()
    {
        return $this->conn->select('*')->from('auction')->where('finish = 0')->get();
    }

    public function getVilFarmlist($wref)
    {
        $result = $this->conn->select('id')->from('farmlist')->where('wref = :wref', ['wref' => $wref])->orderByAsc('wref')->first();
        return $result['id'] != 0 ? true : false;
    }

    public function delFarmList($id, $owner)
    {
        $this->conn->delete('farmlist', 'id = :id AND owner = :owner', ['id' => $id, 'owner' => $owner]);
    }

    public function delSlotFarm($id)
    {
        $this->conn->delete('raidlist', 'id = :id', ['id' => $id]);
    }

    public function createFarmList($wref, $owner, $name)
    {
        $this->conn->insert('farmlist', ['wref' => $wref, 'owner' => $owner, 'name' => $name]);
    }

    public function addSlotFarm($lid, $towref, $x, $y, $distance, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10)
    {
        $data = [
            'lid' => $lid, 'towref' => $towref, 'x' => $x, 'y' => $y, 'distance' => $distance,
            't1' => $t1, 't2' => $t2, 't3' => $t3, 't4' => $t4, 't5' => $t5,
            't6' => $t6, 't7' => $t7, 't8' => $t8, 't9' => $t9, 't10' => $t10
        ];
        $this->conn->insert('raidlist', $data);
    }

    public function editSlotFarm($eid, $lid, $wref, $x, $y, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10)
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
        $this->conn->upgrade('raidlist', $data, 'id = :id', ['id' => $eid]);
    }

    public function removeOases($wref)
    {
        $r1 = $this->conn->upgrade('odata', ['conqured' => 0, 'owner' => 3, 'name' => 'Unoccupied oasis'], 'wref = :wref', ['wref' => $wref]);
        $r2 = $this->conn->upgrade('wdata', ['occupied' => 0], 'id = :id', ['id' => $wref]);
        return ($r1 && $r2);
    }

    public function getArrayMemberVillage($uid)
    {
        $columns = 'a.wref, a.name, a.capital, b.x, b.y';
        $onCondition = 'b.id = a.wref';
        $where = 'a.owner = :uid';
        $params = ['uid' => $uid];
        $orderBy = 'a.capital DESC, a.pop DESC';

        return $this->conn->leftJoin('vdata AS a', 'wdata AS b', $onCondition, $columns, $where, $params, $orderBy);
    }

    public function getNoticeData($nid)
    {
        $result = $this->conn->select('`data`')->from('ndata')->where('id = :id', ['id' => $nid])->first();
        return $result['data'];
    }

    public function getUsersNotice($userID, $ntype = -1, $viewed = -1)
    {
        $params = [];
        $where = 'uid = :uid';
        $params['uid'] = $userID;

        if ($ntype >= 0) {
            $where .= ' AND ntype = :ntype ';
            $params['ntype'] = $ntype;
        }
        if ($viewed >= 0) {
            $where .= ' AND viewed = :viewed';
            $params['viewed'] = $viewed;
        }
        return $this->conn->select('*')->from('ndata')->where($where, $params)->get();
    }

    public function setSilver($userID, $silver, $mode)
    {
        if (!$mode) {
            $this->conn->upgrade('users', ['silver' => "silver - $silver"], 'id = :id', ['id' => $userID]);
            $this->conn->upgrade('users', ['usedsilver' => "usedsilver + $silver"], 'id = :id', ['id' => $userID]);
        } else {
            $this->conn->upgrade('users', ['silver' => "silver + $silver"], 'id = :id', ['id' => $userID]);
            $this->conn->upgrade('users', ['Addsilver' => "Addsilver + $silver"], 'id = :id', ['id' => $userID]);
        }
    }

    public function getAuctionSilver($userID)
    {
        return $this->conn->select('*')->from('auction')->where('uid = :uid AND finish = 0', ['uid' => $userID])->first();
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function delAuction($id)
    {
        $aucData = $this->getAuctionData($id);
        $usedtime = AUCTIONTIME - ($aucData['time'] - time());
        if (($usedtime < (AUCTIONTIME / 10)) && !$aucData['bids']) {
            $this->modifyHeroItem($aucData['itemid'], 'num', $aucData['num'], 1);
            $this->modifyHeroItem($aucData['itemid'], 'proc', 0, 0);
            $this->conn->delete('auction', 'id = :id AND finish = 0', ['id' => $id]);
        } else {
            return false;
        }
    }

    public function getAuctionData($id)
    {
        return $this->conn->select('*')->from('auction')->where('id = :id', ['id' => $id])->first();
    }

    public function modifyHeroItem($id, $column, $value, $mode)
    {
        $data = match ($mode) {
            0 => [$column => $value],               // mode=0 set
            1 => [$column => "$column + $value"],   // mode=1 add
            2 => [$column => "$column - $value"],   // mode=2 sub
            3 => [$column => "$column * $value"],   // mode=3 mul
            4 => [$column => "$column / $value"],   // mode=4 div
        };
        $this->conn->upgrade('heroitems', $data, 'id = :id', ['id' => $id]);
    }

    public function getAuctionUser($userID)
    {
        return $this->conn->select('*')->from('auction')->where('owner = :owner', ['owner' => $userID])->get();
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
        $silver = match ($btype) {
            7, 8, 9, 10, 11, 14 => $amount,
            default => $amount * 100,
        };
        return $this->conn->insert('auction', ['owner' => $owner, 'itemid' => $itemid, 'btype' => $btype, 'type' => $type, 'num' => $amount, 'uid' => 0, 'bids' => 0, 'silver' => $silver, 'maxsilver' => $silver, 'time' => $time, 'finish' => 0]);
    }

    public function getHeroItem($id = 0, $userID = 0, $btype = 0, $type = 0, $proc = 2)
    {
        $where = ' true ';
        $params = [];
        if ($id) {
            $where .= ' AND id = :id';
            $params['id'] = $id;
        }
        if ($userID) {
            $where .= ' AND uid = :uid';
            $params['uid'] = $userID;
        }
        if ($btype) {
            $where .= ' AND btype = :btype';
            $params['btype'] = $btype;
        }
        if ($type) {
            $where .= ' AND type = :type';
            $params['type'] = $type;
        }
        if ($proc != 2) {
            $where .= ' AND proc = :proc';
            $params['proc'] = $proc;
        }

        $result = $this->conn->select('*')->from('heroitems')->where($where, $params)->get();

        if ($id) {
            return isset($result) ? $result : [];
        }
        return $result;
    }

    public function addBid($id, $userID, $silver, $maxsilver, $time)
    {
        $data = [
            'uid' => $userID,
            'silver' => $silver,
            'maxsilver' => $maxsilver,
            'bids' => 'bids + 1',
            'time' => $time
        ];
        $this->conn->upgrade('auction', $data, 'id = :id', ['id' => $id]);
    }

    public function removeBidNotice($id)
    {
        $this->conn->delete('auction', 'id = :id', ['id' => $id]);
    }

    public function addHeroItem($userID, $btype, $type, $num)
    {
        $this->conn->insert('heroitems', ['uid' => $userID, 'btype' => $btype, 'type' => $type, 'num' => $num, 'proc' => 0]);
    }

    public function checkHeroItem($userID, $btype, $type = 0, $proc = 2)
    {
        $params = [];
        $where = 'true ';
        if ($userID) {
            $where .= ' AND uid = :uid ';
            $params['uid'] = $userID;
        }
        if ($btype) {
            $where .= ' AND btype = :btype';
            $params['btype'] = $btype;
        }
        if ($type) {
            $where .= ' AND type = :type';
            $params['type'] = $type;
        }
        if ($proc != 2) {
            $where .= ' AND proc = :proc';
            $params['proc'] = $proc;
        }
        $result = $this->conn->select('id, btype')->from('heroitems')->where($where, $params)->first();
        return isset($result['btype']) ? $result['id'] : false;
    }

    public function editBid($id, $maxsilver, $minsilver)
    {
        $this->conn->upgrade('auction', ['maxsilver' => $maxsilver, 'silver' => $minsilver], 'id = :id', ['id' => $id]);
    }

    public function getBidData($id)
    {
        return $this->conn->select('*')->from('auction')->where('id = :id', ['id' => $id])->first();
    }

    public function getFLData($id)
    {
        return $this->conn->select('*')->from('farmlist')->where('id = :id', ['id' => $id])->first();
    }

    public function getHeroField($userID, $field)
    {
        $result = $this->conn->select($field)->from('hero')->where('uid = :uid', ['uid' => $userID])->first();
        return $result[$field];
    }

    public function getCapBrewery($userID)
    {
        $capWref = $this->getVFH($userID);
        if ($capWref) {
            $result = $this->conn->select('*')->from('fdata')->where('vref = :vref', ['vref' => $capWref])->first();
            if (!empty($result)) {
                for ($i = 19; $i <= 40; $i++) {
                    if ($result["f{$i}t"] == 35) {
                        return $result["f{$i}"];
                    }
                }
            }
        }
        return 0;
    }

    public function getVFH($userID)
    {
        $result = $this->conn->select('wref')->from('vdata')->where('owner = :owner AND capital = 1', ['owner' => $userID])->first();
        return $result['wref'];
    }

    public function getNotice2($id, $field)
    {
        $result = $this->conn->select($field)->from('ndata')->where('id = :id', ['id' => $id])->first();
        return $result[$field];
    }

    public function addAdventure($wref, $userID)
    {
        $time = time() + (3600 * 120);
        $ddd = rand(0, 3);
        $dif = $ddd == 1 ? 1 : 0;
        $lastw = 641601;
        if (($wref - 10000) <= 10) {
            $w1 = rand(10, ($wref + 10000));
        } elseif (($wref + 10000) >= $lastw) {
            $w1 = rand(($wref - 10000), ($lastw - 10000));
        } else {
            $w1 = rand(($wref - 10000), ($wref + 10000));
        }
        $data = [
            'wref' => $w1,
            'uid' => $userID,
            'dif' => $dif,
            'time' => $time,
            'end' => 0
        ];
        $this->conn->insert('adventure', $data);
    }

    public function addHero($userID)
    {
        $time = time();

        $tribe = $this->getUserField($userID, 'tribe', 0);

        $default = [
            0 => ['cpproduction' => 0, 'speed' => 7, 'rob' => 0, 'fsperpoint' => 100, 'extraresist' => 0, 'vsnatars' => 0, 'autoregen' => 10, 'extraexpgain' => 0, 'accountmspeed' => 0, 'allymspeed' => 0, 'longwaymspeed' => 0, 'returnmspeed' => 0],
            1 => ['cpproduction' => 5, 'speed' => 6, 'rob' => 0, 'fsperpoint' => 100, 'extraresist' => 4, 'vsnatars' => 25, 'autoregen' => 20, 'extraexpgain' => 0, 'accountmspeed' => 0, 'allymspeed' => 0, 'longwaymspeed' => 0, 'returnmspeed' => 0],
            2 => ['cpproduction' => 0, 'speed' => 8, 'rob' => 10, 'fsperpoint' => 90, 'extraresist' => 0, 'vsnatars' => 0, 'autoregen' => 10, 'extraexpgain' => 15, 'accountmspeed' => 0, 'allymspeed' => 0, 'longwaymspeed' => 0, 'returnmspeed' => 0],
            3 => ['cpproduction' => 0, 'speed' => 10, 'rob' => 0, 'fsperpoint' => 80, 'extraresist' => 0, 'vsnatars' => 0, 'autoregen' => 10, 'extraexpgain' => 0, 'accountmspeed' => 30, 'allymspeed' => 15, 'longwaymspeed' => 25, 'returnmspeed' => 30],
            4 => ['cpproduction' => 0, 'speed' => 7, 'rob' => 0, 'fsperpoint' => 100, 'extraresist' => 0, 'vsnatars' => 0, 'autoregen' => 10, 'extraexpgain' => 0, 'accountmspeed' => 0, 'allymspeed' => 0, 'longwaymspeed' => 0, 'returnmspeed' => 0],
            5 => ['cpproduction' => 0, 'speed' => 7, 'rob' => 0, 'fsperpoint' => 100, 'extraresist' => 0, 'vsnatars' => 0, 'autoregen' => 10, 'extraexpgain' => 0, 'accountmspeed' => 0, 'allymspeed' => 0, 'longwaymspeed' => 0, 'returnmspeed' => 0]
        ];

        $hero = $default[$tribe];

        $data = [
            'uid' => $userID,
            'wref' => 0,
            'level' => 0,
            'speed' => $hero['speed'],
            'points' => 0,
            'experience' => '0',
            'dead' => 0,
            'health' => '100',
            'power' => '0',
            'fsperpoint' => $hero['fsperpoint'],
            'offBonus' => '0',
            'defBonus' => '0',
            'product' => '4',
            'r0' => '1',
            'autoregen' => $hero['autoregen'],
            'extraexpgain' => $hero['extraexpgain'],
            'cpproduction' => $hero['cpproduction'],
            'rob' => $hero['rob'],
            'extraresist' => $hero['extraresist'],
            'vsnatars' => $hero['vsnatars'],
            'accountmspeed' => $hero['accountmspeed'],
            'allymspeed' => $hero['allymspeed'],
            'longwaymspeed' => $hero['longwaymspeed'],
            'returnmspeed' => $hero['returnmspeed'],
            'lastupdate' => $time,
            'lastadv' => '0',
            'hash' => md5($time)
        ];
        $this->conn->insert('hero', $data);
    }

    // Add new password => mode:0
    // Add new email => mode: 1
    public function addNewProc($userID, $npw, $nemail, $act, $mode)
    {
        $data = ['uid' => $userID, 'act' => $act, 'time' => time(), 'proc' => 0];
        if (!$mode) {
            $data['npw'] = $npw;
        } else {
            $data['nemail'] = $nemail;
        }
        $this->conn->insert('newproc', $data);
    }

    public function checkProcExist($userID)
    {
        $result = $this->conn->select('uid')->from('newproc')->where('uid = :uid AND proc = 0', ['uid' => $userID])->first();
        return !empty($result);
    }

    public function removeProc($userID)
    {
        $this->conn->delete('newproc', 'uid = :uid', ['uid' => $userID]);
    }

    public function checkBan($userID)
    {
        $result = $this->conn->select('access')->from('users')->where('id = :id', ['id' => $userID])->limit(1)->first();
        return !empty($result) && ($result['access'] <= 1 || $result['access'] >= 7) ? true : false;
    }

    public function getNewProc($userID)
    {
        return $this->conn->select('`npw`,`act`')->from('newproc')->where('uid = :uid', ['uid' => $userID])->get();
    }

    public function checkAdventure($userID, $wref, $end)
    {
        return $this->conn->select('`id`')
            ->from('adventure')
            ->where('uid = :uid AND wref = :wref AND end = :end', ['uid' => $userID, 'wref' => $wref, 'end' => $end])
            ->get();
    }

    public function getAdventure($userID, $wref = 0, $end = 2)
    {
        $params = [];
        $where = 'uid = :uid';
        $params['uid'] = $userID;

        if ($wref != 0) {
            $where .= ' AND wref = :wref ';
            $params['wref'] = $wref;
        }
        if ($end != 2) {
            $where .= ' AND end = :end ';
            $params['end'] = $end;
        }
        return $this->conn->select('`id`,`dif`')->from('adventure')->where($where, $params)->get();
    }

    public function editTableField($table, $field, $value, $refField, $ref)
    {
        $this->conn->upgrade($table, [$field => $value], $refField = $ref);
    }

    public function getAllianceDipProfile($aid, $type)
    {
        $allianceLinks = '';
        $alliances1 = $this->conn->select('`alli2`')->from('diplomacy')->where('alli1 = :alli1 AND type = :type AND accepted = 1', ['alli1' => $aid, 'type' => $type])->get();
        $alliances2 = $this->conn->select('`alli1`')->from('diplomacy')->where('alli2 = :alli1 AND type = :type AND accepted = 1', ['alli2' => $aid, 'type' => $type])->get();
        if (!empty($alliances1)) {
            foreach ($alliances1 as $alliance1) {
                $alliance = $this->getAlliance($alliance1['alli2']);
                $allianceLinks .= "<a href='allianz.php?aid={$alliance['id']}'>{$alliance['tag']}</a><br>";
            }
        }
        if (!empty($alliances2)) {
            foreach ($alliances2 as $alliance2) {
                $alliance = $this->getAlliance($alliance2['alli1']);
                $allianceLinks .= "<a href='allianz.php?aid={$alliance['id']}'>{$alliance['tag']}</a><br>";
            }
        }
        if (empty($allianceLinks)) {
            $allianceLinks = '-<br>';
        }
        return $allianceLinks;
    }

    public function getAlliance($id, $mode = 0)
    {
        $where = '';
        $params = [];
        switch ($mode) {
            case 0:
                $where = 'id = :id';
                $params['id'] = $id;
                break;
            case 1:
                $where = 'name = :name';
                $params['name'] = $id;
                break;
            case 2:
                $where = 'tag = :tag';
                $params['tag'] = $id;
                break;
        }
        return $this->conn->select('`id`,`tag`,`desc`,`max`,`name`,`notice`')->from('alidata')->where($where, $params)->first();
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function canClaimArtifact($vref, $type)
    {
        $defenderFields = $this->getResourceLevel($vref);
        $attackerFields = $this->getResourceLevel($vref);

        for ($i = 19; $i <= 38; $i++) {
            if ($defenderFields["f{$i}t"] == 27) {
                $defcanclaim = false;
            } else {
                $defcanclaim = true;
            }
        }

        for ($i = 19; $i <= 38; $i++) {
            if ($attackerFields["f{$i}t"] == 27) {
                $attTresuaryLevel = $attackerFields["f{$i}"];
                if ($attTresuaryLevel >= 10) {
                    $villageartifact = true;
                } else {
                    $villageartifact = false;
                }
                if ($attTresuaryLevel == 20) {
                    $accountartifact = true;
                } else {
                    $accountartifact = false;
                }
            }
        }
        if ($type == 1) {
            if ($defcanclaim == true && $villageartifact == true) {
                return true;
            }
        } else if ($type == 2) {
            if ($defcanclaim == true && $accountartifact == true) {
                return true;
            }
        } else if ($type == 3) {
            if ($defcanclaim == true && $accountartifact == true) {
                return true;
            }
        } else {
            return false;
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        if (!isset($pct)) {
            return false;
        }
        $pct /= 100;
        // Get image width and height
        $w = imagesx($src_im);
        $h = imagesy($src_im);
        // Turn alpha blending off
        imagealphablending($src_im, false);
        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for ($x = 0; $x < $w; $x++)
            for ($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }
        //loop through image pixels and modify alpha for each
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                //get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($src_im, $x, $y);
                $alpha = ($colorxy >> 24) & 0xFF;
                //calculate new alpha
                if ($minalpha !== 127) {
                    $alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $pct;
                }
                //get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
                //set pixel with the new color + opacity
                if (!imagesetpixel($src_im, $x, $y, $alphacolorxy)) {
                    return false;
                }
            }
        }
        // The image copy
        imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
    }

    public function getCropProdstarv($wref)
    {
        $basecrop = $grainmill = $bakery = 0;
        $owner = $this->getVillageField($wref, 'owner');
        $bonus = $this->getUserField($owner, 'b4', 0);
        $buildarray = $this->getResourceLevel($wref);
        $cropholder = [];
        for ($i = 1; $i <= 38; $i++) {
            if ($buildarray["f{$i}t"] == 4) {
                array_push($cropholder, "f{$i}");
            }
            if ($buildarray["f{$i}t"] == 8) {
                $grainmill = $buildarray["f{$i}"];
            }
            if ($buildarray["f{$i}t"] == 9) {
                $bakery = $buildarray["f{$i}"];
            }
        }
        $oases = $this->conn->select('type')->from('odata')->where('conqured = :conqured', ['conqured' => $wref])->get();
        $cropo = 0;
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
        for ($i = 0; $i <= count($cropholder) - 1; $i++) {
            $basecrop += $bid4[$buildarray[$cropholder[$i]]]['prod'];
        }
        $crop = $basecrop + $basecrop * 0.25 * $cropo;
        if ($grainmill >= 1 || $bakery >= 1) {
            $crop += $basecrop / 100 * ($bid8[$grainmill]['attri'] + $bid9[$bakery]['attri']);
        }
        if ($bonus > time()) {
            $crop *= 1.25;
        }
        $crop *= setting('speed');
        return $crop;
    }

    public function getNatarsProgress()
    {
        return $this->conn->select('*')->from('natarsprogress')->get();
    }

    public function setNatarsProgress($field, $value)
    {
        $this->conn->upgrade('natarsprogress', [$field => $value], '');
    }

    public function getNatarsCapital()
    {
        return $this->conn->select('`wref`')->from('vdata')->where('owner = 2 AND capital = 1')->orderByAsc('created')->get();
    }

    public function getNatarsWWVillages()
    {
        return $this->conn->select('`owner`')->from('vdata')->where('owner = 2 AND name = "WW Village"')->orderByAsc('created')->get();
    }

    public function addNatarsVillage($worlID, $userID, $capital)
    {
        $total = count($this->getVillagesID($userID));
        $vname = sprintf('[%05d] Natars', $total + 1);
        $time = time();
        $data = [
            'wref' => $worlID, 'owner' => $userID, 'name' => $vname, 'capital' => $capital, 'pop' => 2, 'cp' => 1,
            'celebration' => 0, 'wood' => 780, 'clay' => 780, 'iron' => 780, 'maxstore' => 800, 'crop' => 780,
            'maxcrop' => 800, 'lastupdate' => $time, 'created' => $time, 'natar' => 1
        ];
        $this->conn->insert('vdata', $data);
    }

    public function instantTrain($vref)
    {
        $result = $this->conn->select('`id`')->from('training')->where('vref = :vref', ['vref' => $vref])->first();
        $count = count($result);
        $this->conn->upgrade('training', ['commence' => 0, 'eachtime' => 1, 'endat' => 0, 'timestamp' => 0], 'vref = :vref', ['vref' => $vref]);

        return $result ? $count : -1;
    }

    public function hasWinner()
    {
        $winner = $this->conn->select('vref')
            ->from('fdata')
            ->where('f99 = 100 AND f99t = 40')
            ->first();
        $winner > 0 ? true : false;
    }

    public function getVillageActiveArte($vref)
    {
        $conquered = (time() - max(86400 / setting('speed'), 600));
        return $this->conn->select('*')
            ->from('artefacts')
            ->where('vref = :vref AND status = 1 AND conquered <= :conquered', ['vref' => $vref, 'conquered' => $conquered])
            ->first();
    }

    public function getAccountActiveArte($owner)
    {
        $conquered = (time() - max(86400 / setting('speed'), 600));
        return $this->conn->select('*')
            ->from('artefacts')
            ->where('owner = :owner AND status = 1 AND conquered <= :conquered', ['owner' => $owner, 'conquered' => $conquered])
            ->first();
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
        $owner = $this->getVillage($wref);
        if (!empty($owner) && isset($owner['owner'])) {
            $conquered = (time() - max(86400 / setting('speed'), 600));
            $results = $this->conn->select('`vref`,`effect`,`aoe`')
                ->from('artefacts')
                ->where('owner = :owner AND effecttype = :type AND status = 1 AND conquered <= :conquered', ['owner' => $owner['owner'], 'type' => $type, 'conquered' => $conquered])
                ->orderByDesc('conquered')
                ->first();
            if (!empty($results) && count($results) > 0) {
                $i = 0;
                foreach ($results as $result) {
                    if ($result['vref'] == $wref) {
                        return $result['effect'];
                    }
                    if ($result['aoe'] == 3) {
                        return $result['effect'];
                    }
                    $i += 1;
                    if ($i >= 3) {
                        break;
                    }
                }
            }
        }
        return $artEff;
    }

    public function updateFoolArtes()
    {
        $conquered = (time() - max(86400 / setting('speed'), 600));
        $results = $this->conn->select('`id`,`size`')
            ->from('artefacts')
            ->where("type = 3 AND status = 1 AND conquered <= $conquered AND lastupdate <= $conquered")
            ->get();
        if (!empty($results) && count($results) > 0) {
            foreach ($results as $result) {
                $effecttype = rand(3, 9);
                if ($effecttype == 3) {
                    $effecttype = 2;
                }
                $aoerand = rand(1, 100);
                if ($aoerand <= 75) {
                    $aoe = 1;
                } elseif ($aoerand <= 95) {
                    $aoe = 2;
                } else {
                    $aoe = 3;
                }
                $effect = match ($effecttype) {
                    2 => $result['size'] == 1 ? rand(100, 500) / 100 : rand(100, 1000) / 100,
                    4 => $result['size'] == 1 ? rand(100, 300) / 100 : rand(100, 600) / 100,
                    5 => $result['size'] == 1 ? rand(100, 1000) / 100 : rand(100, 2000) / 100,
                    6 => $result['size'] == 1 ? rand(50, 100) / 100 : rand(25, 100) / 100,
                    7 => $result['size'] == 1 ? rand(100, 50000) / 100 : rand(100, 100000) / 100,
                    8 => $result['size'] == 1 ? rand(50, 100) / 100 : rand(25, 100) / 100,
                    9 => $result['size'] == 1 ? 1 : 1,
                };
                if ($result['size'] == 1 && rand(1, 100) <= 50) {
                    $effect = 1 / $effect;
                }
                $this->conn->upgrade('artefacts', ['effecttype' => $effecttype, 'effect' => $effect, 'aoe' => $aoe], 'id = :id', ['id' => $result['id']]);
            }
        }
    }

    public function getArtEffDiet($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 6);
        if ($res != 0) {
            $artEff = $res;
        }
        return $artEff;
    }

    public function getArtEffGrt($wref)
    {
        $artEff = 0;
        $res = $this->getArteEffectByType($wref, 9);
        if ($res != 0) {
            $artEff = $res;
        }
        return $artEff;
    }

    public function getArtEffArch($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 2);
        if ($res != 0) {
            $artEff = $res;
        }
        return $artEff;
    }

    public function getArtEffSpy($wref)
    {
        $artEff = 0;
        $res = $this->getArteEffectByType($wref, 5);
        if ($res != 0) {
            $artEff = $res;
        }
        return $artEff;
    }

    public function getArtEffTrain($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 8);
        if ($res != 0) {
            $artEff = $res;
        }
        return $artEff;
    }

    public function getArtEffConf($wref)
    {
        $artEff = 1;
        $res = $this->getArteEffectByType($wref, 7);
        if ($res != 0) {
            $artEff = $res;
        }
        return $artEff;
    }

    public function getArtEffBP($wref)
    {
        $artEff = 0;
        $vinfo = $this->getVillage($wref);

        $conquered = (time() - max(86400 / setting('speed'), 600));
        $result = $this->conn->select('id')
            ->from('artefacts')
            ->where('owner = :owner AND effecttype = 11 AND status = 1 AND conquered <= :conquered', ['ownew' => $vinfo['owner'], 'conquered' => $conquered])
            ->orderByDesc('conquered')
            ->get();
        if (!empty($result) && count($result) > 0) {
            return $artEff = 1;
        }
        return $artEff;
    }

    public function getArtEffAllyBP($userID)
    {
        $artEff = 0;
        $userAlli = $this->getUserField($userID, 'alliance', 0);
        $diplos = $this->conn->select('`alli1`,`alli2`')->from('diplomacy')->where('alli1 = :alli1 OR alli2 = :alli2 AND accepted <> 0', ['alli1' => $userAlli, 'alli2' => $userAlli])->get();

        if (!empty($diplos) && count($diplos) > 0) {
            $alliances = [];
            foreach ($diplos as $ds) {
                $alliances[] = $ds['alli1'];
                $alliances[] = $ds['alli2'];
            }
            $alliances = array_unique($alliances);
            $alliancestr = implode(',', $alliances);

            $mate = $this->conn->select('`id`')
                ->from('users')
                ->where('alliance IN :alliance AND id <> :id', ['alliance' => $alliancestr, 'id' => $userID])
                ->get();
            if (!empty($mate) && count($mate) > 0) {
                $ml = [];
                foreach ($mate as $ms) {
                    $ml[] = $ms['id'];
                }
                $matestr = implode(',', $ml);

                $result = $this->conn->select('`id`')->from('artefacts')->where('owner IN (:owner) AND effecttype = 11 AND status = 1 AND conquered <= :conquered', ['owner' => $matestr, 'conquered' => (time() - max(86400 / setting('speed'), 600))])->orderByDesc('conquered')->get();
                if (!empty($result) && count($result) > 0) {
                    return $artEff = 1;
                }
            }
        }
        return $artEff;
    }

    public function modifyExtraVillage($worlID, $column, $value)
    {
        $this->conn->upgrade('vdata', [$column => "$column + $value"], 'vref = :vref', ['vref' => $worlID]);
    }

    public function modifyFieldLevel($worlID, $field, $level, $mode)
    {
        $b = "f{$field}";
        $operation = $mode ? '+' : '-';
        $this->conn->upgrade('fdata', [$b => "$b $operation $level"], 'vref = :vref', ['vref' => $worlID, 'level' => $level]);
    }

    public function modifyFieldType($worlID, $field, $type)
    {
        $this->conn->upgrade('fdata', ["f{$field}t" => $type], 'vref = :vref', ['vref' => $worlID]);
    }

    public function resendact($mail)
    {
        return $this->conn->select('`id`, `username`, `email`, `password`')
            ->from('users')
            ->where('email = :email', ['email' => $mail])
            ->limit(0, 1)
            ->first();
    }

    public function changemail($mail, $id)
    {
        $this->conn->upgrade('users', ['email' => $mail], 'id = :id', ['id' => $id]);
    }

    public function register2($username, $password, $email, $act, $activateat)
    {
        $time = time();
        if (strtotime(START_TIME) > time()) {
            $time = strtotime(START_TIME);
        }
        $timep = ($time + PROTECTION);
        $rand = rand(8900, 9000);

        $data = [
            'username' => $username,
            'password' => $password,
            'access' => 2,
            'email' => $email,
            'timestamp' => $time,
            'action' => $act,
            'protect' => $timep,
            'fquest' => '0,0,0,0,0,0,0,0,0,0,0',
            'clp' => $rand,
            'cp' => 1,
            'reg2' => 1,
            'activateat' => $activateat
        ];
        $this->conn->insert('users', $data) ? $this->conn->getLastInsertId() : false;
    }

    public function checkname($id)
    {
        return $this->conn->select('username, email')->from('users')->where('id = :id', ['id' => $id])->first();
    }

    public function settribe($tribe, $userID)
    {
        return $this->conn->upgrade('users', ['tribe' => $tribe], 'id = :id AND reg2 = 1', ['id' => $userID]);
    }

    public function checkreg($userID)
    {
        return $this->conn->select('reg2')->from('users')->where('id = :id', ['id' => $userID])->first();
    }

    public function checkreg2($name)
    {
        return $this->conn->select('reg2')->from('users')->where('username = :username', ['username' => $name])->first();
    }

    public function checkid($name)
    {
        return $this->conn->select('id')->from('users')->where('username = :username', ['username' => $name])->first();
    }

    public function setreg2($userID)
    {
        $this->conn->upgrade('users', ['reg2' => 0], 'id = :id AND reg2 = 1', ['id' => $userID]);
    }

    public function getNotice5($userID)
    {
        return $this->conn->select('`id`')
            ->from('ndata')
            ->where('uid = :uid AND viewed = 0', ['uid' => $userID, 'viewed' => 0])
            ->orderByDesc('time')
            ->limit(1)
            ->first();
    }

    public function setref($id, $name)
    {
        $this->conn->insert('reference', ['player_id' => $id, 'player_name' => $name]);
    }

    public function getAttackCasualties($time)
    {
        $generals = $this->conn->select('`time`')->from('general')->where('shown = 1')->get();
        $casualties = 0;
        foreach ($generals as $general) {
            if (date('j. M', $time) == date('j. M', $general['time'])) {
                $casualties += $general['casualties'];
            }
        }
        return $casualties;
    }

    public function getAttackByDate($time)
    {
        $generals = $this->conn->select('`time`')->from('general')->where('shown = 1')->get();
        $attack = 0;
        foreach ($generals as $general) {
            if (date('j. M', $time) == date('j. M', $general['time'])) {
                $attack += 1;
            }
        }
        return $attack * 100;
    }

    public function getStatsInfo($userID, $time, $inf)
    {
        $users = $this->conn->select("{$inf}, time")->from('stats')->where('owner = :owner', ['owner' => $userID])->get();
        $t = 0;
        foreach ($users as $user) {
            if (date('j. M', $time) == date('j. M', $user['time'])) {
                $t += ($inf == 'rank') ? $user[$inf] : 0;
                if ($inf == 'rank') {
                    break;
                }
            }
        }
        return $t;
    }

    public function modifyHero2($column, $value, $userID, $mode)
    {
        $data = match ($mode) {
            1 => [$column => "$column + $value"],
            2 => [$column => "$column - $value"],
            default => [$column => $value],
        };
        $this->conn->upgrade('hero', $data, 'uid = :uid', ['uid' => $userID]);
    }

    public function createTradeRoute($userID, $worlID, $from, $r1, $r2, $r3, $r4, $start, $deliveries, $merchant, $time)
    {
        $this->conn->upgrade('users', ['gold' => 'gold - 2'], 'id = :id', ['id' => $userID]);

        $data = [
            'uid' => $userID,
            'wid' => $worlID,
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
        $this->conn->insert('route', $data);
    }

    public function getTradeRoute($userID)
    {
        return $this->conn->select('*')->from('route')->where('uid = :uid', ['uid' => $userID])->orderByAsc('timestamp')->get();
    }

    public function getTradeRoute2($id)
    {
        return $this->conn->select('*')->from('route')->where('id = :id', ['id' => $id])->get();
    }

    public function getTradeRouteUid($id)
    {
        $result = $this->conn->select('`uid`')->from('route')->where('id = :id', ['id' => $id])->first();
        return $result['uid'];
    }

    public function editTradeRoute($id, $column, $value, $mode)
    {
        $data = [$column => ($mode ? $value : "{$column} + {$value}")];
        $this->conn->upgrade('route', $data, 'id = :id', ['id' => $id]);
    }

    public function deleteTradeRoute($id)
    {
        $this->conn->delete('route', 'id = :id', ['id' => $id]);
    }

    public function getHeroData($userID)
    {
        return $this->conn->select()->from('hero')->where('uid = :uid', ['uid' => $userID])->first();
    }

    public function getHeroData2($userID)
    {
        return $this->conn->select('heroid')
            ->from('hero')
            ->where('dead = 0 AND uid = :uid', ['uid' => $userID])
            ->limit(0, 1)
            ->first();
    }

    public function getHeroInVillid($userID, $mode)
    {
        $name = '';
        $villages = $this->conn->select('`wref`, `name`')
            ->from('vdata')
            ->where('owner = :owner', ['owner' => $userID])
            ->orderByDesc('owner')
            ->get();

        foreach ($villages as $village) {
            $unit = $this->conn->select('`hero`')
                ->from('units')
                ->where('vref = :vref', ['vref' => $village['wref']])
                ->orderByDesc('vref')
                ->first();

            if ($unit['hero'] == 1) {
                $name = $mode ? $village['name'] : $village['wref'];
            }

        }
        return $name;
    }

}
