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

    public function myregister($username, $password, $email, $act, $tribe, $locate)
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

        if ($this->conn->insert('users', $data)) {
            return $this->conn->getLastInsertId();
        } else {
            return false;
        }
    }

    public function modifyPoints($aid, $points, $amt)
    {
        return $this->conn->from('users')->set($points, $points + $amt)->where('id = :aid', [':aid' => $aid])->update();
    }

    public function modifyPointsAlly($aid, $points, $amt)
    {
        return $this->conn->from('alidata')->set($points, $points + $amt)->where('id = :aid', [':aid' => $aid])->update();
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

        if ($this->conn->insert('activate', $data)) {
            return $this->conn->getLastInsertId();
        } else {
            return false;
        }
    }

    public function unreg($username)
    {
        $this->conn->delete('activate', 'username = :username', [':username' => $username]);
    }

    public function deleteReinf($id)
    {
        $this->conn->delete('enforcement', 'id = :id', [':id' => $id]);
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

    public function checkExist($ref, $mode)
    {
        $column = $mode ? 'email' : 'username';
        $result = $this->conn->select($column)->from('users')->where("$column = :ref", [':ref' => $ref])->limit(1)->first();
        return !empty($result) ? true : false;
    }

    public function checkExist_activate($ref, $mode)
    {
        $column = $mode ? 'email' : 'username';
        $result = $this->conn->select($column)->from('activate')->where("$column = :ref", [':ref' => $ref])->limit(1)->first();
        return !empty($result) ? true : false;
    }

    public function updateUserField($ref, $field, $value, $mode)
    {
        $condition = '';
        if ($mode == 0) {
            $condition = "username = :ref";
        } elseif ($mode == 1) {
            $condition = "id = :ref";
        } elseif ($mode == 2) {
            $condition = "id = :ref";
            $value = "$field + $value";
        } elseif ($mode == 3) {
            $condition = "id = :ref";
            $value = "$field - $value";
        }
        $this->conn->upgrade('users', [$field => $value], $condition, [':value' => $value, ':ref' => $ref]);
    }

    public function getSit($userID)
    {
        return $this->conn->select('*')->from('users_setting')->where('id = ?', [$userID])->first();
    }

    public function getSitee1($userID)
    {
        return $this->conn->select('`id`,`username`,`sit1`')->from('users')->where("sit1 = :uid", [':uid' => $userID])->get();
    }

    public function getSitee2($userID)
    {
        return $this->conn->select('`id`,`username`,`sit2`')->from('users')->where("sit2 = :uid", [':uid' => $userID])->get();
    }

    public function removeMeSit($userID, $userID2)
    {
        $this->conn->set('sit1', 0)->from('users')->where('id = :uid AND sit1 = :uid2', [':uid' => $userID, ':uid2' => $userID2])->update();
        $this->conn->set('sit2', 0)->from('users')->where('id = :uid AND sit2 = :uid2', [':uid' => $userID, ':uid2' => $userID2])->update();
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

    public function setSitter($ref, $field, $value)
    {
        $this->conn->upgrade('users', [$field => $value], 'id = ?', [$ref]);
    }

    public function sitSetting($sitSet, $set, $val, $userID)
    {
        $field = "sitter{$sitSet}_set_{$set}";
        $this->conn->upgrade('users_setting', [$field => $val], 'id = ?', [$userID]);
    }

    public function whoissitter($userID)
    {
        return $return['whosit_sit'] = $_SESSION['whois_sit'];
    }

    public function getActivateField($ref, $field, $mode)
    {
        $condition = $mode ? "username = :ref" : "id = :ref";
        $result = $this->conn->select($field)->from('activate')->where($condition, [':ref' => $ref])->first();
        return $result[$field];
    }

    public function login($username, $password)
    {
        $result = $this->conn->select('password')->from('users')->where('username = :username', [':username' => $username])->first();

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
        $result = $this->conn->select('sit1, sit2')->from('users')->where('username = :username AND access != 0', [':username' => $username])->get();

        if ($result && count($result) > 0) {
            $pw_sit1 = $this->conn->select('password')->from('users')->where('id = :id AND access != 0', [':sit1' => $result['sit1']])->get();
        }
        if ($result['sit2'] != 0) {
            $pw_sit2 = $this->conn->select('password')->from('users')->where('id = :id AND access != 0', [':sit1' => $result['sit2']])->get();
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
            $this->conn->delete('deleting', 'uid = :uid', [':uid' => $userID]);
        }
    }

    public function isDeleting($userID)
    {
        $result = $this->conn->select('timestamp')->from('deleting')->where('uid = :uid', [':uid' => $userID])->first();
        return $result['timestamp'];
    }

    public function modifyGold($userID, $amount, $mode)
    {
        if (!$mode) {
            // Decrement gold
            $this->conn->from('users')->set('gold', "gold - $amount")->where('id = :userid', [':userid' => $userID])->update();
            // Increment usedgold
            $this->conn->from('users')->set('usedgold', "usedgold + $amount")->where('id = :userid', [':userid' => $userID])->update();
        } else {
            // Increment gold
            $this->conn->from('users')->set('gold', "gold + $amount")->where('id = :userid', [':userid' => $userID])->update();
            // Increment Addgold
            $this->conn->from('users')->set('Addgold', "Addgold + $amount")->where('id = :userid', [':userid' => $userID])->update();
        }
        $this->conn->insert('gold_fin_log', [
            'wid' => $userID,
            'log' => "$amount GOLD ADDED FROM " . $_SERVER['HTTP_REFERER'] ?? ''
        ]);
    }

    public function getGoldFinLog()
    {
        return $this->conn->select('*')->from('gold_fin_log')->get();
    }

    public function instantCompleteBdataResearch($worlID, $username)
    {
        $bdata = $this->conn->set('timestamp', 1)
            ->where('wid = :wid AND type != 25 AND type != 26', [':wid' => $worlID])
            ->from('bdata')
            ->update();
        $research = $this->conn->set('timestamp', 1)
            ->from('research')
            ->where('vref = :vref', [':vref' => $worlID])
            ->update();

        if ($bdata || $research) {
            $this->conn->set('gold', 'gold - 2')
                ->set('usedgold', 'usedgold + 2')
                ->from('users')
                ->where("username = :username", [':username' => $username])
                ->update();

            $this->conn->insert('gold_fin_log', [
                'wid' => $worlID,
                'log' => 'Finish construction and research with gold'
            ]);
            return true;
        } else {
            $this->conn->insert('gold_fin_log', [
                'wid' => $worlID,
                'log' => 'Failed construction and research with gold'
            ]);
            return false;
        }
    }

    public function getUsersList($list)
    {
        $where = ' WHERE TRUE ';
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

    public function modifyUser($ref, $column, $value, $mode = 0)
    {
        $condition = !$mode ? 'id = :ref' : 'username = :ref';
        $this->conn->upgrade('users', [$column => $value], $condition, [':ref' => $ref]);
    }

    public function getUserWithEmail($email)
    {
        return $this->conn->select('`id`,`username`')->from('users')->where('email = :email', [':email' => $email])->limit(1)->first();
    }

    public function activeModify($username, $mode)
    {
        if (!$mode) {
            $this->conn->insert('active', ['username' => $username, 'timestamp' => time()]);
        } else {
            $this->conn->delete('active', "username = {$username}");
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
        $this->conn->set('timestamp', $time)->from('users')->where('username = :username', [':username' => $username])->update();
    }

    public function checkSitter($username)
    {
        $result = $this->conn->select('sitter')
            ->from('online')
            ->where('name = :username', [':username' => $username])
            ->first();

        return $result ? $result['sitter'] : null;
    }

    public function canConquerOasis($vref, $wref)
    {
        $AttackerFields = $this->getResourceLevel($vref);
        for ($i = 19; $i <= 38; $i++) {
            if ($AttackerFields['f' . $i . 't'] == 37) {
                $HeroMansionLevel = $AttackerFields['f' . $i];
            }
        }
        if ($this->VillageOasisCount($vref) < floor(($HeroMansionLevel - 5) / 5)) {
            $OasisInfo = $this->getOasisInfo($wref);
            $troopcount = $this->countOasisTroops($wref);
            if ($OasisInfo['conqured'] == 0 || $OasisInfo['conqured'] != 0 && $OasisInfo['loyalty'] < 99 / min(3, (4 - $this->VillageOasisCount($OasisInfo['conqured']))) && $troopcount == 0) {
                $CoordsVillage = $this->getCoor($vref);
                $CoordsOasis = $this->getCoor($wref);
                if (abs($CoordsOasis['x'] - $CoordsVillage['x']) <= 3 && abs($CoordsOasis['y'] - $CoordsVillage['y']) <= 3) {
                    return True;
                } else {
                    return False;
                }
            } else {
                return False;
            }
        } else {
            return False;
        }
    }

    public function getResourceLevel($vid)
    {
        return $this->conn
            ->select('*')
            ->from('fdata')
            ->where('vref = :vref', [':vref' => $vid])
            ->get();
    }

    public function VillageOasisCount($vref)
    {
        $result = $this->conn->count('odata')->where('conqured = :conqured', [':conqured' => $vref]);
        return $result[0];
    }

    public function getOasisInfo($worlid)
    {
        return $this->conn
            ->select('`conqured`,`loyalty`')
            ->from('odata')
            ->where('`wref` = :wref', [':wref' => $worlid])
            ->limit(1)
            ->first();
    }

    public function getCoor($wref)
    {
        return $this->conn
            ->select('x, y')
            ->from('wdata')
            ->where('id = :id', [':id' => $wref])
            ->limit(1)
            ->first();
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

        $this->conn->from('odata')->values($data)->where('wref = :wref', [':wref' => $wref])->update();
    }

    public function getVillage($vid)
    {
        return $this->conn
            ->select()
            ->from('vdata')
            ->where('wref = :vid', [':vid' => $vid])
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

            $this->conn->from('odata')
                ->set('loyalty', 'GREATEST(loyalty-:loyaltyAmendment,0)', true)
                ->where('wref = :wref', [':loyaltyAmendment' => $loyaltyAmendment, ':wref' => $wref])
                ->update();
        }
        return false;
    }

    public function isVillageOases($wref)
    {
        $result = $this->conn
            ->select('oasistype')
            ->from('wdata')
            ->where('id = :id', [':id' => $wref])
            ->limit(1)
            ->first();
        return $result['oasistype'];
    }

    public function oasesUpdateLastFarm($wref)
    {
        $this->conn->upgrade('odata', ['lastfarmed' => time()], 'wref = :wref', [':wref' => $wref]);
    }

    public function oasesUpdateLastTrain($wref)
    {
        $this->conn->upgrade('odata', ['lasttrain' => time()], 'wref = :wref', [':wref' => $wref]);
    }

    public function checkactiveSession($username, $sessid)
    {
        $user = $this->getUser($username);
        $sessidarray = explode("+", $user['sessid']);
        return in_array($sessid, $sessidarray) ? true : false;
    }

    public function getUser($ref, $mode = 0)
    {
        $condition = $mode ? "username = :ref" : "id = :ref";
        $result = $this->conn->select('*')->from('users')->where($condition, [':ref' => $ref])->get();
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
        $this->conn->upgrade('users', $data, 'id = :uid', [':uid' => $userID]);
    }

    public function updateOnline($mode, $name = "", $sit = 0)
    {
        if ($mode == 'login') {
            $this->conn->insertIgnore('online', ['name' => $name, 'time' => time(), 'sitter' => $sit]);
        } else {
            $this->conn->delete('online', 'name = :name', [':name' => Session::get('username')]);
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
                $order = "ORDER BY y DESC,x DESC";
                $mmm = rand(-1, -20);
                $x_y = "AND x < -4 AND y < $mmm";
                break;
            case 2: // (+/-) SE
                $x_a = ($world_max - ($world_max * 2));
                $x_b = 0;
                $y_a = 0;
                $y_b = $world_max;
                $order = "ORDER BY y ASC,x DESC";
                $mmm = rand(1, 20);
                $x_y = "AND x < -4 AND y > $mmm";
                break;
            case 3: // (+/+) NE
                $x_a = 0;
                $x_b = $world_max;
                $y_a = 0;
                $y_b = $world_max;
                $order = "ORDER BY y,x ASC";
                $mmm = rand(1, 20);
                $x_y = "AND x > 4 AND y > $mmm";
                break;
            case 4: // (-/+) NW
                $x_a = 0;
                $x_b = $world_max;
                $y_a = ($world_max - ($world_max * 2));
                $y_b = 0;
                $order = "ORDER BY y DESC, x ASC";
                $mmm = rand(-1, -20);
                $x_y = "AND x > 4 AND y < $mmm";
                break;
        }

        $q = "SELECT id FROM wdata WHERE fieldtype = 3 AND occupied = 0 $x_y AND (x BETWEEN $x_a AND $x_b) AND (y BETWEEN $y_a AND $y_b) AND (SQRT(POW(x, 2) + POW(y, 2)) > $nareadis) $order LIMIT 20";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['id'] ?? null;
    }

    public function setFieldTaken($id)
    {
        $this->conn->from('wdata')->set('occupied', 1)->where('id = :id', [':id' => $id])->update();
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
        return $this->conn->insert('vdata', $data);
    }

    public function getVillagesID($userID)
    {
        $results = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('owner = :owner', [':owner' => $userID])
            ->get();

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
        return $this->conn->insert('fdata', array_merge(['vref' => $vid], $fields));
    }

    public function populateOasis()
    {
        $rows = $this->conn->select('id')->from('wdata')->where('oasistype != 0')->get();
        foreach ($rows as $row) {
            $this->addUnits($row['id']);
        }
    }

    public function addUnits($vid)
    {
        return $this->conn->insert('units', ['vref' => $vid]);
    }

    /**
     * retrieve type of village via ID
     * References: Village ID
     */
    public function getVillageOasis($list, $limit, $order)
    {
        $wref = $this->getVilWref($order['x'], $order['y']);
        $where = " WHERE TRUE and conqured = :wref";
        $params = [':wref' => $wref];

        foreach ($list as $key => $value) {
            if ($key !== 'extra') {
                $where .= " AND $key = :$key";
                $params[":$key"] = $value;
            }
        }
        $where .= " AND {$list['extra']} ";

        $limit = isset($limit) ? " LIMIT $limit " : "";

        $orderby = "";
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
//        $q = '';
//        if ($order['by'] == 'distance') {
//            $q = "SELECT *,(ROUND(SQRT(POW(LEAST(ABS(" . $order['x'] . " - wdata.x), ABS(" . $order['max'] . " - ABS(" . $order['x'] . " - wdata.x))), 2) + POW(LEAST(ABS(" . $order['y'] . " - wdata.y), ABS(" . $order['max'] . " - ABS(" . $order['y'] . " - wdata.y))), 2)),3)) AS distance FROM ";
//        } else {
//            $q = "SELECT * FROM ";
//        }
//        $q .= "odata LEFT JOIN wdata ON wdata.id=odata.wref " . $where . $orderby . $limit;
    }

    public function getVillageType($wref)
    {
        $result = $this->conn
            ->select('id, fieldtype')
            ->from('wdata')
            ->where('id = :wref', [':wref' => $wref])
            ->first();

        return $result['fieldtype'];
    }

    public function getWref($x, $y)
    {
        $result = $this->conn
            ->select('id')
            ->from('wdata')
            ->where('x = :x AND y = :y', [':x' => $x, ':y' => $y])
            ->first();
        return $result['id'] ?? null;
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

    public function checkVilExist($wref)
    {
        $result = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('wref = :wref', [':wref' => $wref])
            ->limit(1)
            ->first();

        return !empty($result);
    }

    public function getVillageState($wref)
    {
        $result = $this->conn->select('oasistype, occupied')->from('wdata')->where('id = :wref', [':wref' => $wref])->first();
        return $result['occupied'] != 0 || $result['oasistype'] != 0;
    }

    public function getVillageStateForSettle($wref)
    {
        $result = $this->conn
            ->select('`oasistype`,`occupied`,`fieldtype`')
            ->from('wdata')
            ->where('id = :id', [':id' => $wref])
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
            ->where('owner = :owner', [':owner' => $userID])
            ->orderByDesc('pop')
            ->get();
    }

    public function getProfileMedal($userID)
    {
        return $this->conn
            ->select('id, categorie, plaats, week, img, points')
            ->from('medal')
            ->where('userid = :uid', [':uid' => $userID])
            ->orderByDesc('id')
            ->get();
    }

    public function getProfileMedalAlly($userID)
    {
        return $this->conn
            ->select('id, categorie, plaats, week, img, points')
            ->from('allimedal')
            ->where('allyid = :allyid', [':allyid' => $userID])
            ->orderByDesc('id')
            ->get();
    }

    public function getVillageID($userID)
    {
        $result = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('owner = :owner', [':owner' => $userID])
            ->first();
        return $result['wref'];
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
        $q = 'SELECT ';
        if ($order['by'] == 'distance') {
            $q .= " *,(ROUND(SQRT(POW(LEAST(ABS(" . $order['x'] . " - x), ABS(" . $order['max'] . " - ABS(" . $order['x'] . " - x))), 2) + POW(LEAST(ABS(" . $order['y'] . " - y), ABS(" . $order['max'] . " - ABS(" . $order['y'] . " - y))), 2)),3)) AS distance FROM ";
        } else {
            $q .= " * FROM ";
        }
        $q .= "wdata " . $where . $orderby . $limit;
        return $this->query_return($q);
    }

    public function getVillagesListCount($list)
    {
        $where = ' WHERE TRUE ';
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
            ->where('wref = :wref', [':wref' => $vid])
            ->limit(1)
            ->first();
    }

    public function getAInfo($id)
    {
        return $this->conn
            ->select('`x`,`y`')
            ->from('wdata')
            ->where('id = :id', [':id' => $id])
            ->limit(1)
            ->first();
    }

    public function getOasisField($ref, $field)
    {
        $result = $this->conn
            ->select($field)
            ->from('odata')
            ->where('wref = :wref', [':wref' => $ref])
            ->limit(1)
            ->first();
        return $result[$field];
    }

    public function setVillageField($ref, $field, $value)
    {
        if ((stripos($field, 'name') !== false) && ($value == '')) {
            return false;
        }
        $this->conn->upgrade('vdata', [$field => $value], 'wref = :wref', [':wref' => $ref]);
    }

    public function setVillageLevel($ref, $field, $value)
    {
        $this->conn->upgrade('fdata', [$field => $value], 'vref = :vref', [':vref' => $ref]);
    }

    public function removeTribeSpecificFields($vref)
    {
        $fields = $this->getResourceLevel($vref);
        $tribeSpecificArray = array(31, 32, 33, 35, 36, 41);
        for ($i = 19; $i <= 40; $i++) {
            if (in_array($fields['f' . $i . 't'], $tribeSpecificArray)) {
                $q = "UPDATE fdata set " . ('f' . $i) . " = '0', " . ('f' . $i . 't') . " = '0' WHERE vref = " . $vref;

            }
        }
        $q = 'UPDATE units SET u199=0 WHERE `vref`=' . $vref;

        $q = 'DELETE FROM trapped WHERE `vref`=' . $vref;

        $q = 'DELETE FROM training WHERE `vref`=' . $vref;

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
        $this->conn->delete('admin_log', 'id = :id', [':id' => $id]);
    }

    public function checkForum($id)
    {
        return $this->conn
            ->select('id')
            ->from('forum_cat')
            ->where('alliance = :id', [':id' => $id])
            ->get();
    }

    public function countCat($id)
    {
        $result = $this->conn
            ->select('COUNT(id)')
            ->from('forum_topic')
            ->where('cat = :cat', [':cat' => $id])
            ->get();
        return $result[0];
    }

    public function lastTopic($id)
    {
        return $this->conn
            ->select('`id`')
            ->from('forum_topic')
            ->where('cat = :cat', [':cat' => $id])
            ->orderByDesc('post_date')
            ->get();
    }

    public function check_forumRules($id)
    {
        $q = "SELECT * FROM fpost_rules WHERE forum_id = $id";
        $z =
        $row = mysql_fetch_assoc($z);

        $ids = explode(',', $row['players_id']);
        foreach ($ids as $pid) {
            if ($pid == $session->uid) return false;
        }
        $idn = explode(',', $row['players_name']);
        foreach ($idn as $pid) {
            if ($pid == $_SESSION['username']) return false;
        }

        $aid = $session->alliance;
        $ids = explode(',', $row['ally_id']);
        foreach ($ids as $pid) {
            if ($pid == $aid) return false;
        }
        $q = "SELECT `tag` FROM alidata WHERE id = $aid";
        $z =
        $rows = mysql_fetch_assoc($z);

        $idn = explode(',', $row['ally_tag']);
        foreach ($idn as $pid) {
            if ($pid == $rows['tag']) return false;
        }

        return true;
    }

    public function CheckLastTopic($id)
    {
        $q = "SELECT id from forum_topic where cat = '$id'";

        if (mysql_num_rows($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkLastPost($id)
    {
        return $this->conn
            ->select('id')
            ->from('forum_post')
            ->where('topic = :topic', [':topic' => $id])
            ->get();
    }

    public function LastPost($id)
    {
        return $this->conn
            ->select('`date`,`owner`')
            ->from('forum_post')
            ->where('topic = :topic', [':topic' => $id])
            ->get();
    }

    public function countTopic($id)
    {
        $postsCount = $this->conn
            ->count('forum_post')
            ->where('owner = :owner', [':owner' => $id]);

        $topicsCount = $this->conn
            ->count('forum_topic')
            ->where('owner = :owner', [':owner' => $id]);

        return $postsCount + $topicsCount;
    }

    public function CountPost($id)
    {
        $q = "SELECT count(id) FROM forum_post where topic = '$id'";

        $row = mysql_fetch_row($result);
        return $row[0];
    }

    public function forumCat($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_cat')
            ->where('alliance = :alliance', [':alliance' => $id])
            ->orderByDesc('id')
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

    public function ForumCatName($id)
    {
        $q = "SELECT forum_name from forum_cat where id = $id";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['forum_name'];
    }

    public function CheckCatTopic($id)
    {
        $q = "SELECT id from forum_topic where cat = '$id'";

        if (mysql_num_rows($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function CheckResultEdit($alli)
    {
        $q = "SELECT id from forum_edit where alliance = '$alli'";

        if (mysql_num_rows($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function CheckCloseTopic($id)
    {
        $q = "SELECT close from forum_topic where id = '$id'";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['close'];
    }

    public function CheckEditRes($alli)
    {
        $q = "SELECT result from forum_edit where alliance = '$alli'";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['result'];
    }

    public function CreatResultEdit($alli, $result)
    {
        $q = "INSERT into forum_edit values (0,'$alli','$result')";

        return mysql_insert_id($this->connection);
    }

    public function UpdateResultEdit($alli, $result)
    {
        $date = time();
        $q = "UPDATE forum_edit set result = '$result' where alliance = '$alli'";

    }

    public function UpdateEditTopic($id, $title, $cat)
    {
        $q = "UPDATE forum_topic set title = '$title', cat = '$cat' where id = $id";

    }

    public function UpdateEditForum($id, $name, $des)
    {
        $q = "UPDATE forum_cat set forum_name = '$name', forum_des = '$des' where id = $id";

    }

    public function StickTopic($id, $mode)
    {
        $q = "UPDATE forum_topic set stick = '$mode' where id = '$id'";

    }

    public function forumCatTopic($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where('cat = :cat AND stick = ""', [':cat' => $id])
            ->orderByDesc('post_date')
            ->get();
    }

    public function forumCatTopicStick($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where('cat = :cat AND stick = 1', [':cat' => $id])
            ->orderByDesc('post_date')
            ->get();
    }

    public function showTopic($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_topic')
            ->where('id = :id', [':id' => $id])
            ->get();
    }

    public function showPost($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_post')
            ->where('topic = :topic', [':topic' => $id])
            ->get();
    }

    public function showPostEdit($id)
    {
        return $this->conn
            ->select('*')
            ->from('forum_post')
            ->where('id = :id', [':id' => $id])
            ->get();
    }

    public function CreatForum($owner, $alli, $name, $des, $area)
    {
        $q = "INSERT into forum_cat values (0,'$owner','$alli','$name','$des','$area')";

        return mysql_insert_id($this->connection);
    }

    public function CreatTopic($title, $post, $cat, $owner, $alli, $ends)
    {
        $date = time();
        $q = "INSERT into forum_topic values (0,'$title','$post','$date','$date','$cat','$owner','$alli','$ends','','')";

        return mysql_insert_id($this->connection);
    }

    public function CreatPost($post, $tids, $owner)
    {
        $date = time();
        $q = "INSERT into forum_post values (0,'$post','$tids','$owner','$date')";

        return mysql_insert_id($this->connection);
    }

    public function UpdatePostDate($id)
    {
        $date = time();
        $q = "UPDATE forum_topic set post_date = '$date' where id = $id";

    }

    public function EditUpdateTopic($id, $post)
    {
        $q = "UPDATE forum_topic set post = '$post' where id = $id";

    }

    public function EditUpdatePost($id, $post)
    {
        $q = "UPDATE forum_post set post = '$post' where id = $id";

    }

    public function LockTopic($id, $mode)
    {
        $q = "UPDATE forum_topic set close = '$mode' where id = '$id'";

    }

    public function DeleteCat($id)
    {
        $qs = "DELETE from forum_cat where id = '$id'";
        $q = "DELETE from forum_topic where cat = '$id'";
    }

    public function DeleteTopic($id)
    {
        $qs = "DELETE from forum_topic where id = '$id'";
        //  $q = "DELETE from forum_post where topic = '$id'";//
        return mysql_query($qs, $this->connection); //
        // mysql_query($q,$this->connection);
    }

    public function DeletePost($id)
    {
        $q = "DELETE from forum_post where id = '$id'";

    }

    public function getAllianceName($id)
    {
        if (!$id) return false;
        $q = "SELECT tag from alidata where id = $id";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['tag'];
    }

    public function getAlliancePermission($ref, $field, $mode)
    {
        if (!$mode) {
            $q = "SELECT $field FROM ali_permission where uid = '$ref'";
        } else {
            $q = "SELECT $field FROM ali_permission where username = '$ref'";
        }

        $dbarray = mysql_fetch_array($result);
        return $dbarray[$field];
    }

    public function ChangePos($id, $mode)
    { //??S-H=a-d=o-W??//
        $q = "SELECT `forum_area` from forum_cat where id = '$id'";

        $dbarray = mysql_fetch_assoc($result);
        if ($mode == '-1') {
            $q = "SELECT `id` from forum_cat WHERE forum_area = '" . $dbarray['forum_area'] . "' AND id < '$id' ORDER BY id DESC";
            $result2 =
            $dbarray2 = mysql_fetch_assoc($result2);
            if ($dbarray2) {
                $q = "UPDATE forum_cat set id = 0 where id = '" . $dbarray2['id'] . "'";

                $q = "UPDATE forum_cat set id = -1 where id = '" . $id . "'";

                $q = "UPDATE forum_cat set id = '" . $id . "' where id = '0'";

                $q = "UPDATE forum_cat set id = '" . $dbarray2['id'] . "' where id = '-1'";

            }
        } elseif ($mode == 1) {
            $q = "SELECT * from forum_cat where id > '$id' AND forum_area = '" . $dbarray['forum_area'] . "' LIMIT 0,1";
            $result2 =
            $dbarray2 = mysql_fetch_assoc($result2);
            if ($dbarray2) {
                $q = "UPDATE forum_cat set id = 0 where id = '" . $dbarray2['id'] . "'";

                $q = "UPDATE forum_cat set id = -1 where id = '" . $id . "'";

                $q = "UPDATE forum_cat set id = '" . $id . "' where id = '0'";

                $q = "UPDATE forum_cat set id = '" . $dbarray2['id'] . "' where id = '-1'";

            }
        }
    }

    public function ForumCatAlliance($id)
    {
        $q = "SELECT `alliance` from forum_cat where id = $id";

        $dbarray = mysql_fetch_assoc($result);
        return $dbarray['alliance'];
    }

    public function CreatPoll($id, $name, $p1_name, $p2_name, $p3_name, $p4_name)
    {
        $q = "INSERT into forum_poll values ('$id','$name','0','0','0','0','$p1_name','$p2_name','$p3_name','$p4_name','')";

        return mysql_insert_id($this->connection);
    }

    public function CreatForum_rules($aid, $id, $users_id, $users_name, $alli_id, $alli_name)
    {
        $q = "INSERT into fpost_rules values ('$aid','$id','$users_id','$users_name', '$alli_id','$alli_name')";

        return mysql_insert_id($this->connection);
    }

    public function setAlliName($aid, $name, $tag)
    {
        if (!$aid) return false;
        $q = "UPDATE alidata set name = '$name', tag = '$tag' where id = $aid";

    }

    public function isAllianceOwner($id)
    {
        if (!$id) return false;
        $q = "SELECT id from alidata where leader = '$id'";

        if (mysql_num_rows($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function aExist($ref, $type)
    {
        $q = "SELECT $type FROM alidata where $type = '$ref'";

        if (mysql_num_rows($result)) {
            return true;
        } else {
            return false;
        }
    }

    public function createAlliance($tag, $name, $userID, $max)
    {
        $q = "INSERT into alidata values (0,'$name','$tag',$userID,0,0,0,'','',$max,'','','','','','','','')";

        return mysql_insert_id($this->connection);
    }

    /**
     * insert an alliance new
     */
    public function insertAlliNotice($aid, $notice)
    {
        $time = time();
        $q = "INSERT into ali_log values (0,'$aid','$notice',$time)";

        return mysql_insert_id($this->connection);
    }

    /**
     * delete alliance if empty
     */
    public function deleteAlliance($aid)
    {
        $result = mysql_query("SELECT id FROM users where alliance = $aid");
        $num_rows = mysql_num_rows($result);
        if ($num_rows == 0) {
            $q = "DELETE FROM alidata WHERE id = $aid";
        }

        return mysql_insert_id($this->connection);
    }

    /**
     * read all alliance news
     */
    public function readAlliNotice($aid)
    {
        return $this->conn
            ->select('*')
            ->from('ali_log')
            ->where('aid = :aid', [':aid' => $aid])
            ->orderByDesc('date')
            ->get();
    }

    /**
     * create alliance permissions
     * References: ID, notice, description
     */
    public function createAlliPermissions($userID, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8)
    {

        $q = "INSERT into ali_permission values(0,'$userID','$aid','$rank','$opt1','$opt2','$opt3','$opt4','$opt5','$opt6','$opt7','$opt8')";

        return mysql_insert_id($this->connection);
    }

    /**
     * update alliance permissions
     */
    public function deleteAlliPermissions($userID)
    {
        $q = "DELETE from ali_permission where uid = '$userID'";

    }

    /**
     * update alliance permissions
     */
    public function updateAlliPermissions($userID, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8 = 0)
    {
        $q = "UPDATE ali_permission SET rank = '$rank', opt1 = '$opt1', opt2 = '$opt2', opt3 = '$opt3', opt4 = '$opt4', opt5 = '$opt5', opt6 = '$opt6', opt7 = '$opt7', opt8 = '$opt8' where uid = $userID && alliance =$aid";

    }

    /**
     * read alliance permissions
     * References: ID, notice, description
     */
    public function getAlliPermissions($userID, $aid)
    {
        $q = "SELECT * FROM ali_permission where uid = $userID && alliance = $aid";

        return mysql_fetch_assoc($result);
    }

    /**
     * update an alliance description and notice
     * References: ID, notice, description
     */
    public function submitAlliProfile($aid, $notice, $desc)
    {
        if (!$aid) return false;
        $q = "UPDATE alidata SET `notice` = '$notice', `desc` = '$desc' where id = $aid";

    }

    public function diplomacyInviteAdd($alli1, $alli2, $type)
    {
        $q = "INSERT INTO diplomacy (alli1,alli2,type,accepted) VALUES ($alli1,$alli2," . (int)intval($type) . ",0)";

    }

    public function diplomacyOwnOffers($session_alliance)
    {
        return $this->conn
            ->select()
            ->from('diplomacy')
            ->where('alli1 = :session_alliance AND accepted = 0', [':session_alliance' => $session_alliance])
            ->get();
    }

    public function getAllianceID($name)
    {
        $q = "SELECT id FROM alidata WHERE tag ='" . $this->RemoveXSS($name) . "'";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['id'];
    }

    public function RemoveXSS($val)
    {
        return htmlspecialchars($val, ENT_QUOTES);
    }

    public function diplomacyCancelOffer($id)
    {
        $q = "DELETE FROM diplomacy WHERE id = $id";

    }

    public function diplomacyInviteAccept($id, $session_alliance)
    {
        $q = "UPDATE diplomacy SET accepted = 1 WHERE id = $id AND alli2 = $session_alliance";

    }

    public function diplomacyInviteDenied($id, $session_alliance)
    {
        $q = "DELETE FROM diplomacy WHERE id = $id AND alli2 = $session_alliance";

    }

    public function diplomacyInviteCheck($session_alliance)
    {
        $q = "SELECT * FROM diplomacy WHERE alli2 = $session_alliance AND accepted = 0";
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli2 = :alliance AND accepted = 0', [':alliance' => $session_alliance])
            ->get();
    }

    public function diplomacyExistingRelationships($session_alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli2 = :alliance AND accepted = 1', [':alliance' => $session_alliance])
            ->get();
    }

    public function diplomacyExistingRelationships2($session_alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli1 = :alliance AND accepted = 1', [':alliance' => $session_alliance])
            ->get();
    }

    public function diplomacyCancelExistingRelationship($id, $session_alliance)
    {
        $q = "DELETE FROM diplomacy WHERE id = $id AND alli2 = $session_alliance";

    }

    public function getUserAlliance($id)
    {
        if (!$id) return false;
        $q = "SELECT alidata.tag from users join alidata where users.alliance = alidata.id and users.id = $id";

        $dbarray = mysql_fetch_array($result);
        if ($dbarray['tag'] == "") {
            return "-";
        } else {
            return $dbarray['tag'];
        }
    }

    public function modifyResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        if (!$mode) {
            $q = "UPDATE vdata set wood = wood - $wood, clay = clay - $clay, iron = iron - $iron, crop = crop - $crop where wref = $vid";
        } else {
            $q = "UPDATE vdata set wood = wood + $wood, clay = clay + $clay, iron = iron + $iron, crop = crop + $crop where wref = $vid";
        }

    }

    public function modifyProduction($vid, $woodp, $clayp, $ironp, $cropp, $upkeep)
    {
        $q = "UPDATE vdata set woodp = $woodp, clayp = $clayp, ironp = $ironp, cropp = $cropp, upkeep = $upkeep where wref = $vid";

    }

    public function modifyOasisResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        if (!$mode) {
            $q = "UPDATE odata set wood = wood - $wood, clay = clay - $clay, iron = iron - $iron, crop = crop - $crop where wref = $vid";
        } else {
            $q = "UPDATE odata set wood = wood + $wood, clay = clay + $clay, iron = iron + $iron, crop = crop + $crop where wref = $vid";
        }

    }

    public function getFieldType($vid, $field)
    {
        $q = "SELECT f" . $field . "t from fdata where vref = $vid";

        return mysql_result($result, 0);
    }

    public function getVSumField($userID, $field)
    {
        $q = "SELECT sum(" . $field . ") FROM vdata where owner = $userID";

        $row = mysql_fetch_row($result);
        return $row[0];
    }

    public function updateVillage($vid)
    {
        $time = time();
        $q = "UPDATE vdata set lastupdate = $time where wref = $vid";

    }

    public function updateOasis($vid)
    {
        $time = time();
        $q = "UPDATE odata set lastupdated = $time where wref = $vid";

    }

    public function setVillageName($vid, $name)
    {
        if ($name == '') return false;
        $q = "UPDATE vdata set name = '$name' where wref = $vid";

    }

    public function modifyPop($vid, $pop, $mode)
    {
        if (!$mode) {
            $q = "UPDATE vdata set pop = pop + $pop where wref = $vid";
        } else {
            $q = "UPDATE vdata set pop = pop - $pop where wref = $vid";
        }

    }

    public function addCP($ref, $cp)
    {
        $q = "UPDATE vdata set cp = cp + '$cp' where wref = '$ref'";

    }

    public function addCel($ref, $cel, $type)
    {
        $q = "UPDATE vdata set celebration = $cel, type= $type where wref = $ref";

    }

    public function getCel()
    {
        return $this->conn
            ->select('`wref`,`type`,`owner`')
            ->from('vdata')
            ->where('celebration < :time AND celebration != 0', [':time' => time()])
            ->get();
    }

    public function getActiveGCel($vref)
    {
        return $this->conn->select('*')
            ->from('vdata')
            ->where('vref = :vref AND celebration > :time AND type = 2', [':vref' => $vref, ':time' => time()])
            ->get();
    }

    public function clearCel($ref)
    {
        $q = "UPDATE vdata set celebration = 0, type = 0 where wref = $ref";

    }

    public function setCelCp($user, $cp)
    {
        $q = "UPDATE users set cp = cp + $cp where id = $user";

    }

    public function getInvitation($userID, $ally)
    {
        return $this->conn->select("*")
            ->from("ali_invite")
            ->where('uid = :uid AND alliance = :alliance', [':uid' => $userID, ':alliance' => $ally])
            ->get();
    }

    public function getInvitation2($userID)
    {
        return $this->conn->select("*")
            ->from("ali_invite")
            ->where('uid = :uid ', [':uid' => $userID])
            ->get();
    }

    public function getAliInvitations($aid)
    {
        return $this->conn->select("*")
            ->from("ali_invite")
            ->where('alliance = :alliance AND accept = 0', [':alliance' => $aid])
            ->get();
    }

    public function sendInvitation($userID, $alli, $sender)
    {
        $time = time();
        $q = "INSERT INTO ali_invite values (0,$userID,$alli,$sender,$time,0)";

    }

    public function removeInvitation($id)
    {
        $q = "DELETE FROM ali_invite where id = $id";

    }

    public function delMessage($id)
    {
        $q = "DELETE FROM mdata WHERE id = $id";

    }

    public function delNotice($id, $userID)
    {
        $q = "DELETE FROM ndata WHERE id = $id AND uid = $userID";

    }

    public function sendMessage($client, $owner, $topic, $message, $send, $alliance, $player, $coor, $report)
    {
        $time = time();
        $q = "INSERT INTO mdata values (0,$client,$owner,'$topic',\"$message\",0,0,$send,$time,0,0,$alliance,$player,$coor,$report)";

    }

    public function setArchived($id)
    {
        $q = "UPDATE mdata set archived = 1 where id = $id";

    }

    public function setNorm($id)
    {
        $q = "UPDATE mdata set archived = 0 where id = $id";

    }

    public function getMessage($id, $mode)
    {
        switch ($mode) {
            case 1:
                $q = "SELECT `id`,`target`,`owner`,`topic`,`message`,`viewed`,`archived`,`send`,`time`,`deltarget`,`delowner`,`alliance`,`player`,`coor`,`report` FROM mdata WHERE target = $id and send = 0 and archived = 0 ORDER BY time DESC";
                break;
            case 2:
                $q = "SELECT `id`,`target`,`owner`,`topic`,`message`,`viewed`,`archived`,`send`,`time`,`deltarget`,`delowner`,`alliance`,`player`,`coor`,`report` FROM mdata WHERE owner = $id ORDER BY time DESC";
                break;
            case 3:
                $q = "SELECT `id`,`target`,`owner`,`topic`,`message`,`viewed`,`archived`,`send`,`time`,`deltarget`,`delowner`,`alliance`,`player`,`coor`,`report` FROM mdata where id = $id";
                break;
            case 4:
                $q = "UPDATE mdata set viewed = 1 where id = $id AND target = $session->uid";
                break;
            case 5:
                $q = "UPDATE mdata set deltarget = 1 ,viewed = 1 where id = $id";
                break;
            case 6:
                $q = "SELECT `id`,`target`,`owner`,`topic`,`message`,`viewed`,`archived`,`send`,`time`,`deltarget`,`delowner`,`alliance`,`player`,`coor`,`report` FROM mdata where target = $id and send = 0 and archived = 1";
                break;
            case 7:
                $q = "UPDATE mdata set delowner = 1 where id = $id";
                break;
            case 8:
                $q = "UPDATE mdata set deltarget = 1, delowner = 1, viewed = 1 where id = $id";
                break;
            case 9:
                $q = "SELECT `id`,`target`,`owner`,`topic`,`message`,`viewed`,`archived`,`send`,`time`,`deltarget`,`delowner`,`alliance`,`player`,`coor`,`report` FROM mdata WHERE target = $id and send = 0 and archived = 0 and deltarget = 0 and viewed = 0 ORDER BY time DESC";
                break;
            case 10:
                $q = "SELECT `id`,`target`,`owner`,`topic`,`message`,`viewed`,`archived`,`send`,`time`,`deltarget`,`delowner`,`alliance`,`player`,`coor`,`report` FROM mdata WHERE owner = $id and delowner = 0 ORDER BY time DESC";
                break;
            case 11:
                $q = "SELECT `id`,`target`,`owner`,`topic`,`message`,`viewed`,`archived`,`send`,`time`,`deltarget`,`delowner`,`alliance`,`player`,`coor`,`report` FROM mdata where target = $id and send = 0 and archived = 1 and deltarget = 0";
                break;
            case 12:
                $q = "SELECT `id`,`target`,`owner`,`topic`,`message`,`viewed`,`archived`,`send`,`time`,`deltarget`,`delowner`,`alliance`,`player`,`coor`,`report` FROM mdata WHERE target = $id and send = 0 and archived = 0 and deltarget = 0 and viewed = 0 ORDER BY time DESC LIMIT 1";
                break;
        }
        if ($mode <= 3 || $mode == 6 || $mode > 8) {
            return $this->mysql_fetch_all($result);
        } else {

        }
    }

    public function unarchiveNotice($id)
    {
        $q = "UPDATE ndata set `archive` = 0 where id = $id";

    }

    public function archiveNotice($id)
    {
        $q = "update ndata set `archive` = 1 where id = $id";

    }

    public function removeNotice($id)
    {
        $this->conn->delete('ndata', 'id = :id', [':id' => $id]);
    }

    public function noticeViewed($id)
    {
        $q = "UPDATE ndata set viewed = 1 where id = $id";

    }

    public function addNotice($userID, $toWref, $ally, $type, $topic, $data, $time = 0)
    {
        if ($time == 0) {
            $time = time();
        }
        $q = "INSERT INTO ndata (id, uid, toWref, ally, topic, ntype, data, time, viewed) values (0,'$userID','$toWref','$ally','$topic',$type,'$data',$time,0)";

    }

    public function getNotice($userID)
    {
        return $this->conn->select('*')
            ->from('ndata')
            ->where('uid = :uid AND del = 0', [':uid' => $userID])
            ->orderByDesc('time')
            ->limit(99)
            ->get();
    }

    public function getNoticeReportBox($userID)
    {
        $q = "SELECT COUNT(`id`) as maxreport FROM ndata where uid = $userID ORDER BY time DESC LIMIT 200";

        $result = mysql_fetch_assoc($result);
        return $result['maxreport'];
    }

    public function addBuilding($worlID, $field, $type, $loop, $time, $master, $level)
    {
        $x = "UPDATE fdata SET f" . $field . "t=" . $type . " WHERE vref=" . $worlID;
        mysql_query($x, $this->connection);
        $q = "INSERT into bdata values (0,$worlID,$field,$type,$loop,$time,$master,$level)";

    }

    public function removeBuilding($d)
    {
        global $building;
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
                        $time = $uprequire['time'];
                        $timestamp = $time + time();
                        $q = "UPDATE bdata SET loopcon=0,level=level-1,timestamp=" . $timestamp . " WHERE id=" . $jobs[1]['id'] . "";

                    }
                } else if ($SameBuildCount == 6) {
                    if ($jobDeleted == 0) {
                        $uprequire = $building->resourceRequired($jobs[2]['field'], $jobs[2]['type'], 1);
                        $time = $uprequire['time'];
                        $timestamp = $time + time();
                        $q = "UPDATE bdata SET loopcon=0,level=level-1,timestamp=" . $timestamp . " WHERE id=" . $jobs[2]['id'] . "";

                    }
                } else if ($SameBuildCount == 7) {
                    if ($jobDeleted == 1) {
                        $uprequire = $building->resourceRequired($jobs[2]['field'], $jobs[2]['type'], 1);
                        $time = $uprequire['time'];
                        $timestamp = $time + time();
                        $q = "UPDATE bdata SET loopcon=0,level=level-1,timestamp=" . $timestamp . " WHERE id=" . $jobs[2]['id'] . "";

                    }
                }
                if ($SameBuildCount < 8) {
                    $uprequire1 = $building->resourceRequired($jobs[$jobMaster]['field'], $jobs[$jobMaster]['type'], 2);
                    $time1 = $uprequire1['time'];
                    $timestamp1 = $time1;
                    $q1 = "UPDATE bdata SET level=level-1,timestamp=" . $timestamp1 . " WHERE id=" . $jobs[$jobMaster]['id'] . "";
                    mysql_query($q1, $this->connection);
                } else {
                    $uprequire1 = $building->resourceRequired($jobs[$jobMaster]['field'], $jobs[$jobMaster]['type'], 1);
                    $time1 = $uprequire1['time'];
                    $timestamp1 = $time1;
                    $q1 = "UPDATE bdata SET level=level-1,timestamp=" . $timestamp1 . " WHERE id=" . $jobs[$jobMaster]['id'] . "";
                    mysql_query($q1, $this->connection);
                }
            } else if ($d == $jobs[floor($SameBuildCount / 3)]['id'] || $d == $jobs[floor($SameBuildCount / 2) + 1]['id']) {
                $q = "UPDATE bdata SET loopcon=0,level=level-1,timestamp=" . $jobs[floor($SameBuildCount / 3)]['timestamp'] . " WHERE master = 0 AND id > " . $d . " and (ID=" . $jobs[floor($SameBuildCount / 3)]['id'] . " OR ID=" . $jobs[floor($SameBuildCount / 2) + 1]['id'] . ")";

            }
        } else {
            if ($jobs[$jobDeleted]['field'] >= 19) {
                $x = "SELECT f" . $jobs[$jobDeleted]['field'] . " FROM fdata WHERE vref=" . $jobs[$jobDeleted]['wid'];
                $result = mysql_query($x, $this->connection);
                $fieldlevel = mysql_fetch_row($result);
                if ($fieldlevel[0] == 0) {
                    $x = "UPDATE fdata SET f" . $jobs[$jobDeleted]['field'] . "t=0 WHERE vref=" . $jobs[$jobDeleted]['wid'];
                    mysql_query($x, $this->connection);
                }
            }
            if (($jobLoopconID >= 0) && ($jobs[$jobDeleted]['loopcon'] != 1)) {
                if (($jobs[$jobLoopconID]['field'] <= 18 && $jobs[$jobDeleted]['field'] <= 18) || ($jobs[$jobLoopconID]['field'] >= 19 && $jobs[$jobDeleted]['field'] >= 19) || sizeof($jobs) < 3) {
                    $uprequire = $building->resourceRequired($jobs[$jobLoopconID]['field'], $jobs[$jobLoopconID]['type']);
                    $x = "UPDATE bdata SET loopcon=0,timestamp=" . (time() + $uprequire['time']) . " WHERE wid=" . $jobs[$jobDeleted]['wid'] . " AND loopcon=1 AND master=0";
                    mysql_query($x, $this->connection);
                }
            }
        }
        $q = "DELETE FROM bdata where id = $d";

    }

    public function addDemolition($worlID, $field)
    {
        global $building, $village;
        $q = "DELETE FROM bdata WHERE field=$field AND wid=$worlID";

        $uprequire = $building->resourceRequired($field - 1, $village->resarray['f' . $field . 't']);
        $q = "INSERT INTO demolition VALUES (" . $worlID . "," . $field . "," . ($this->getFieldLevel($worlID, $field) - 1) . "," . (time() + floor($uprequire['time'] / 2)) . ")";

    }

    public function getFieldLevel($vid, $field)
    {
        $q = "SELECT f" . $field . " from fdata where vref = $vid";

        return mysql_result($result, 0);
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
        $q = "UPDATE demolition SET timetofinish=0 WHERE vref=" . $worlID;

    }

    public function delDemolition($worlID)
    {
        $q = "DELETE FROM demolition WHERE vref=" . $worlID;

    }

    public function getJobs($worlID)
    {
        return $this->conn->select('*')
            ->from('bdata')
            ->where('wid = :wid', [':wid' => $worlID])
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
        $this->conn->upgrade('bdata', ['timestamp' => time() - 1], 'id = :id', [':id' => $bdata['id']]);
    }

    public function FinishCropLand($worlID)
    {
        $time = time() - 1;
        $q = "SELECT `id`,`timestamp` FROM bdata where wid = $worlID and type = 4 order by master,timestamp ASC";
        $result = mysql_query($q);
        $dbarray = mysql_fetch_assoc($result);
        $q = "UPDATE bdata SET timestamp = $time WHERE id = '" . $dbarray['id'] . "'";
        $this->query($q);
        $q2 = "SELECT `id` FROM bdata where wid = $worlID and loopcon = 1 and field <= 18 order by master,timestamp ASC";
        if (mysql_num_rows($q2) > 0) {
            $result2 = mysql_query($q2);
            $dbarray2 = mysql_fetch_assoc($result2);
            $wc_time = $dbarray['timestamp'];
            $q2 = "UPDATE bdata SET timestamp = timestamp - $wc_time WHERE id = '" . $dbarray2['id'] . "'";
            $this->query($q2);
        }
    }

    public function finishBuildings($worlID)
    {
        $time = time() - 1;
        $q = "SELECT id FROM bdata where wid = $worlID order by master,timestamp ASC";

        while ($row = mysql_fetch_assoc($result)) {
            $q = "UPDATE bdata SET timestamp = $time WHERE id = '" . $row['id'] . "'";
            $this->query($q);
        }
    }

    public function getMasterJobs($worlID)
    {
        return $this->conn->select('`id`')
            ->from('bdata')
            ->where('wid = :wid AND master = 1', [':wid' => $worlID])
            ->order('ORDER BY master, timestamp ASC')
            ->get();
    }

    public function getBuildingByField($worlID, $field)
    {
        return $this->conn->select('`id`')
            ->from('bdata')
            ->where('wid = :wid AND field = :field AND master = 0', [':wid' => $worlID, ':field' => $field])
            ->get();
    }

    public function getBuildingByType($worlID, $type)
    {
        return $this->conn->select('`id`')
            ->from('bdata')
            ->where('wid = :wid AND type = :type AND master = 0', [':wid' => $worlID, ':type' => $type])
            ->order('ORDER BY master, timestamp ASC')
            ->get();
    }

    public function getDorf1Building($worlID)
    {
        return $this->conn->select('`timestamp`')
            ->from('bdata')
            ->where('wid = :wid AND field < 19 AND master = 0', [':wid' => $worlID])
            ->get();
    }

    public function getDorf2Building($worlID)
    {
        return $this->conn->select('`timestamp`')
            ->from('bdata')
            ->where('wid = :wid AND field > 18 AND master = 0', [':wid' => $worlID])
            ->get();
    }

    public function updateBuildingWithMaster($id, $time, $loop)
    {
        $q = "UPDATE bdata SET master = 0, timestamp = " . $time . ",loopcon = " . $loop . " WHERE id = " . $id . "";

    }

    public function getVillageByName($name)
    {
        $name = mysql_real_escape_string($name, $this->connection);
        $q = "SELECT wref FROM vdata where name = '$name' limit 1";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['wref'];
    }

    /**
     * set accept flag on market
     * References: id
     */
    public function setMarketAcc($id)
    {
        $q = "UPDATE market set accept = 1 where id = $id";

    }

    /**
     * send resource to other village
     * Mode 0: Send
     * Mode 1: Cancel
     * References: Wood/ID, Clay, Iron, Crop, Mode
     */
    public function sendResource($wood, $clay, $iron, $crop, $merchant)
    {
        $q = "INSERT INTO send (`wood`, `clay`, `iron`, `crop`, `merchant`) values ($wood,$clay,$iron,$crop,$merchant)";

        return mysql_insert_id($this->connection);
    }

    public function sendResourceMORE($wood, $clay, $iron, $crop, $send)
    {
        $q = "INSERT INTO send (`wood`, `clay`, `iron`, `crop`, `send`) values ($wood,$clay,$iron,$crop,$send)";

        return mysql_insert_id($this->connection);
    }

    public function removeSend($ref)
    {
        $q = "DELETE FROM send WHERE id = " . $ref;

    }

    /**
     * get resources back if you delete offer
     * References: VillageRef (vref)
     * Made by: Dzoki
     */
    public function getResourcesBack($vref, $gtype, $gamt)
    {
        //Xtype (1) = wood, (2) = clay, (3) = iron, (4) = crop
        if ($gtype == 1) {
            $q = "UPDATE vdata SET `wood` = `wood` + '$gamt' WHERE wref = $vref";

        } else
            if ($gtype == 2) {
                $q = "UPDATE vdata SET `clay` = `clay` + '$gamt' WHERE wref = $vref";

            } else
                if ($gtype == 3) {
                    $q = "UPDATE vdata SET `iron` = `iron` + '$gamt' WHERE wref = $vref";

                } else
                    if ($gtype == 4) {
                        $q = "UPDATE vdata SET `crop` = `crop` + '$gamt' WHERE wref = $vref";

                    }
    }

    /**
     * get info about offered resources
     * References: VillageRef (vref)
     * Made by: Dzoki
     */
    public function getMarketField($vref, $field)
    {
        $q = "SELECT $field FROM market where vref = '$vref'";

        $dbarray = mysql_fetch_array($result);
        return $dbarray[$field];
    }

    public function removeAcceptedOffer($id)
    {
        $q = "DELETE FROM market where id = $id";

        return mysql_fetch_assoc($result);
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
            $q = "INSERT INTO market values (0,$vid,$gtype,$gamt,$wtype,$wamt,0,$time,$alliance,$merchant)";

            return mysql_insert_id($this->connection);
        } else {
            $q = "DELETE FROM market where id = $gtype and vref = $vid";

        }
    }

    /**
     * get market offer
     * References: Village, Mode
     */
    public function getMarket($vid, $mode)
    {
        if (!$mode) {
            $result = $this->conn
                ->select('*')
                ->from("market")
                ->where('vref = :vref AND accept = 0', [':vref' => $vid])
                ->orderByDesc('id')
                ->get();
        } else {
            $result = $this->conn
                ->select('*')
                ->from("market")
                ->where('vref != :vref AND alliance = :alliance OR vref != :vref AND alliance = 0 AND accept = 0', [':vref' => $vid, 'alliance' => $alliance])
                ->orderByDesc('id')
                ->get();
        }
        return $result;
    }

    public function getUserField($ref, $field, $mode)
    {
        $column = !$mode ? 'id' : 'username';
        $result = $this->conn->select($field)->from('users')->where("$column = :ref", [':ref' => $ref])->first();
        return $result[$field];
    }

    public function getVillageField($ref, $field)
    {
        $result = $this->conn
            ->select($field)
            ->from('vdata')
            ->where('wref = :wref', [':wref' => $ref])
            ->limit(1)
            ->first();
        return $result[$field];
    }

    /**
     * get market offer
     * References: ID
     */
    public function getMarketInfo($id)
    {
        $q = "SELECT `vref`,`gtype`,`wtype`,`merchant`,`wamt` FROM market where id = $id";

        return mysql_fetch_assoc($result);
    }

    public function setMovementProc($moveid)
    {
        $q = "UPDATE movement set proc = 1 where moveid = $moveid";

    }

    /**
     * retrieve used merchant
     * References: Village
     */
    public function totalMerchantUsed($vid)
    {
        //$time = time();
        $q = "SELECT sum(send.merchant) from send, movement where movement.from = $vid and send.id = movement.ref and movement.proc = 0 and sort_type = 0";

        $row = mysql_fetch_row($result);
        $q2 = "SELECT sum(send.merchant) from send, movement where movement.to = $vid and send.id = movement.ref and movement.proc = 0 and sort_type = 1";
        $result2 = mysql_query($q2, $this->connection);
        $row2 = mysql_fetch_row($result2);
        $q3 = "SELECT sum(merchant) from market where vref = $vid and accept = 0";
        $result3 = mysql_query($q3, $this->connection);
        $row3 = mysql_fetch_row($result3);
        return $row[0] + $row2[0] + $row3[0];
    }

    public function getMovementById($id)
    {
        return $this->conn->select('`starttime`,`to`,`from`')
            ->from('movement')
            ->where('moveid = :moveid', [':moveid' => $id])
            ->get();
    }

    public function cancelMovement($id, $newfrom, $newto)
    {
        $refstr = '';
        $q = "SELECT ref FROM movement WHERE moveid=$id";
        $amove = $this->query_return($q);
        if (count($amove) > 0) $mov = $amove[0];
        if ($mov['ref'] == 0) {
            $ref = $this->addAttack($newto, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, 0, 0, 0);
            $refstr = ',`ref`=' . $ref;
        }
        $q = "UPDATE movement SET `from`=" . $newfrom . ", `to`=" . $newto . ", `sort_type`=4, `endtime`=(" . (2 * time()) . "-`starttime`),`starttime`=" . time() . " " . $refstr . " WHERE moveid = " . $id;
        $this->query($q);
    }

    public function addAttack($vid, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type, $ctar1, $ctar2, $spy)
    {
        $q = "INSERT INTO attacks values (0,$vid,$t1,$t2,$t3,$t4,$t5,$t6,$t7,$t8,$t9,$t10,$t11,$type,$ctar1,$ctar2,$spy)";

        return mysql_insert_id($this->connection);
    }

    public function getAdvMovement($village)
    {
        return $this->conn->select('`moveid`')
            ->from('movement')
            ->where('movement.from = :from AND sort_type = 9', [':from' => $village])
            ->get();
    }

    public function getCompletedAdvMovement($village)
    {
        return $this->conn->select('`moveid`')
            ->from('movement')
            ->where('movement.from = :from AND sort_type = 9 AND proc = 1', [':from' => $village])
            ->get();
    }

    public function addA2b($ckey, $timestamp, $to, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type)
    {
        $q = "INSERT INTO a2b (ckey,time_check,to_vid,u1,u2,u3,u4,u5,u6,u7,u8,u9,u10,u11,type) VALUES ('$ckey', '$timestamp', '$to', '$t1', '$t2', '$t3', '$t4', '$t5', '$t6', '$t7', '$t8', '$t9', '$t10', '$t11', '$type')";

        return mysql_insert_id($this->connection);
    }

    public function getA2b($ckey, $check)
    {
        $q = "SELECT * from a2b where ckey = '" . $ckey . "' AND time_check = '" . $check . "'";

        if ($result) {
            return mysql_fetch_assoc($result);
        } else {
            return false;
        }
    }

    public function removeA2b($ckey, $check)
    {
        $q = "DELETE FROM a2b where ckey = '" . $ckey . "' AND time_check = '" . $check . "'";

        if ($result) {
            return mysql_fetch_assoc($result);
        } else {
            return false;
        }
    }

    public function addMovement($type, $from, $to, $ref, $data, $endtime)
    {
        $q = "INSERT INTO movement values (0,$type,$from,$to,$ref,'$data'," . time() . ",$endtime,0)";

    }

    public function modifyAttack($aid, $unit, $amount)
    {
        $unit = "t{$unit}";
        $result = $this->conn->select($unit)->from('attacks')->where('id = :id', [':id' => $aid])->get();

        $amount = min($result[$unit], $amount);
        $this->conn->upgrade('attacks', [$unit => "$unit - $amount"], 'id = :id', [':id' => $aid]);
    }

    public function getRanking()
    {
        return $this->conn
            ->select('id, username, alliance, ap, apall, dp, dpall, access')
            ->from('users')
            ->where('tribe <= 3 AND access < 8')
            ->get();
    }

    public function getBuildList($type, $worlID = 0)
    {
        $where = 'TRUE';
        $params = [];

        if ($type) {
            $where .= ' AND type = :type';
            $params[':type'] = $type;
        }
        if ($worlID) {
            $where .= ' AND wid = :wid';
            $params[':wid'] = $worlID;
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
            ->where('v.owner = u.id AND u.tribe <= 3 AND v.wref != "" AND u.access <= 8')
            ->get();
    }

    public function getARanking()
    {
        return $this->conn->select('id, name, tag')
            ->from('alidata')
            ->where('id != ""')
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
            ->where('alliance = :alliance', [':alliance' => $aid])
            ->orderBy('(SELECT SUM(pop) FROM vdata WHERE owner = users.id)', 'DESC')
            ->get();
    }

    public function getUnit($vid)
    {
        $q = "SELECT * FROM units where vref = " . $vid . "";

        if (!empty($result)) {
            return mysql_fetch_assoc($result);
        } else {
            return NULL;
        }
    }

    public function getHUnit($vid)
    {
        $q = "SELECT hero FROM units where vref = " . $vid . "";

        $dbarray = mysql_fetch_array($result);
        if ($dbarray['hero'] != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getHero($userID = false, $id = false, $dead = 2)
    {
        $query = $this->conn->select()->from('hero');
        if ($userID) {
            $query->where('uid = :uid', [':uid' => $userID]);
        }
        if ($id) {
            $query->andWhere('id = :id', [':id' => $id]);
        }
        if ($dead != 2) {
            $query->andWhere('dead = :dead', [':dead' => $dead]);
        }
        $query->limit(1);
        return $query->first();
    }

    public function modifyHero($userID, $id, $column, $value, $mode = 0)
    {
        $cmd = '';
        $cmd .= match ($mode) {
            0 => " $column = :value ",
            1 => " $column = $column + :value ",
            2 => " $column = $column - :value ",
            3 => " $column = $column * :value ",
            4 => " $column = $column / :value ",
        };

        $cmd .= match ($column) {
            'r0', 'r1', 'r2', 'r3', 'r4' => " ,rc = 1 ",
        };

        $params = [':value' => $value];
        $where = 'TRUE';
        if ($userID) {
            $where .= ' AND uid = :uid';
            $params[':uid'] = $userID;
        }
        if ($id) {
            $where .= ' AND heroid = :id';
            $params[':id'] = $id;
        }

        $q = "UPDATE hero SET $cmd WHERE $where";

        return $this->conn->executeQuery($q, $params);
    }

    public function clearTech($vref)
    {
        $q = "DELETE from tdata WHERE vref = $vref";

        return $this->addTech($vref);
    }

    public function addTech($vid)
    {
        return $this->conn->insert('tdata', ['vref' => $vid]);
    }

    public function clearABTech($vref)
    {
        $q = "DELETE FROM abdata WHERE vref = $vref";

        return $this->addABTech($vref);
    }

    public function addABTech($vid)
    {
        return $this->conn->insert('abdata', ['vref' => $vid]);
    }

    public function getABTech($vid)
    {
        $q = "SELECT `vref`,`a1`,`a2`,`a3`,`a4`,`a5`,`a6`,`a7`,`a8`,`a9`,`a10`,`b1`,`b2`,`b3`,`b4`,`b5`,`b6`,`b7`,`b8`,`b9`,`b10` FROM abdata where vref = $vid";

        return mysql_fetch_assoc($result);
    }

    public function addResearch($vid, $tech, $time)
    {
        $q = "INSERT into research values (0,$vid,'$tech',$time)";

    }

    public function getResearching($vid)
    {
        return $this->$this->conn->select('*')
            ->from('research')
            ->where('vref = :vref', [':vref' => $vid])
            ->get();
    }

    public function checkIfResearched($vref, $unit)
    {
        $q = "SELECT $unit FROM tdata WHERE vref = $vref";

        $dbarray = mysql_fetch_array($result);
        return $dbarray[$unit];
    }

    public function getTech($vid)
    {
        $q = "SELECT * from tdata where vref = $vid";

        return mysql_fetch_assoc($result);
    }

    public function getTraining($vid)
    {
        return $this->conn->select('`amt`,`unit`,`endat`,`commence`,`id`,`vref`,`pop`,`timestamp`,`eachtime`')
            ->from('training')
            ->where('vref = :vref'. [':vref' => $vid])
            ->orderByDesc('id')
            ->get();
    }

    public function trainUnit($vid, $unit, $amount, $pop, $each, $commence, $mode)
    {
        global $technology;

        if (!$mode) {
            $barracks = array(1, 2, 3, 11, 12, 13, 14, 21, 22, 31, 32, 33, 34, 41, 42, 43, 44);
            $greatbarracks = array(61, 62, 63, 71, 72, 73, 84, 81, 82, 91, 92, 93, 94, 101, 102, 103, 104);
            $stables = array(4, 5, 6, 15, 16, 23, 24, 25, 26, 35, 36, 45, 46);
            $greatstables = array(64, 65, 66, 75, 76, 83, 84, 85, 86, 95, 96, 105, 106);
            $workshop = array(7, 8, 17, 18, 27, 28, 37, 38, 47, 48);
            $greatworkshop = array(67, 68, 77, 78, 87, 88, 97, 98, 107, 108);
            $residence = array(9, 10, 19, 20, 29, 30, 39, 40, 49, 50);
            $trap = array(199);

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

            if ($queued[count($queued) - 1]['unit'] == $unit) {
                $endat = $each * $amount / 1000;
                $q = "UPDATE training SET amt = amt + $amount, timestamp = $timestamp,endat = endat + $endat WHERE id = " . $queued[count($queued) - 1]['id'] . "";
            } else {
                $endat = $timestamp + ($each * $amount / 1000);
                $q = "INSERT INTO training values (0,$vid,$unit,$amount,$pop,$timestamp,$each,$commence,$endat)";
            }
        } else {
            $q = "DELETE FROM training where id = $vid";
        }

    }

    public function removeZeroTrain()
    {
        $q = "DELETE FROM training where `unit` <> 0 AND amt <= 0";

    }

    public function getHeroTrain($vid)
    {
        $q = "SELECT `id`,`eachtime` from training where vref = $vid and unit = 0";

        $dbarray = mysql_fetch_array($result);
        if (empty($result)) {
            return false;
        } else {
            return $dbarray;
        }
    }

    public function trainHero($vid, $each, $endat, $mode)
    {
        if (!$mode) {
            $time = time();
            $q = "INSERT INTO training values (0, $vid, 0, 1, 6, $time, $each, $time, $endat)";
        } else {
            $q = "DELETE FROM training where id = $vid";
        }

    }

    public function updateTraining($id, $trained)
    {
        $time = time();
        $q = "UPDATE training set amt = GREATEST(amt - $trained, 0), timestamp = $time where id = $id";

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
                $result = $this->conn->select($unit)->from('units')->where('vref = :vref', [':vref' => $vref])->first();
                $amount = min($result[$unit], $amount);
                $q = "UPDATE units SET $unit = ($unit - :amt) WHERE vref = :vref";
                break;
            case 1:
                $q = "UPDATE units SET $unit = ($unit + :amt) WHERE vref = :vref";
                break;
            case 2:
                $q = "UPDATE units SET $unit = :amt WHERE vref = :vref";
                break;
        }

        return $this->conn->executeQuery($q, [':amt' => $amount, ':vref' => $vref]);
    }

    public function getFilledTrapCount($vref)
    {
        $result = 0;
        $q = "SELECT * FROM trapped WHERE `vref` = $vref";
        $trapped = $this->query_return($q);
        if (count($trapped) > 0) {
            foreach ($trapped as $k => $v) {
                for ($i = 1; $i <= 50; $i++) {
                    if ($v['u' . $i] > 0) $result += $v['u' . $i];
                }
                if ($v['hero'] > 0) $result += 1;
            }
        }
        return $result;
    }

    public function getTrapped($id)
    {
        $q = "SELECT * FROM trapped WHERE `id` = $id";

        return mysql_fetch_assoc($result);
    }

    public function getTrappedIn($vref)
    {
        $q = "SELECT * from trapped where `vref` = '$vref'";
        return $this->query_return($q);
    }

    public function getTrappedFrom($from)
    {
        $q = "SELECT * from trapped where `from` = '$from'";
        return $this->query_return($q);
    }

    public function addTrapped($vref, $from)
    {
        $id = $this->hasTrapped($vref, $from);
        if (!$id) {
            $q = "INSERT into trapped (vref,`from`) values (" . $vref . "," . $from . ")";

            $id = mysql_insert_id($this->connection);
        }
        return $id;
    }

    public function hasTrapped($vref, $from)
    {
        $q = "SELECT id FROM trapped WHERE `vref` = $vref AND `from` = $from";

        $result = mysql_fetch_assoc($result);
        if (isset($result['id'])) {
            return $result['id'];
        } else {
            return false;
        }
    }

    public function modifyTrapped($id, $unit, $amount, $mode)
    {
        if (!$mode) {
            $trapped = $this->conn->select($unit)->from('trapped')->where('id = :id', [':id' => $id])->get();
            $amount = min($trapped['u' . $unit], $amount);
            $this->conn->upgrade('trapped', [$unit => "$unit - $amount"], 'id = :id', [':id' => $id]);
        } else {
            $this->conn->upgrade('trapped', [$unit => "$unit + $amount"], 'id = :id', [':id' => $id]);
        }
    }

    public function removeTrapped($id)
    {
        $q = "DELETE FROM trapped WHERE `id`=$id";

    }

    public function removeAnimals($id)
    {
        $q = "DELETE FROM enforcement WHERE `id`=$id";

    }

    public function checkEnforce($vid, $from)
    {
        $q = "SELECT `id` from enforcement where `from` = $from and vref = $vid";
        $result = $this->query_return($q);
        if (count($result)) {
            return $result[0];
        } else {
            return false;
        }
    }

    public function addEnforce($data)
    {
        $q = "INSERT into enforcement (vref,`from`) values (" . $data['to'] . "," . $data['from'] . ")";

        $id = mysql_insert_id($this->connection);
        $isoasis = $this->isVillageOases($data['from']);
        if ($isoasis) {
            $fromVillage = $this->getOMInfo($data['from']);
        } else {
            $fromVillage = $this->getMInfo($data['from']);
        }
        $fromTribe = $this->getUserField($fromVillage["owner"], "tribe", 0);
        $start = ($fromTribe - 1) * 10 + 1;
        $end = ($fromTribe * 10);
        //add unit
        $j = '1';
        for ($i = $start; $i <= $end; $i++) {
            $this->modifyEnforce($id, $i, $data['t' . $j . ''], 1);
            $j++;
        }
        return mysql_insert_id($this->connection);
    }

    public function getOMInfo($id)
    {
        return $this->conn->leftJoin('wdata', 'odata', 'odata.wref = wdata.id', '*', 'wdata.id = :id', [':id' => $id]);
    }

    public function getMInfo($id)
    {
        return $this->conn->leftJoin('wdata', 'vdata', 'vdata.wref = wdata.id', '*', 'wdata.id = :id', [':id' => $id]);
    }

    public function modifyEnforce($id, $unit, $amount, $mode)
    {
        if ($unit == 'hero') {
            $unit = 'hero';
        } else {
            $unit = 'u' . $unit;
        }
        if (!$mode) {
            $q = "SELECT $unit FROM enforcement WHERE id = $id";
            $result = $this->query_return($q);
            if (isset($result) && !empty($result) && count($result) > 0) {
                $row = $result[0];
                $amount = min($row[$unit], $amount);
                $q = "UPDATE enforcement set $unit = $unit - $amount where id = $id";

            }
        } else {
            $q = "UPDATE enforcement set $unit = $unit + $amount where id = $id";

        }
    }

    public function addHeroEnforce($data)
    {
        $q = "INSERT into enforcement (`vref`,`from`,`hero`) values (" . $data['to'] . "," . $data['from'] . ",1)";

    }

    public function getEnforceArray($id, $mode)
    {
        if (!$mode) {
            $q = "SELECT * from enforcement where id = $id";
        } else {
            $q = "SELECT * from enforcement where `from` = $id";
        }

        return mysql_fetch_assoc($result);
    }

    public function getEnforceVillage($id, $mode)
    {
        $column = !$mode ? 'vref' : 'from';
        return $this->conn->select('*')
            ->from('enforcement')
            ->where("$column = :ref", [':ref' => $id])
            ->get();
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

            return $this->conn->select('*')
                ->from('enforcement')
                ->where('`from` = :from AND `vref` IN :vref', [':from' => $id, ':vref' => $inos])
                ->get();
        } else {
            return null;
        }
    }

    public function getOasis($vid)
    {
        return $this->conn
            ->select('`type`,`wref`')
            ->from('odata')
            ->where('`conqured` = :conqured', [':conqured' => $vid])
            ->get();
    }

    public function getVillageMovement($id)
    {
        $vinfo = $this->getVillage($id);
        if (isset($vinfo['owner'])) {
            $vtribe = $this->getUserField($vinfo['owner'], "tribe", 0);
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
            return array();
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
        //$time = time();
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

        $array = $this->mysql_fetch_all($result);
        return $array;
    }

    public function getVillageMovementArray($id)
    {
        $movingarray = array();
        $outgoingarray = $this->getMovement(3, $id, 0);
        if (!empty($outgoingarray)) $movingarray = array_merge($movingarray, $outgoingarray);
        $returningarray = $this->getMovement(4, $id, 1);
        if (!empty($returningarray)) $movingarray = array_merge($movingarray, $returningarray);
        return $movingarray;
    }

    public function getWW()
    {
        $q = "SELECT vref FROM fdata WHERE f99t = 40";

        if (mysql_num_rows($result)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get world wonder level!
     * Made by: Dzoki
     */
    public function getWWLevel($vref)
    {
        $q = "SELECT f99 FROM fdata WHERE vref = $vref";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['f99'];
    }

    /**
     * get world wonder owner ID!
     * Made by: Dzoki
     */
    public function getWWOwnerID($vref)
    {
        $q = "SELECT owner FROM vdata WHERE wref = $vref LIMIT 1";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['owner'];
    }

    /**
     * get user alliance name!
     * Made by: Dzoki
     */
    public function getUserAllianceID($id)
    {
        $q = "SELECT alliance FROM users where id = $id LIMIT 1";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['alliance'];
    }

    /**
     * get WW name
     * Made by: Dzoki
     */
    public function getWWName($vref)
    {
        $q = "SELECT wwname FROM fdata WHERE vref = $vref";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['wwname'];
    }

    /**
     * change WW name
     * Made by: Dzoki
     */
    public function submitWWname($vref, $name)
    {
        $q = "UPDATE fdata SET `wwname` = '$name' WHERE fdata.`vref` = $vref";

    }

    public function modifyCommence($id, $commence = 0)
    {
        if ($commence == 0) $commence = time();
        $q = "UPDATE training set commence = $commence WHERE id=$id";

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
        return $this->conn->select('uid')
            ->from('deleting')
            ->where('timestamp <= :time', [':time' => time()])
            ->get();
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
        $q = "SELECT wood FROM vdata WHERE wref = $wref LIMIT 1";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['wood'];
    }

    public function getClayAvailable($wref)
    {
        $q = "SELECT clay FROM vdata WHERE wref = $wref LIMIT 1";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['clay'];
    }

    public function getIronAvailable($wref)
    {
        $q = "SELECT iron FROM vdata WHERE wref = $wref LIMIT 1";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['iron'];
    }

    public function getCropAvailable($wref)
    {
        $q = "SELECT crop FROM vdata WHERE wref = $wref LIMIT 1";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['crop'];
    }

    public function poulateOasisdata()
    {
        $q2 = "SELECT id FROM wdata where oasistype != 0";
        $result2 = mysql_query($q2, $this->connection);
        while ($row = mysql_fetch_array($result2)) {
            $worlID = $row['id'];
            $time = time();
            $t1 = 750 * SPEED / 10;
            $t2 = 750 * SPEED / 10;
            $t3 = 750 * SPEED / 10;

            $t4 = 800 * SPEED / 10;
            $t5 = 750 * SPEED / 10;
            $t6 = 800 * SPEED / 10;

            $tt = "$t1,$t2,$t3,0,0,0,$t4,$t5,0,$t6,$time,$time,$time";
            $basearray = $this->getOMInfo($worlID);
            //We switch type of oasis and instert record with apropriate infomation.
            $q = "INSERT into odata VALUES ('" . $basearray['id'] . "'," . $basearray['oasistype'] . ",0," . $tt . ",100,3,'Unoccupied oasis')";

        }
    }

    public function getAvailableExpansionTraining()
    {
        global $building, $session, $technology, $village;
        $q = "SELECT (IF(exp1=0,1,0)+IF(exp2=0,1,0)+IF(exp3=0,1,0)) FROM vdata WHERE wref = $village->wid";

        $row = mysql_fetch_row($result);
        $maxslots = $row[0];
        $residence = $building->getTypeLevel(25);
        $palace = $building->getTypeLevel(26);
        if ($residence > 0) {
            $maxslots -= (3 - floor($residence / 10));
        }
        if ($palace > 0) {
            $maxslots -= (3 - floor(($palace - 5) / 5));
        }

        $q = "SELECT (u10+u20+u30) FROM units WHERE vref = $village->wid";

        $row = mysql_fetch_row($result);
        $settlers = $row[0];
        $q = "SELECT (u9+u19+u29) FROM units WHERE vref = $village->wid";

        $row = mysql_fetch_row($result);
        $chiefs = $row[0];

        $settlers += 3 * count($this->getMovement(5, $village->wid, 0));
        $current_movement = $this->getMovement(3, $village->wid, 0);
        if (!empty($current_movement)) {
            foreach ($current_movement as $build) {
                $settlers += $build['t10'];
                $chiefs += $build['t9'];
            }
        }
        $current_movement = $this->getMovement(3, $village->wid, 1);
        if (!empty($current_movement)) {
            foreach ($current_movement as $build) {
                $settlers += $build['t10'];
                $chiefs += $build['t9'];
            }
        }
        $current_movement = $this->getMovement(4, $village->wid, 0);
        if (!empty($current_movement)) {
            foreach ($current_movement as $build) {
                $settlers += $build['t10'];
                $chiefs += $build['t9'];
            }
        }
        $current_movement = $this->getMovement(4, $village->wid, 1);
        if (!empty($current_movement)) {
            foreach ($current_movement as $build) {
                $settlers += $build['t10'];
                $chiefs += $build['t9'];
            }
        }
        $q = "SELECT (u10+u20+u30) FROM enforcement WHERE `from` = " . $village->wid;

        $row = mysql_fetch_row($result);
        if (!empty($row)) {
            foreach ($row as $reinf) {
                $settlers += $reinf[0];
            }
        }
        $q = "SELECT (u10+u20+u30) FROM trapped WHERE `from` = " . $village->wid;

        $row = mysql_fetch_row($result);
        if (!empty($row)) {
            foreach ($row as $trapped) {
                $settlers += $trapped[0];
            }
        }
        $q = "SELECT (u9+u19+u29) FROM enforcement WHERE `from` = " . $village->wid;

        $row = mysql_fetch_row($result);
        if (!empty($row)) {
            foreach ($row as $reinf) {
                $chiefs += $reinf[0];
            }
        }
        $q = "SELECT (u9+u19+u29) FROM trapped WHERE `from` = " . $village->wid;

        $row = mysql_fetch_row($result);
        if (!empty($row)) {
            foreach ($row as $trapped) {
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

        if (!$technology->getTech(($session->tribe - 1) * 10 + 9)) {
            $chiefslots = 0;
        }
        $slots = array("chiefs" => $chiefslots, "settlers" => $settlerslots);
        return $slots;
    }

    public function addArtefact($vref, $owner, $type, $size, $name, $desc, $effecttype, $effect, $aoe, $img)
    {
        $q = "INSERT INTO `artefacts` (`vref`, `owner`, `type`, `size`, `conquered`, `name`, `desc`, `effecttype`, `effect`, `aoe`, `img`) VALUES ('$vref', '$owner', '$type', '$size', '" . time() . "', '$name', '$desc', '$effecttype', '$effect', '$aoe', '$img')";

    }

    public function getOwnArtefactInfo($vref)
    {
        $q = "SELECT * FROM artefacts WHERE vref = $vref";
        return $this->query_return($q);
    }

    public function getArtefactInfo($sizes)
    {
        if (count($sizes) != 0) {
            $sizestr = ' AND ( FALSE ';
            foreach ($sizes as $s) {
                $sizestr .= ' OR `artefacts`.`size` = ' . $s . ' ';
            }
            $sizestr .= ' ) ';
        } else {
            $sizestr = '';
        }
        $q = "SELECT * FROM artefacts WHERE true " . $sizestr . ' ORDER BY type';
        return $this->query_return($q);
    }

    public function getArtefactInfoByDistance($coor, $distance, $sizes)
    {
        if (count($sizes) != 0) {
            $sizestr = ' AND ( FALSE ';
            foreach ($sizes as $s) {
                $sizestr .= ' OR artefacts.size = ' . $s . ' ';
            }
            $sizestr .= ' ) ';
        } else {
            $sizestr = '';
        }
        $q = "SELECT *,"
            . " (ROUND(SQRT(POW(LEAST(ABS(" . $coor['x'] . " - wdata.x), ABS(" . $coor['max'] . " - ABS(" . $coor['x'] . " - wdata.x))), 2) + POW(LEAST(ABS(" . $coor['y'] . " - wdata.y), ABS(" . $coor['max'] . " - ABS(" . $coor['y'] . " - wdata.y))), 2)),3)) AS distance "
            . " FROM wdata, artefacts WHERE artefacts.vref = wdata.id"
            . " AND (ROUND(SQRT(POW(LEAST(ABS(" . $coor['x'] . " - wdata.x), ABS(" . $coor['max'] . " - ABS(" . $coor['x'] . " - wdata.x))), 2) + POW(LEAST(ABS(" . $coor['y'] . " - wdata.y), ABS(" . $coor['max'] . " - ABS(" . $coor['y'] . " - wdata.y))), 2)),3)) <= " . $distance
            . $sizestr
            . ' ORDER BY distance';
        return $this->query_return($q);
    }

    public function arteIsMine($id, $newvref, $newowner)
    {
        $q = "UPDATE artefacts SET `owner` = " . $newowner . " WHERE id = " . $id;
        $this->query($q);
        $this->captureArtefact($id, $newvref, $newowner);
    }

    public function captureArtefact($id, $newvref, $newowner)
    {
        // get the artefact
        $currentArte = $this->getArtefactDetails($id);

        // set new active artes for new owner
        #---------first inactive large and uinque artes if this currentArte is large/unique
        if ($currentArte['size'] == 2 || $currentArte['size'] == 3) {
            $ulArts = $this->query_return('SELECT * FROM artefacts WHERE `owner`=' . $newowner . ' AND `status`=1 AND `size`<>1');
            if (!empty($ulArts) && count($ulArts) > 0) {
                foreach ($ulArts as $art) $this->query("UPDATE artefacts SET `status` = 2 WHERE id = " . $art['id']);
            }
        }
        #---------then check extra artes
        $vArts = $this->query_return('SELECT * FROM artefacts WHERE `vref`=' . $newvref . ' AND `status`=1');
        if (!empty($vArts) && count($vArts) > 0) {
            foreach ($vArts as $art) $this->query("UPDATE artefacts SET `status` = 2 WHERE id = " . $art['id']);
        } else {
            $uArts = $this->query_return('SELECT * FROM artefacts WHERE `owner`=' . $newowner . ' AND `status`=1 ORDER BY conquered DESC');
            if (!empty($uArts) && count($uArts) > 2) {
                for ($i = 2; $i < count($uArts); $i++) $this->query("UPDATE artefacts SET `status` = 2 WHERE id = " . $uArts[$i]['id']);
            }
        }
        // set currentArte -> owner,vref,conquered,status
        $time = time();
        $q = "UPDATE artefacts SET vref = $newvref, owner = $newowner, conquered = $time, `status` = 1 WHERE id = $id";
        $this->query($q);
        // set new active artes for old user
        if ($currentArte['status'] == 1) {
            #--- get olduser's active artes
            $ouaArts = $this->query_return('SELECT * FROM artefacts WHERE `owner`=' . $currentArte['owner'] . ' AND `status`=1');
            $ouiArts = $this->query_return('SELECT * FROM artefacts WHERE `owner`=' . $currentArte['owner'] . ' AND `status`=2 ORDER BY conquered DESC');
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
                            $q = "UPDATE artefacts SET `status` = 1 WHERE id = " . $ia['id'];
                            $this->query($q);
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
        $q = "SELECT * FROM artefacts WHERE id = " . $id;

        return mysql_fetch_array($result);
    }

    public function getHeroFace($userID)
    {
        $q = "SELECT * FROM heroface WHERE uid = " . $userID;

        return mysql_fetch_array($result);
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
        return $this->conn->insert('heroface', $data);
    }

    public function modifyHeroFace($userID, $column, $value)
    {
        $hash = md5("$userID" . time());
        $q = "UPDATE heroface SET `$column`='$value',`hash`='$hash' WHERE `uid` = '$userID'";

    }

    public function modifyWholeHeroFace($userID, $face, $color, $hair, $ear, $eyebrow, $eye, $nose, $mouth, $beard)
    {
        $hash = md5("$userID" . time());
        $q = "UPDATE heroface SET `face`=$face,`color`=$color,`hair`=$hair,`ear`=$ear,`eyebrow`=$eyebrow,`eye`=$eye,`nose`=$nose,`mouth`=$mouth,`beard`=$beard,`hash`='$hash' WHERE uid = $userID";


    }

    public function populateOasisUnitsLow()
    {
        $q2 = "SELECT * FROM wdata where oasistype != 0";
        $result2 = mysql_query($q2, $this->connection);
        while ($row = mysql_fetch_array($result2)) {
            $worlID = $row['id'];
            $basearray = $this->getMInfo($worlID);
            //each Troop is a Set for oasis type like mountains have rats spiders and snakes fields tigers elphants clay wolves so on stonger one more not so less
            switch ($basearray['oasistype']) {
                case 1:
                case 2:
                    // Oasis Random populate
                    $UP35 = rand(5, 30) * (SPEED / 10);
                    $UP36 = rand(5, 30) * (SPEED / 10);
                    $UP37 = rand(0, 30) * (SPEED / 10);
                    //+25% lumber per hour
                    $q = "UPDATE units SET u35 = u35 +  '" . $UP35 . "', u36 = u36 + '" . $UP36 . "', u37 = u37 + '" . $UP37 . "' WHERE vref = '" . $worlID . "'";

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
                    $q = "UPDATE units SET u35 = u35 +  '" . $UP35 . "', u36 = u36 + '" . $UP36 . "', u37 = u37 + '" . $UP37 . "', u39 = u39 + '" . $UP39 . "', u40 = u40 + '" . $UP40 . "' WHERE vref = '" . $worlID . "'";

                    break;
                case 4:
                case 5:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP32 = rand(5, 30) * (SPEED / 10);
                    $UP35 = rand(0, 25) * (SPEED / 10);
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 +  '" . $UP31 . "', u32 = u32 + '" . $UP32 . "', u35 = u35 + '" . $UP35 . "' WHERE vref = '" . $worlID . "'";

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
                    $q = "UPDATE units SET u31 = u31 +  '" . $UP31 . "', u32 = u32 + '" . $UP32 . "', u35 = u35 + '" . $UP35 . "', u38 = u38 + '" . $UP38 . "', u40 = u40 + '" . $UP40 . "' WHERE vref = '" . $worlID . "'";

                    break;
                case 7:
                case 8:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP32 = rand(5, 30) * (SPEED / 10);
                    $UP34 = rand(0, 25) * (SPEED / 10);
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 +  '" . $UP31 . "', u32 = u32 + '" . $UP32 . "', u34 = u34 + '" . $UP34 . "' WHERE vref = '" . $worlID . "'";

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
                    $q = "UPDATE units SET u31 = u31 +  '" . $UP31 . "', u32 = u32 + '" . $UP32 . "', u34 = u34 + '" . $UP34 . "', u37 = u37 + '" . $UP37 . "', u40 = u40 + '" . $UP40 . "' WHERE vref = '" . $worlID . "'";

                    break;
                case 10:
                case 11:
                    // Oasis Random populate
                    $UP31 = rand(5, 40) * (SPEED / 10);
                    $UP33 = rand(5, 30) * (SPEED / 10);
                    $UP37 = rand(1, 25) * (SPEED / 10);
                    $UP39 = rand(0, 25) * (SPEED / 10);
                    //+25% lumber per hour
                    $q = "UPDATE units SET u31 = u31 +  '" . $UP31 . "', u33 = u33 + '" . $UP33 . "', u37 = u37 + '" . $UP37 . "', u39 = u39 + '" . $UP39 . "' WHERE vref = '" . $worlID . "'";

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
                    $q = "UPDATE units SET u31 = u31 +  '" . $UP31 . "', u33 = u33 + '" . $UP33 . "', u38 = u38 + '" . $UP38 . "', u39 = u39 + '" . $UP39 . "', u40 = u40 + '" . $UP40 . "' WHERE vref = '" . $worlID . "'";

                    break;
            }
        }
    }

    public function hasBeginnerProtection($vid)
    {
        $q = "SELECT u.protect FROM users u,vdata v WHERE u.id=v.owner AND v.wref=" . $vid;

        $dbarray = mysql_fetch_array($result);
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

    public function addCLP($userID, $clp)
    {
        $q = "UPDATE users set clp = clp + $clp where id = $userID";

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
        return $this->conn->insert('mdata', $data);
    }

    public function getLinks($userID)
    {
        return $this->conn->select('`url`,`name`')
            ->from('links')
            ->where('`userid` = :userid', [':userid' => $userID])
            ->orderByAsc('pos')
            ->get();
    }

    public function removeLinks($id, $userID)
    {
        $this->conn->delete('links', 'id = :id, userid = :userid', [':id' => $id, ':userid' => $userID]);
    }

    public function getFarmlist($userID)
    {
        $q = 'SELECT id FROM farmlist WHERE owner = ' . $userID . ' ORDER BY name ASC';

        $dbarray = mysql_fetch_array($result);

        if ($dbarray['id'] != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getRaidList($id)
    {
        $q = "SELECT * FROM raidlist WHERE id = " . $id . "";

        return mysql_fetch_array($result);
    }

    public function getAllAuction()
    {
        $q = "SELECT * FROM auction WHERE finish = 0";

        return mysql_fetch_array($result);
    }

    public function getVilFarmlist($wref)
    {
        $q = 'SELECT id FROM farmlist WHERE wref = ' . $wref . ' ORDER BY wref ASC';

        $dbarray = mysql_fetch_array($result);

        if ($dbarray['id'] != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function delFarmList($id, $owner)
    {
        $q = "DELETE FROM farmlist where id = $id and owner = $owner";

    }

    public function delSlotFarm($id)
    {
        $q = "DELETE FROM raidlist where id = $id";

    }

    public function createFarmList($wref, $owner, $name)
    {
        $q = "INSERT INTO farmlist (`wref`, `owner`, `name`) VALUES ('$wref', '$owner', '$name')";

    }

    public function addSlotFarm($lid, $towref, $x, $y, $distance, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10)
    {
        $q = "INSERT INTO raidlist (`lid`, `towref`, `x`, `y`, `distance`, `t1`, `t2`, `t3`, `t4`, `t5`, `t6`, `t7`, `t8`, `t9`, `t10`) VALUES ('$lid', '$towref', '$x', '$y', '$distance', '$t1', '$t2', '$t3', '$t4', '$t5', '$t6', '$t7', '$t8', '$t9', '$t10')";

    }

    public function editSlotFarm($eid, $lid, $wref, $x, $y, $dist, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10)
    {
        $q = "UPDATE raidlist set lid = '$lid', towref = '$wref', x = '$x', y = '$y', t1 = '$t1', t2 = '$t2', t3 = '$t3', t4 = '$t4', t5 = '$t5', t6 = '$t6', t7 = '$t7', t8 = '$t8', t9 = '$t9', t10 = '$t10' WHERE id = $eid";

    }

    public function removeOases($wref)
    {
        $q = "UPDATE odata SET conqured = 0, owner = 3, name = '" . UNOCCUPIEDOASES . "' WHERE wref = $wref";
        $r =
        $q = "UPDATE wdata SET occupied = 0 WHERE id = $wref";
        $r2 =
        return ($r && $r2);
    }

    public function getArrayMemberVillage($userID)
    {
        $fields = ['v.wref', 'v.name', 'v.capital', 'w.x', 'w.y'];
        $joinTables = ['vdata AS v', 'wdata AS w'];
        $joinConditions = ['w.id' => 'v.wref'];
        $condition = ['owner' => $userID];
        $orderBy = 'ORDER BY capital, pop DESC';
        return $this->conn->join($fields, $joinTables, $joinConditions, $condition, null, $orderBy);
    }

    public function getNoticeData($nid)
    {
        $q = "SELECT `data` FROM ndata where id = $nid";

        $dbarray = mysql_fetch_array($result);
        return $dbarray['data'];
    }

    public function getUsersNotice($userID, $ntype = -1, $viewed = -1)
    {
        $q = "SELECT * FROM ndata where uid=$userID ";
        if ($ntype >= 0) {
            $q .= " and ntype=$ntype ";
        }
        if ($viewed >= 0) {
            $q .= " and viewed=$viewed ";
        }

        $dbarray = mysql_fetch_array($result);
        return $dbarray;
    }

    public function setSilver($userID, $silver, $mode)
    {
        if (!$mode) {
            $q = "UPDATE users set silver = silver - $silver where id = $userID";
            //Used Silver
            $q2 = "UPDATE users set usedsilver = usedsilver+" . $silver . " where id = $userID";
            mysql_query($q2, $this->connection);
        } else {
            $q = "UPDATE users set silver = silver + $silver where id = $userID";
            //Addgold gold
            $q2 = "UPDATE users set Addsilver = Addsilver+" . $silver . " where id = $userID";
            mysql_query($q2, $this->connection);
        }

    }

    public function getAuctionSilver($userID)
    {
        $q = "SELECT * FROM auction where uid = $userID and finish = 0";

        return mysql_fetch_array($result);
    }

    public function delAuction($id)
    {
        $aucData = $this->getAuctionData($id);
        $usedtime = AUCTIONTIME - ($aucData['time'] - time());
        if (($usedtime < (AUCTIONTIME / 10)) && !$aucData['bids']) {
            $this->modifyHeroItem($aucData['itemid'], 'num', $aucData['num'], 1);
            $this->modifyHeroItem($aucData['itemid'], 'proc', 0, 0);
            $q = "DELETE FROM auction where id = $id and finish = 0";

        } else {
            return false;
        }
    }

    public function getAuctionData($id)
    {
        $q = "SELECT * FROM auction where id = $id";

        return mysql_fetch_array($result);
    }

    public function modifyHeroItem($id, $column, $value, $mode)
    {
        // mode=0 set; 1 add; 2 sub; 3 mul; 4 div
        switch ($mode) {
            case 0:
                $cmd = " $column = $value ";
                break;
            case 1:
                $cmd = " $column = $column+$value ";
                break;
            case 2:
                $cmd = " $column = $column-$value ";
                break;
            case 3:
                $cmd = " $column = $column*$value ";
                break;
            case 4:
                $cmd = " $column = $column/$value ";
                break;
        }
        $q = "UPDATE heroitems set $cmd where id = $id";

        return ($result ? true : false);
    }

    public function getAuctionUser($userID)
    {
        $q = "SELECT * FROM auction where owner = $userID";

        return mysql_fetch_array($result);
    }

    public function addAuction($owner, $itemid, $btype, $type, $amount)
    {
        $time = time() + AUCTIONTIME;
        $itemData = $this->getHeroItem($itemid);
        if ($amount >= $itemData['num']) {
            $amount = $itemData['num'];
            $this->modifyHeroItem($itemid, 'proc', 1, 0);
        }
        if ($amount <= 0) return false;
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
        $q = "INSERT INTO auction (`owner`, `itemid`, `btype`, `type`, `num`, `uid`, `bids`, `silver`, `maxsilver`, `time`, `finish`) VALUES ('$owner', '$itemid', '$btype', '$type', '$amount', 0, 0, '$silver', '$silver', '$time', 0)";

    }

    public function getHeroItem($id = 0, $userID = 0, $btype = 0, $type = 0, $proc = 2)
    {
        $q = "SELECT * FROM heroitems WHERE TRUE "
            . ($id ? (" AND id = " . $id) : (""))
            . ($userID ? (" AND uid = " . $userID) : (""))
            . ($btype ? (" AND btype = " . $btype) : (""))
            . ($type ? (" AND type = " . $type) : (""))
            . ($proc != 2 ? (" AND proc = " . $proc) : (""));
        $result = $this->query_return($q);
        if ($id) $result = $result[0];
        return $result;
    }

    public function addBid($id, $userID, $silver, $maxsilver, $time)
    {
        $q = "UPDATE auction set uid = $userID, silver = $silver, maxsilver = $maxsilver, bids = bids + 1, time = $time where id = $id";

    }

    public function removeBidNotice($id)
    {
        $q = "DELETE FROM auction where id = $id";

    }

    public function addHeroItem($userID, $btype, $type, $num)
    {
        $q = "INSERT INTO heroitems (`uid`, `btype`, `type`, `num`, `proc`) VALUES ('$userID', '$btype', '$type', '$num', 0)";

    }

    public function checkHeroItem($userID, $btype, $type = 0, $proc = 2)
    {
        $q = "SELECT id, btype FROM heroitems WHERE TRUE "
            . ($userID ? " AND uid = '$userID'" : '')
            . ($btype ? " AND btype = '$btype'" : '')
            . ($type ? " AND type = '$type'" : '')
            . ($proc != 2 ? " AND proc = '$proc'" : '');

        $dbarray = mysql_fetch_array($result);
        if (isset($dbarray['btype'])) {
            return $dbarray['id'];
        } else {
            return false;
        }
    }

    public function editBid($id, $maxsilver, $minsilver)
    {
        $q = "UPDATE auction set maxsilver = $maxsilver, silver = $minsilver where id = $id";

    }

    public function getBidData($id)
    {
        $q = "SELECT * FROM auction WHERE id = $id";

        return mysql_fetch_array($result);
    }

    public function getFLData($id)
    {
        $q = "SELECT * FROM farmlist where id = $id";

        return mysql_fetch_array($result);
    }

    public function getHeroField($userID, $field)
    {
        $q = "SELECT " . $field . " FROM hero WHERE uid = $userID";

        $dbarray = mysql_fetch_array($result);
        return $dbarray[$field];
    }

    public function getCapBrewery($userID)
    {
        $capWref = $this->getVFH($userID);
        if ($capWref) {
            $q = "SELECT * FROM fdata WHERE vref = " . $capWref;

            if ($result) {
                $dbarray = mysql_fetch_assoc($result);
                if (!empty($dbarray)) {
                    for ($i = 19; $i <= 40; $i++) {
                        if ($dbarray['f' . $i . 't'] == 35) {
                            return $dbarray['f' . $i];
                        }
                    }
                }
            }
        }
        return 0;
    }

    public function getVFH($userID)
    {
        return $this->conn
            ->select('wref')
            ->from('vdata')
            ->where("`owner` = {$userID} AND capital = 1")
            ->first()['wref'];
    }

    public function getNotice2($id, $field)
    {
        $q = "SELECT " . $field . " FROM ndata where `id` = '$id'";

        $dbarray = mysql_fetch_array($result);
        return $dbarray[$field];
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
        return $this->conn->insert('adventure', $data);
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
        return $this->conn->insert('hero', $data);
    }

    // Add new password => mode:0
    // Add new email => mode: 1
    public function addNewProc($userID, $npw, $nemail, $act, $mode)
    {
        $time = time();
        if (!$mode) {
            $q = "INSERT into newproc (uid, npw, act, time, proc) values ('$userID', '$npw', '$act', '$time', 0)";
        } else {
            $q = "INSERT into newproc (uid, nemail, act, time, proc) values ('$userID', '$nemail', '$act', '$time', 0)";
        }


    }

    public function checkProcExist($userID)
    {
        $q = "SELECT uid FROM newproc where uid = '$userID' and proc = 0";

        if (mysql_num_rows($result)) {
            return false;
        } else {
            return true;
        }
    }

    public function removeProc($userID)
    {
        $q = "DELETE FROM newproc where uid = $userID";

    }

    public function checkBan($userID)
    {
        $q = "SELECT access FROM users WHERE id = $userID LIMIT 1";
        $result = $this->query_return($q);
        if (!empty($result) && ($result[0]['access'] <= 1 /*|| $result[0]['access']>=7*/)) {
            return true;
        } else {
            return false;
        }
    }

    public function getNewProc($userID)
    {
        $q = "SELECT `npw`,`act` FROM newproc WHERE uid = $userID";

        if (mysql_num_rows($result)) {
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }

    public function CheckAdventure($userID, $wref, $end)
    {
        $q = "SELECT `id` FROM adventure WHERE uid = $userID AND wref = $wref AND end = $end";

        if ($result) {
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }

    public function getAdventure($userID, $wref = 0, $end = 2)
    {
        $q = "SELECT `id`,`dif` FROM adventure WHERE uid = $userID "
            . ($wref != 0 ? " AND wref = '$wref'" : '')
            . ($end != 2 ? " AND end = $end" : '');

        if ($result) {
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }

    public function editTableField($table, $field, $value, $refField, $ref)
    {
        $q = "UPDATE " . $table . " set $field = '$value' where " . $refField . " = '$ref'";

    }

    public function config()
    {
        $q = "SELECT * FROM config";

        return mysql_fetch_array($result);
    }

    public function getAllianceDipProfile($aid, $type)
    {
        $q = "SELECT `alli2` FROM diplomacy WHERE alli1 = '$aid' AND type = '$type' AND accepted = '1'";

        if (mysql_num_rows($result) == 0) {
            $q2 = "SELECT `alli1` FROM diplomacy WHERE alli2 = '$aid' AND type = '$type' AND accepted = '1'";
            $result2 = mysql_query($q2, $this->connection);
            while ($row = mysql_fetch_array($result2)) {
                $alliance = $this->getAlliance($row['alli1']);
                $text = "";
                $text .= "<a href=allianz.php?aid=" . $alliance['id'] . ">" . $alliance['tag'] . "</a><br> ";
            }
        } else {
            while ($row = mysql_fetch_array($result)) {
                $alliance = $this->getAlliance($row['alli2']);
                $text = "";
                $text .= "<a href=allianz.php?aid=" . $alliance['id'] . ">" . $alliance['tag'] . "</a><br> ";
            }
        }
        if (strlen($text) == 0) {
            $text = "-<br>";
        }
        return $text;
    }

    public function getAlliance($id, $mod = 0)
    {
        if (!$id) return 0;
        switch ($mod) {
            case 0:
                $where = ' id = "' . $id . '"';
                break;
            case 1:
                $where = ' name = "' . $id . '"';
                break;
            case 2:
                $where = ' tag = "' . $id . '"';
                break;
        }
        $q = "SELECT `id`,`tag`,`desc`,`max`,`name`,`notice` from alidata where " . $where;

        return mysql_fetch_assoc($result);
    }

    public function canClaimArtifact($vref, $type)
    {
        $DefenderFields = $this->getResourceLevel($vref);
        for ($i = 19; $i <= 38; $i++) {
            if ($DefenderFields['f' . $i . 't'] == 27) {
                $defcanclaim = FALSE;
                //$defTresuaryLevel = $DefenderFields['f' . $i];
            } else {
                $defcanclaim = TRUE;
            }
        }
        $AttackerFields = $this->getResourceLevel($vref);
        for ($i = 19; $i <= 38; $i++) {
            if ($AttackerFields['f' . $i . 't'] == 27) {
                $attTresuaryLevel = $AttackerFields['f' . $i];
                if ($attTresuaryLevel >= 10) {
                    $villageartifact = TRUE;
                } else {
                    $villageartifact = FALSE;
                }
                if ($attTresuaryLevel == 20) {
                    $accountartifact = TRUE;
                } else {
                    $accountartifact = FALSE;
                }
            }
        }
        if ($type == 1) {
            if ($defcanclaim == TRUE && $villageartifact == TRUE) {
                return TRUE;
            }
        } else if ($type == 2) {
            if ($defcanclaim == TRUE && $accountartifact == TRUE) {
                return TRUE;
            }
        } else if ($type == 3) {
            if ($defcanclaim == TRUE && $accountartifact == TRUE) {
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }

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
        global $bid4, $bid8, $bid9;

        $basecrop = $grainmill = $bakery = 0;
        $owner = $this->getVillageField($wref, 'owner');
        $bonus = $this->getUserField($owner, 'b4', 0);

        $buildarray = $this->getResourceLevel($wref);
        $cropholder = array();
        for ($i = 1; $i <= 38; $i++) {
            if ($buildarray['f' . $i . 't'] == 4) {
                array_push($cropholder, 'f' . $i);
            }
            if ($buildarray['f' . $i . 't'] == 8) {
                $grainmill = $buildarray['f' . $i];
            }
            if ($buildarray['f' . $i . 't'] == 9) {
                $bakery = $buildarray['f' . $i];
            }
        }
        $q = "SELECT type FROM `odata` WHERE conqured = $wref";
        $oasis = $this->query_return($q);
        $cropo = 0;
        foreach ($oasis as $oa) {
            switch ($oa['type']) {
                case 3:
                    $cropo += 1;
                    break;
                case 6:
                    $cropo += 1;
                    break;
                case 9:
                    $cropo += 1;
                    break;
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
        $crop *= SPEED;
        return $crop;
    }

    public function getNatarsProgress()
    {
        $q = "SELECT * FROM natarsprogress";
        $sql = mysql_query($q);
        $result = mysql_fetch_array($sql);
        return $result;
    }

    public function setNatarsProgress($field, $value)
    {
        $q = "UPDATE natarsprogress SET `$field` = '$value'";

    }

    public function getNatarsCapital()
    {
        $q = "SELECT `wref` FROM vdata WHERE owner=2 AND capital = 1 ORDER BY created ASC";
        $result = $this->query_return($q);
        return $result[0];
    }

    public function getNatarsWWVillages()
    {
        $q = "SELECT `owner` FROM vdata WHERE owner=2 AND name = 'WW Village' ORDER BY created ASC";
        $result = $this->query_return($q);
        return $result;
    }

    public function addNatarsVillage($worlID, $userID, $username, $capital)
    {
        $total = count($this->getVillagesID($userID));
        $vname = sprintf("[%05d] Natars", $total + 1);
        $time = time();
        $q = "INSERT into vdata "
            . " (wref, owner, name, capital, pop, cp, celebration, wood, clay, iron, maxstore, crop, maxcrop, lastupdate, created, natar)"
            . " values ('$worlID', '$userID', '$vname', '$capital', 2, 1, 0, 780, 780, 780, 800, 780, 800, '$time', '$time', '1')";

    }

    public function instantTrain($vref)
    {
        $q = 'SELECT `id` FROM training WHERE `vref`=' . $vref;
        $count = count($this->query_return($q));
        $q = 'UPDATE training SET `commence`=0,`eachtime`=1,`endat`=0,`timestamp`=0 WHERE `vref`=' . $vref;

        if ($result) {
            return $count;
        } else {
            return -1;
        }
    }

    public function hasWinner()
    {
        $sql = mysql_query("SELECT vref FROM fdata WHERE f99 = '100' and f99t = '40'");
        $winner = mysql_num_rows($sql);
        return ($winner > 0 ? true : false);
    }

    public function getVillageActiveArte($vref)
    {
        $q = 'SELECT * FROM artefacts WHERE `vref`=' . $vref . ' AND `status`=1 AND `conquered`<=' . (time() - max(86400 / SPEED, 600));
        return $this->query_return($q);
    }

    public function getAccountActiveArte($owner)
    {
        $q = 'SELECT * FROM artefacts WHERE `owner`=' . $owner . ' AND `status`=1 AND `conquered`<=' . (time() - max(86400 / SPEED, 600));
        return $this->query_return($q);
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
            $q = 'SELECT `vref`,`effect`,`aoe` FROM artefacts WHERE `owner`=' . $owner . ' AND `effecttype`=' . $type . ' AND `status`=1 AND `conquered`<=' . (time() - max(86400 / SPEED, 600)) . ' ORDER BY `conquered` DESC';
            $result = $this->query_return($q);
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
        $q = 'SELECT `id`,`size` FROM artefacts WHERE `type`=3 AND `status`=1 AND `conquered`<=' . (time() - max(86400 / SPEED, 600)) . ' AND lastupdate<=' . (time() - max(86400 / SPEED, 600));
        $result = $this->query_return($q);
        if (!empty($result) && count($result) > 0) {
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
                        if ($r['size'] == 1) {
                            $effect = rand(100, 500) / 100;
                        } else {
                            $effect = rand(100, 1000) / 100;
                        }
                        break;
                    case 4:
                        if ($r['size'] == 1) {
                            $effect = rand(100, 300) / 100;
                        } else {
                            $effect = rand(100, 600) / 100;
                        }
                        break;
                    case 5:
                        if ($r['size'] == 1) {
                            $effect = rand(100, 1000) / 100;
                        } else {
                            $effect = rand(100, 2000) / 100;
                        }
                        break;
                    case 6:
                        if ($r['size'] == 1) {
                            $effect = rand(50, 100) / 100;
                        } else {
                            $effect = rand(25, 100) / 100;
                        }
                        break;
                    case 7:
                        if ($r['size'] == 1) {
                            $effect = rand(100, 50000) / 100;
                        } else {
                            $effect = rand(100, 100000) / 100;
                        }
                        break;
                    case 8:
                        if ($r['size'] == 1) {
                            $effect = rand(50, 100) / 100;
                        } else {
                            $effect = rand(25, 100) / 100;
                        }
                        break;
                    case 9:
                        if ($r['size'] == 1) {
                            $effect = 1;
                        }
                        break;
                }
                if ($r['size'] == 1 && rand(1, 100) <= 50) {
                    $effect = 1 / $effect;
                }
                $q = 'UPDATE artefacts SET `effecttype`=' . $effecttype . ',`effect`=' . $effect . ',`aoe`=' . $aoe . ' WHERE `id`=' . $r['id'];

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
        $vinfo = $this->getVillage($wref);
        $owner = $vinfo['owner'];
        $q = 'SELECT `id` FROM artefacts WHERE `owner`=' . $owner . ' AND `effecttype`=11 AND `status`=1 AND `conquered`<=' . (time() - max(86400 / SPEED, 600)) . ' ORDER BY `conquered` DESC';
        $result = $this->query_return($q);
        if (!empty($result) && count($result) > 0) {
            return $artEff = 1;
        }
        return $artEff;
    }

    public function getArtEffAllyBP($userID)
    {
        $artEff = 0;
        $userAlli = $this->getUserField($userID, 'alliance', 0);
        $q = 'SELECT `alli1`,`alli2` FROM diplomacy WHERE alli1=' . $userAlli . ' OR alli2=' . $userAlli . ' AND accepted<>0';
        $diplos = $this->query_return($q);
        $diplos[] = array('alli1' => $userAlli, 'alli2' => $userAlli);
        if (!empty($diplos) && count($diplos) > 0) {
            $al = array();
            foreach ($diplos as $ds) {
                $al[] = $ds['alli1'];
                $al[] = $ds['alli2'];
            }
            $al = array_unique($al);
            $alstr = implode(',', $al);
            $q = 'SELECT `id` FROM users WHERE alliance IN (' . $alstr . ') AND id<>' . $userID;
            $mate = $this->query_return($q);
            if (!empty($mate) && count($mate) > 0) {
                $ml = array();
                foreach ($mate as $ms) {
                    $ml[] = $ms['id'];
                }
                $matestr = implode(',', $ml);
                $q = 'SELECT `id` FROM artefacts WHERE `owner` IN (' . $matestr . ') AND `effecttype`=11 AND `status`=1 AND `conquered`<=' . (time() - max(86400 / SPEED, 600)) . ' ORDER BY `conquered` DESC';
                $result = $this->query_return($q);
                if (!empty($result) && count($result) > 0) {
                    return $artEff = 1;
                }
            }
        }
        return $artEff;
    }

    public function modifyExtraVillage($worlID, $column, $value)
    {
        return $this->query("UPDATE vdata SET $column=$column+$value WHERE wref=$worlID");
    }

    public function modifyFieldLevel($worlID, $field, $level, $mode)
    {
        $b = 'f' . $field;
        if (!$mode) {
            return $this->query("UPDATE fdata SET $b=$b-$level WHERE vref=" . $worlID);
        }
        return $this->query("UPDATE fdata SET $b=$b+$level WHERE vref=" . $worlID);
    }

    public function modifyFieldType($worlID, $field, $type)
    {
        $b = 'f' . $field . 't';
        return $this->query("UPDATE fdata SET $b=$type WHERE vref=" . $worlID);
    }

    public function resendact($mail)
    {
        $q = "SELECT `email`, `username`, `password`, `id` from users WHERE email = " . $mail . " LIMIT 0,1";

        $dbarray = mysql_fetch_assoc($result);
        return $dbarray;
    }

    public function changemail($mail, $id)
    {
        $q = "UPDATE users set email= '$mail' WHERE id ='$id'";

    }

    public function register2($username, $password, $email, $act, $activateat)
    {
        $time = time();
        if (strtotime(START_TIME) > time()) {
            $time = strtotime(START_TIME);
        }
        $timep = ($time + PROTECTION);
        $rand = rand(8900, 9000);
        $q = "INSERT INTO users (username,password,access,email,timestamp,act,protect,fquest,clp,cp,reg2,activateat) VALUES ('$username', '$password', " . USER . ", '$email', $time, '$act', $timep, '0,0,0,0,0,0,0,0,0,0,0', '$rand', 1, 1,$activateat)";
        if (mysql_query($q, $this->connection)) {
            return mysql_insert_id($this->connection);
        } else {
            return false;
        }
    }

    public function checkname($id)
    {
        return $this->conn->select('username, email')->from('users')->where('id = :id', [':id' => $id])->first();
    }

    public function settribe($tribe, $userID)
    {
        return $this->conn->upgrade('users', ['tribe' => $tribe], 'id = '.$userID);
    }

    public function checkreg($userID)
    {
        return $this->conn->select('reg2')->from('users')->where('id = :id', [':id' => $userID])->first();
    }

    public function checkreg2($name)
    {
        return $this->conn->select('reg2')->from('users')->where('username = :username', [':username' => $name])->first();
    }

    public function checkid($name)
    {
        return $this->conn->select('id')->from('users')->where('username = :username', [':username' => $name])->first();
    }

    public function setreg2($userID)
    {
        $this->conn->from('users')->set('reg2', 0)->where('id = :id AND reg2 = 1', [':id' => $userID])->update();
    }

    public function getNotice5($userID)
    {
        return $this->conn->select('`id`')
            ->from('ndata')
            ->where('uid = :uid AND viewed = 0', [':uid' => $userID, 'viewed' => 0])
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
            if (date("j. M", $time) == date("j. M", $general['time'])) {
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
            if (date("j. M", $time) == date("j. M", $general['time'])) {
                $attack += 1;
            }
        }
        return $attack * 100;
    }

    public function getStatsInfo($userID, $time, $inf)
    {
        $users = $this->conn->select("{$inf}, time")->from('stats')->where('owner = :owner', [':owner' => $userID])->get();
        $t = 0;
        foreach ($users as $user) {
            if (date("j. M", $time) == date("j. M", $user['time'])) {
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
            1 => [$column => "$column + :value"],
            2 => [$column => "$column - :value"],
            default => [$column => ":value"],
        };
        $this->conn->upgrade('hero', $data, 'uid = :uid', [':uid' => $userID, ":value" => $value]);
    }

    public function createTradeRoute($userID, $worlID, $from, $r1, $r2, $r3, $r4, $start, $deliveries, $merchant, $time)
    {
        $this->conn->upgrade('users', ['gold' => 'gold - 2'], 'id = :id', [':id' => $userID]);

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
        return $this->conn->select('*')->from('route')->where('id = :id', [':id' => $id])->get();
    }

    public function getTradeRouteUid($id)
    {
        $result = $this->conn->select('`uid`')->from('route')->where('id = :id', [':id' => $id])->first();
        return $result['uid'];
    }

    public function editTradeRoute($id, $column, $value, $mode)
    {
        $data = [$column => ($mode ? $value : "{$column} + {$value}")];
        $this->conn->upgrade('route', $data, 'id = :id', [':id' => $id]);
    }

    public function deleteTradeRoute($id)
    {
        $this->conn->delete('route', 'id = :id', [':id' => $id]);
    }

    public function getHeroData($userID)
    {
        return $this->conn->select()->from('hero')->where('uid = :uid', [':uid' => $userID])->first();
    }

    public function getHeroData2($userID)
    {
        return $this->conn->select('heroid')
            ->from('hero')
            ->where('dead = 0 AND uid = :uid', [':uid' => $userID])
            ->limit(0, 1)
            ->first();
    }

    public function getHeroInVillid($userID, $mode)
    {
        $name = '';
        $villages = $this->conn->select('`wref`, `name`')
            ->from('vdata')
            ->where('owner = :owner', [':owner' => $userID])
            ->orderByDesc('owner')
            ->get();

        foreach ($villages as $village) {
            $unit = $this->conn->select('`hero`')
                ->from('units')
                ->where('vref = :vref', [':vref' => $village['wref']])
                ->orderByDesc('vref')
                ->first();

            if ($unit['hero'] == 1) {
                $name = $mode ? $village['name'] : $village['wref'];
            }

        }
        return $name;
    }

}
