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

    public function register($username, $password, $email, $tribe, $locate, $act)
    {
        $time = time();
        $stime = strtotime(START_DATE) - strtotime(date('m/d/Y')) + strtotime(START_TIME);
        if ($stime > time()) {
            $time = $stime;
        }
        $timep = $time + PROTECTION;

        $data = [
            'username' => $username,
            'password' => $password,
            'access' => USER,
            'email' => $email,
            'timestamp' => $time,
            'tribe' => $tribe,
            'location' => $locate,
            'act' => $act,
            'protect' => $timep,
            'fquest' => '0,0,0,0,0,0,0,0,0,0,0',
            'cp' => 1
        ];

        if ($this->conn->insert('users', $data)) {
            return $this->conn->getLastInsertId();
        } else {
            return false;
        }
    }

    public function activate($username, $password, $email, $tribe, $locate, $act, $act2)
    {
        $data = [
            'username' => $username,
            'password' => $password,
            'access' => USER,
            'email' => $email,
            'tribe' => $tribe,
            'timestamp' => time(),
            'location' => $locate,
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
        return $this->conn->delete('activate', 'username = :username', [':username' => $username]);
    }

    public function deleteReinf($id)
    {
        return $this->conn->delete('enforcement', 'id = :id', [':id' => $id]);
    }

    public function updateResource($vid, $what, $number)
    {
        return $this->conn->set($what, $number)->from('vdata')->where('wref = :vid', [':vid' => $vid])->update();
    }

    public function checkExist($ref, $mode)
    {
        $column = $mode ? 'email' : 'username';
        $result = $this->conn->select($column)->from('users')->where("$column = :ref", [':ref' => $ref])->limit(1)->get();
        return !empty($result);
    }

    public function checkExistActivate($ref, $mode)
    {
        $column = $mode ? 'email' : 'username';
        $result = $this->conn->select($column)->from('activate')->where("$column = :ref", [':ref' => $ref])->limit(1)->get();
        return !empty($result);
    }

    public function updateUserField($ref, $field, $value, $mode)
    {
        $condition = '';
        switch ($mode) {
            case 0:
                $condition = "username = :ref";
                break;
            case 1:
                $condition = "id = :ref";
                break;
            case 2:
                $condition = "id = :ref";
                $value = "$field + $value";
                break;
            case 3:
                $condition = "id = :ref";
                $value = "$field - $value";
                break;
        }

        return $this->conn->set($field, $value)->from('users')->where($condition, [':value' => $value, ':ref' => $ref])->update();
    }

    public function getSitee($uid)
    {
        return $this->conn->select('id')->from('users')->where("sit1 = :uid OR sit2 = :uid", [':uid' => $uid])->get();
    }

    public function getSitee1($uid)
    {
        $result = $this->conn->select()->from('users')->where("sit1 = :uid", [':uid' => $uid])->get();
        return $result[0] ?? null;
    }

    public function getSitee2($uid)
    {
        $result = $this->conn->select()->from('users')->where("sit2 = :uid", [':uid' => $uid])->get();
        return $result[0] ?? null;
    }

    public function removeMeSit($uid, $uid2)
    {
        $this->conn->set('sit1', 0)->from('users')->where('id = :uid AND sit1 = :uid2', [':uid' => $uid, ':uid2' => $uid2])->update();
        $this->conn->set('sit2', 0)->from('users')->where('id = :uid AND sit2 = :uid2', [':uid' => $uid, ':uid2' => $uid2])->update();
    }

    public function getUserField($ref, $field, $mode)
    {
        $column = !$mode ? 'id' : 'username';
        $result = $this->conn->select($field)->from('users')->where("$column = :ref", [':ref' => $ref])->get();
        return $result[$field] ?? null;
    }

    public function getInvitedUser($uid)
    {
        return $this->conn->select()->from('users')->where("invited = :uid", [':uid' => $uid])->orderByDesc('regtime')->get();
    }

    public function getStarvation()
    {
        return $this->conn->select()->from('vdata')->where('starv != 0')->get();
    }

    public function getActivateField($ref, $field, $mode)
    {
        $condition = $mode ? "username = :ref" : "id = :ref";
        $result = $this->conn->select($field)->from('activate')->where($condition, [':ref' => $ref])->get();
        return $result[0][$field] ?? null;
    }

    public function login($username, $password)
    {
        $result = $this->conn->select('password, sessid')->from('users')->where('username = :username', [':username' => $username])->get();

        if ($result && count($result) > 0) {
            if ($result['password'] === md5($password)) {
                return true;
            }
        }
        return false;
    }

    public function checkActivate($act)
    {
        $result = $this->conn->select()->from('activate')->where('act = :act', [':act' => $act])->get();
        return $result[0] ?? null;
    }

    public function sitterLogin($username, $password)
    {
        $result = $this->conn->select('sit1, sit2')->from('users')->where('username = :username AND access != :banned', [':username' => $username, ':banned' => BANNED])->get();

        if ($result && count($result) > 0) {
            $dbarray = $result[0];
            $sit1 = $dbarray['sit1'];
            $sit2 = $dbarray['sit2'];

            if ($sit1 != 0) {
                $result_sit1 = $this->conn->select('password')->from('users')->where('id = :sit1 AND access != :banned', [':sit1' => $sit1, ':banned' => BANNED])->get();
                $pw_sit1 = $result_sit1[0];
            }

            if ($sit2 != 0) {
                $result_sit2 = $this->conn->select('password')->from('users')->where('id = :sit2 AND access != :banned', [':sit2' => $sit2, ':banned' => BANNED])->get();
                $pw_sit2 = $result_sit2[0];
            }

            if ($sit1 != 0 || $sit2 != 0) {
                if ($pw_sit1['password'] == md5($password) || $pw_sit2['password'] == md5($password)) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    public function setDeleting($uid, $mode)
    {
        if (!$mode) {
            $this->conn->insert('deleting', ['uid' => $uid, 'time' => time() + 72 * 3600]);
        } else {
            $this->conn->delete('deleting', 'uid = :uid', [':uid' => $uid]);
        }
    }

    public function isDeleting($uid)
    {
        $result = $this->conn->select('time')->from('deleting')->where('uid = :uid', [':uid' => $uid])->get();
        return $result['time'] ?? null;
    }

    public function modifyGold($userid, $amt, $mode)
    {
        if (!$mode) {
            return $this->conn->decrement('gold', $amt)->from('users')->where('id = :userid', [':userid' => $userid])->update();
        } else {
            return $this->conn->increment('gold', $amt)->from('users')->where('id = :userid', [':userid' => $userid])->update();
        }
    }

    public function getUserArray($ref, $mode)
    {
        if (!$mode) {
            $result = $this->conn->select()->from('users')->where('username = :ref', [':ref' => $ref])->get();
        } else {
            $result = $this->conn->select()->from('users')->where('id = :ref', [':ref' => $ref])->get();
        }

        return $result[0] ?? null;
    }

    public function getUserWithEmail($email)
    {
        $result = $this->conn->select()->from('users')->where('email = :email', [':email' => $email])->get();
        return $result[0] ?? null;
    }

    public function activeModify($username, $mode)
    {
        $time = time();
        if (!$mode) {
            return $this->conn->insert('active', ['username' => $username, 'time' => $time]);
        } else {
            return $this->conn->delete('active', 'username = :username', [':username' => $username]);
        }
    }

    public function addActiveUser($username, $time)
    {
        $this->conn->replace('active', ['username' => $username, 'time' => $time]);
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
            ->get();
        return $result ? $result[0]['sitter'] : null;
    }

    public function conquerOasis($vref, $wref)
    {
        $villageInfo = $this->getVillage($vref);
        $uid = $villageInfo['owner'];
        $currentTime = time();

        $data = [
            'conqured' => $vref,
            'loyalty' => 100,
            'lastupdated' => $currentTime,
            'lastupdated2' => $currentTime,
            'owner' => $uid,
            'name' => 'Occupied Oasis'
        ];

        return $this->conn->from('odata')->where('wref = :wref', [':wref' => $wref])->update();
    }

    public function modifyOasisLoyalty($wref)
    {
        if ($this->isVillageOases($wref) != 0) {
            $OasisInfo = $this->getOasisInfo($wref);
            if ($OasisInfo['conqured'] != 0) {
                $loyaltyAmendment = floor(100 / min(3, (4 - $this->villageOasisCount($OasisInfo['conqured']))));

                $this->conn->from('odata')
                    ->decrement('loyalty', $loyaltyAmendment)
                    ->where('wref = :wref', [':wref' => $wref])
                    ->update();

            }
        }
    }

    public function checkActiveSession($username, $sessid)
    {
        $user = $this->getUserArray($username, 0);
        $sessidarray = explode("+", $user['sessid']);
        return in_array($sessid, $sessidarray);
    }

    public function submitProfile($uid, $gender, $location, $birthday, $des1, $des2)
    {
        return $this->conn->from('users')
            ->set('gender', $gender)
            ->set('location', $location)
            ->set('birthday', $birthday)
            ->set('desc1', $des1)
            ->set('desc2', $des2)
            ->where('id = :uid', [':uid' => $uid])
            ->update();
    }

    public function gpack($uid, $gpack)
    {
        return $this->conn->from('users')
            ->set('gpack', $gpack)
            ->where('id = :uid', [':uid' => $uid])
            ->update();
    }

    public function updateOnline($mode, $name = "", $sit = 0)
    {
        if ($mode == "login") {
            return $this->conn->insert('online', ['IGNORE'], ['name' => $name, 'time' => time(), 'sitter' => $sit]);
        } else {
            return $this->conn->delete('online', 'name = :username', [':username' => $name]);
        }
    }

    public function generateBase($sector)
    {
        $sector = ($sector == 0) ? rand(1, 4) : $sector;
        $world_max = setting('world_max');

        // (-/-) SW
        if ($sector == 1) {
            $x_a = ($world_max - ($world_max * 2));
            $x_b = 0;
            $y_a = ($world_max - ($world_max * 2));
            $y_b = 0;
            $order = "ORDER BY y DESC, x DESC";
            $mmm = rand(-1, -20);
            $x_y = "AND x < -4 AND y < $mmm";
        }
        // (+/-) SE
        elseif ($sector == 2) {
            $x_a = ($world_max - ($world_max * 2));
            $x_b = 0;
            $y_a = 0;
            $y_b = $world_max;
            $order = "ORDER BY y ASC, x DESC";
            $mmm = rand(1, 20);
            $x_y = "AND x < -4 AND y > $mmm";
        }
        // (+/+) NE
        elseif ($sector == 3) {
            $x_a = 0;
            $x_b = $world_max;
            $y_a = 0;
            $y_b = $world_max;
            $order = "ORDER BY y, x ASC";
            $mmm = rand(1, 20);
            $x_y = "AND x > 4 AND y > $mmm";
        }
        // (-/+) NW
        elseif ($sector == 4) {
            $x_a = 0;
            $x_b = $world_max;
            $y_a = ($world_max - ($world_max * 2));
            $y_b = 0;
            $order = "ORDER BY y DESC, x ASC";
            $mmm = rand(-1, -20);
            $x_y = "AND x > 4 AND y < $mmm";
        }

        $result = $this->conn
            ->select('id')
            ->from('wdata')
            ->where("fieldtype = 3 AND occupied = 0 $x_y AND (x BETWEEN $x_a AND $x_b) AND (y BETWEEN $y_a AND $y_b)")
            ->order($order)
            ->limit(20)
            ->get();

        return $result['id'];
    }

    public function setFieldTaken($id)
    {
        $this->conn->from('wdata')->set('occupied', 1)->where('id = :id', [':id' => $id])->update();
    }

    public function addVillage($worlid, $uid, $username, $capital)
    {
        $total = count($this->getVillagesID($uid));
        $vname = $total >= 1 ? $username . "'s village " . ($total + 1) : $username . "'s village";

        $time = time();

        $data = [
            'wref' => $worlid,
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
            'maxstore' => 800,
            'crop' => 780,
            'maxcrop' => 800,
            'lastupdate' => $time,
            'created' => $time
        ];

        $this->conn->insert('vdata', $data);
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

    public function isVillageOases($wref)
    {
        $result = $this->conn
            ->select('oasistype')
            ->from('wdata')
            ->where('id = :wref', [':wref' => $wref])
            ->get();
        return $result['oasistype'];
    }

    public function villageOasisCount($vref)
    {
        $result = $this->conn
            ->select('COUNT(*)')
            ->from('odata')
            ->where('conquered = :vref', [':vref' => $vref])
            ->get();

        if ($result && isset($result[0])) {
            return $result[0]['count(*)'];
        }
        return 0;
    }

    public function populateOasis()
    {
        $rows = $this->conn
            ->select()
            ->from('wdata')
            ->where('oasistype != 0')
            ->get();

        foreach ($rows as $row) {
            $this->addUnits($row['id']);
        }
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

    public function getVillageData($wref)
    {
        return $this->conn
            ->select('*')
            ->from('wdata')
            ->where('id = :wref', [':wref' => $wref])
            ->get();
    }

    public function getVillageType2($wref)
    {
        $result = $this->conn
            ->select('oasistype')
            ->from('wdata')
            ->where('id = :wref', [':wref' => $wref])
            ->get();

        return $result['oasistype'];
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
            ->get();

        return !empty($result);
    }

    public function oasischecker($x, $y)
    {
        return $this->conn
            ->select('*')
            ->from('wdata')
            ->where('x = :x AND y = :y', [':x' => $x, ':y' => $y])
            ->get();
    }

    public function getVillageState($wref)
    {
        $result = $this->conn->select('oasistype, occupied')->from('wdata')->where('id = :wref', [':wref' => $wref])->first();
        return $result['occupied'] != 0 || $result['oasistype'] != 0;
    }

    public function getProfileVillages($uid)
    {
        $result = $this->conn
            ->select('wref, maxstore, maxcrop, pop, name, capital')
            ->from('vdata')
            ->where('owner = :uid', [':uid' => $uid])
            ->orderByDesc('pop, desc')
            ->first();

        return $result;
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

    public function getVillagesID($uid)
    {
        $results = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('owner = :uid', [':uid' => $uid])
            ->get();

        return array_column($results, 'wref');
    }

    public function getVillagesID2($uid)
    {
        $result = $this->conn
            ->select('wref')
            ->from('vdata')
            ->where('owner = :uid', [':uid' => $uid])
            ->orderByDesc('capital')
            ->orderByDesc('pop')
            ->get();

        $array = [];
        foreach ($result as $row) {
            $array[] = $row['wref'];
        }

        return $array;
    }

    public function getVillage($vid)
    {
        return $this->conn
            ->select()
            ->from('vdata')
            ->where('wref = :vid', [':vid' => $vid])
            ->get();
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

    public function getMInfo($id)
    {
        return $this->conn->leftJoin('wdata', 'vdata', 'vdata.wref = wdata.id', '*', 'wdata.id = :id', [':id' => $id]);
    }

    public function getOMInfo($id)
    {
        return $this->conn->leftJoin('wdata', 'odata', 'odata.wref = wdata.id', '*', 'wdata.id = :id', [':id' => $id]);
    }

    public function getOasis($vid)
    {
        return $this->conn
            ->select()
            ->from('odata')
            ->where('conqured = :vid', [':vid' => $vid])
            ->get();
    }

    public function getOasisInfo($worlid)
    {
        return $this->conn
            ->select('conquered, loyalty')
            ->from('odata')
            ->where('wref = :wid', [':wid' => $worlid])
            ->get();
    }

    public function getVillageField($ref, $field)
    {
        $result = $this->conn
            ->select($field)
            ->from('vdata')
            ->where('wref = :ref', [':ref' => $ref])
            ->get();
        return $result[$field];
    }

    public function getOasisField($ref, $field)
    {
        $result = $this->conn
            ->select($field)
            ->from('odata')
            ->where('wref = :ref', [':ref' => $ref])
            ->get();
        return $result[$field];
    }

    //parei aqui
    public function setVillageField($ref, $field, $value)
    {
        return $this->conn
            ->from('vdata')
            ->set($field, $value)
            ->where('wref = :ref', [':ref' => $ref])
            ->update();
    }

    public function setVillageLevel($ref, $field, $value)
    {
        return $this->conn
            ->from('fdata')
            ->set($field, $value)
            ->where('vref = :ref', [':ref' => $ref])
            ->update();
    }

    public function getResourceLevel($vid)
    {
        return $this->conn
            ->select()
            ->from('fdata')
            ->where('vref = :vid', [':vid' => $vid])
            ->get();
    }

    public function getAdminLog()
    {
        return $this->conn
            ->select('id, user, log, time')
            ->from('admin_log')
            ->where('id != :id', [':id' => 0])
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function delAdminLog($id)
    {
        return $this->conn->delete('admin_log', 'id = :id', [':id' => $id]);
    }

    public function getCoor($wref)
    {
        return $this->conn
            ->select('x, y')
            ->from('wdata')
            ->where('id = :wref', [':wref' => $wref])
            ->get();
    }

    public function checkForum($id)
    {
        $result = $this->conn
            ->select()
            ->from('forum_cat')
            ->where('alliance = :id', [':id' => $id])
            ->get();
        return !empty($result);
    }

    public function countCat($id)
    {
        $result = $this->conn
            ->select('COUNT(id)')
            ->from('forum_topic')
            ->where('cat = :id', [':id' => $id])
            ->get();
        return $result['COUNT(id)'];
    }

    public function lastTopic($id)
    {
        return $this->conn
            ->select()
            ->from('forum_topic')
            ->where('cat = :id', [':id' => $id])
            ->orderByDesc('post_date')
            ->get();
    }

    public function checkLastTopic($id)
    {
        $result = $this->conn
            ->select()
            ->from('forum_topic')
            ->where('cat = :id', [':id' => $id])
            ->get();
        return !empty($result);
    }

    public function checkLastPost($id)
    {
        $result = $this->conn
            ->select()
            ->from('forum_post')
            ->where('topic = :id', [':id' => $id])
            ->get();
        return !empty($result);
    }

    public function lastPost($id)
    {
        return $this->conn
            ->select()
            ->from('forum_post')
            ->where('topic = :id', [':id' => $id])
            ->get();
    }

    public function countTopic($id)
    {
        $result1 = $this->conn
            ->select('count(id)')
            ->from('forum_post')
            ->where('owner = :id', [':id' => $id])
            ->get();

        $result2 = $this->conn
            ->select('count(id)')
            ->from('forum_topic')
            ->where('owner = :id', [':id' => $id])
            ->get();

        $total1 = $result1['count(id)'] ?? 0;
        $total2 = $result2['count(id)'] ?? 0;

        return $total1 + $total2;
    }

    public function countPost($id)
    {
        $result = $this->conn
            ->select('count(id)')
            ->from('forum_post')
            ->where('topic = :id', [':id' => $id])
            ->get();

        return $result['count(id)'] ?? 0;
    }

    public function forumCat($id)
    {
        return $this->conn
            ->select()
            ->from('forum_cat')
            ->where('alliance = :id', [':id' => $id])
            ->orderByDesc('id')
            ->get();
    }

    public function forumCatEdit($id)
    {
        return $this->conn
            ->select()
            ->from('forum_cat')
            ->where('id = :id', [':id' => $id])
            ->get();
    }

    public function forumCatAlliance($id)
    {
        $result = $this->conn
            ->select('alliance')
            ->from('forum_cat')
            ->where('id = :id', [':id' => $id])
            ->get();

        return $result['alliance'] ?? null;
    }

    public function forumCatName($id)
    {
        $result = $this->conn
            ->select('forum_name')
            ->from('forum_cat')
            ->where('id = :id', [':id' => $id])
            ->get();

        return $result['forum_name'] ?? null;
    }

    public function checkCatTopic($id)
    {
        $result = $this->conn
            ->select()
            ->from('forum_topic')
            ->where('cat = :id', [':id' => $id])
            ->get();

        return $result ? true : false;
    }

    public function checkResultEdit($alli)
    {
        $result = $this->conn
            ->select()
            ->from('forum_edit')
            ->where('alliance = :alli', [':alli' => $alli])
            ->get();

        return $result ? true : false;
    }

    public function checkCloseTopic($id)
    {
        $result = $this->conn
            ->select('close')
            ->from('forum_topic')
            ->where('id = :id', [':id' => $id])
            ->get();

        return $result['close'] ?? null;
    }

    public function checkEditRes($alli)
    {
        $result = $this->conn
            ->select('result')
            ->from('forum_edit')
            ->where('alliance = :alli', [':alli' => $alli])
            ->get();

        return $result['result'] ?? null;
    }

    public function createResultEdit($alli, $result)
    {
        $this->conn->insert('forum_edit', ['alliance' => $alli, 'result' => $result]);
        return $this->conn->lastInsertId();
    }

    public function updateResultEdit($alli, $result)
    {
        return $this->conn->from('forum_edit')
            ->set('result', $result)
            ->where('alliance = :alli', [':alli' => $alli])
            ->update();
    }

    public function updateEditTopic($id, $title, $cat)
    {
        return $this->conn
            ->from('forum_topic')
            ->set('title', $title)
            ->set('cat', $cat)
            ->where('id = :id', [':id' => $id])
            ->update();
    }

    public function updateEditForum($id, $name, $des)
    {
        return $this->conn
            ->from('forum_cat')
            ->set('forum_name', $name)
            ->set('forum_des', $des)
            ->where('id = :id', [':id' => $id])
            ->update();
    }

    public function stickTopic($id, $mode)
    {
        return $this->conn
            ->from('forum_topic')
            ->set('stick', $mode)
            ->where('id = :id', [':id' => $id])
            ->update();
    }

    public function forumCatTopic($id)
    {
        return $this->conn
            ->select()
            ->from('forum_topic')
            ->where('cat = :id AND stick = ""', [':id' => $id])
            ->orderByDesc('post_date')
            ->get();
    }

    public function forumCatTopicStick($id)
    {
        return $this->conn
            ->select()
            ->from('forum_topic')
            ->where('cat = :id AND stick = :stick', [':id' => $id, ':stick' => 1])
            ->orderByDesc('post_date')
            ->get();
    }

    public function showTopic($id)
    {
        return $this->conn
            ->select()
            ->from('forum_topic')
            ->where('id = :id', [':id' => $id])
            ->get();
    }

    public function showPost($id)
    {
        return $this->conn
            ->select()
            ->from('forum_post')
            ->where('topic = :id', [':id' => $id])
            ->get();
    }

    public function showPostEdit($id)
    {
        return $this->conn
            ->select()
            ->from('forum_post')
            ->where('id = :id', [':id' => $id])
            ->get();
    }

    public function creatForum($owner, $alli, $name, $des, $area)
    {
        $data = [
            'owner' => $owner,
            'alliance' => $alli,
            'forum_name' => $name,
            'forum_des' => $des,
            'forum_area' => $area
        ];
        return $this->conn->insert('forum_cat', $data);
    }

    public function creatTopic($title, $post, $cat, $owner, $alli, $ends, $alliance, $player, $coor, $report)
    {
        $data = [
            'title' => $title,
            'post' => $post,
            'post_date' => time(),
            'last_post' => time(),
            'cat' => $cat,
            'owner' => $owner,
            'alliance' => $alli,
            'sticky' => '',
            'closed' => '',
            'edited' => '',
            'edit_user' => $alliance,
            'edit_date' => $player,
            'edit_rea' => $coor,
            'edit_topic' => $report
        ];
        return $this->conn->insert('forum_topic', $data);
    }

    public function creatPost($post, $tids, $owner, $alliance, $player, $coor, $report)
    {
        $date = time();
        $data = [
            'post' => $post,
            'topic' => $tids,
            'owner' => $owner,
            'post_date' => $date,
            'alliance' => $alliance,
            'player' => $player,
            'coor' => $coor,
            'report' => $report
        ];
        return $this->conn->insert('forum_post', $data);
    }

    public function updatePostDate($id)
    {
        $date = time();
        return $this->conn->update('forum_topic', ['post_date' => $date], 'id = :id', [':id' => $id]);
    }

    public function editUpdateTopic($id, $post, $alliance, $player, $coor, $report)
    {
        return $this->conn
            ->from('forum_topic')
            ->set('post', $post)
            ->set('alliance0', $alliance)
            ->set('player0', $player)
            ->set('coor0', $coor)
            ->set('report0' , $report)
            ->where('id = :id', [':id' => $id])
            ->update();
    }

    public function editUpdatePost($id, $post, $alliance, $player, $coor, $report)
    {
        return $this->conn->from('forum_post')
            ->set('post', $post)
            ->set('alliance0', $alliance)
            ->set('player0', $player)
            ->set('coor0', $coor)
            ->set('report0', $report)
            ->where('id = :id', [':id' => $id])
            ->update();
    }

    public function lockTopic($id, $mode)
    {
        return $this->conn->from('forum_topic')
            ->set('close', $mode)
            ->where('id = :id', [':id' => $id])
            ->update();
    }

    public function deleteCat($id)
    {
        $this->conn->delete('forum_cat', 'id = :id', [':id' => $id]);
        $this->conn->delete('forum_topic', 'cat = :id', [':id' => $id]);
    }

    public function deleteTopic($id)
    {
        return $this->conn->delete('forum_topic', 'id = :id', [':id' => $id]);
    }

    public function deletePost($id)
    {
        return $this->conn->delete('forum_post', 'id = :id', [':id' => $id]);
    }

    public function getAllianceName($id)
    {
        $result = $this->conn
            ->select('tag')
            ->from('alidata')
            ->where('id = :id', [':id' => $id])
            ->get();
        return $result ? $result['tag'] : false;
    }

    public function getAlliancePermission($ref, $field, $mode)
    {
        if (!$mode) {
            return $this->conn
                ->select($field)
                ->from('ali_permission')
                ->where('uid = :ref', [':ref' => $ref])
                ->get();
        } else {
            return $this->conn
                ->select($field)
                ->from('ali_permission')
                ->where('username = :ref', [':ref' => $ref])
                ->get();
        }
    }

    public function getAlliance($id)
    {
        return $this->conn
            ->select('*')
            ->from('alidata')
            ->where('id = :id', [':id' => $id])
            ->get();
    }

    public function setAlliName($aid, $name, $tag)
    {
        return $this->conn->from('alidata')->set('name', $name)->set('tag', $tag)->where('id = :aid', [':aid' => $aid])->update();
    }

    public function isAllianceOwner($id)
    {
        $result = $this->conn
            ->select()
            ->from('alidata')
            ->where('leader = :id', [':id' => $id])
            ->get();
        return count($result) > 0;
    }

    public function aExist($ref, $type)
    {
        $result = $this->conn
            ->select($type)
            ->from('alidata')
            ->where("$type = :ref", [':ref' => $ref])
            ->get();
        return count($result) > 0;
    }

    public function modifyPoints($aid, $points, $amt)
    {
        return $this->conn->from('users')->set($points, $points + $amt)->where('id = :aid', [':aid' => $aid])->update();
    }

    public function modifyPointsAlly($aid, $points, $amt)
    {
        return $this->conn->from('alidata')->set($points, $points + $amt)->where('id = :aid', [':aid' => $aid])->update();
    }

    public function createAlliance($tag, $name, $uid, $max)
    {
        $data = [
            'name' => $name,
            'tag' => $tag,
            'leader' => $uid,
            'max' => $max
        ];
        return $this->conn->insert('alidata', $data);
    }

    public function procAllyPop($aid)
    {
        $ally = $this->getAlliance($aid);
        $memberlist = $this->getAllMember($ally['id']);
        $oldrank = 0;
        foreach ($memberlist as $member) {
            $oldrank += $this->getVSumField($member['id'], 'pop');
        }
        if ($ally['oldrank'] != $oldrank) {
            if ($ally['oldrank'] < $oldrank) {
                $totalpoints = $oldrank - $ally['oldrank'];
                $this->addclimberrankpopAlly($ally['id'], $totalpoints);
                $this->updateoldrankAlly($ally['id'], $oldrank);
            } else if ($ally['oldrank'] > $oldrank) {
                $totalpoints = $ally['oldrank'] - $oldrank;
                $this->removeclimberrankpopAlly($ally['id'], $totalpoints);
                $this->updateoldrankAlly($ally['id'], $oldrank);
            }
        }
    }

    public function insertAlliNotice($aid, $notice)
    {
        $data = [
            'aid' => $aid,
            'notice' => $notice,
            'time' => time()
        ];
        return $this->conn->insert('ali_log', $data);
    }

    public function deleteAlliance($aid)
    {
        $result = $this->conn->select('alliance')->from('users')->where('alliance = :aid', [':aid' => $aid])->get();
        $num_rows = count($result);
        if ($num_rows == 0) {
            $this->conn->delete('alidata', 'id = :id', [':id' => $aid]);
        }
    }

    public function readAlliNotice($aid)
    {
        return $this->conn
            ->select()
            ->from('ali_log')
            ->where('aid = :aid', [':aid' => $aid])
            ->orderByDesc('date')
            ->get();
    }

    public function createAlliPermissions($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8)
    {
        $data = [
            'uid' => $uid,
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

        return $this->conn->insert('ali_permission', $data);
    }

    public function deleteAlliPermissions($uid)
    {
        return $this->conn->delete('ali_permission', 'uid = :uid', [':uid' => $uid]);
    }

    public function updateAlliPermissions($uid, $aid, $rank, $opt1, $opt2, $opt3, $opt4, $opt5, $opt6, $opt7, $opt8)
    {
        return $this->conn
            ->from('ali_permission')
            ->where('uid = :uid AND alliance = :aid', [':uid' => $uid, ':aid' => $aid])
            ->set('rank', $rank)
            ->set('opt1', $opt1)
            ->set('opt2', $opt2)
            ->set('opt3', $opt3)
            ->set('opt4', $opt4)
            ->set('opt5', $opt5)
            ->set('opt6', $opt6)
            ->set('opt7', $opt7)
            ->set('opt8', $opt8)
            ->update();
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
        return $this->conn->from('alidata')
            ->set('notice', $notice)
            ->set('desc', $desc)
            ->where('id = :aid', [':aid' => $aid])
            ->update();
    }

    public function diplomacyInviteAdd($alli1, $alli2, $type)
    {
        $data = [
            'alli1' => $alli1,
            'alli2' => $alli2,
            'type' => $type,
            'accepted' => 0
        ];

        return $this->conn->insert('diplomacy', $data);
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
        $result = $this->conn
            ->select('id')
            ->from('alidata')
            ->where('tag = :name', [':name' => $this->removeXSS($name)])
            ->first();
        return $result['id'];
    }

    public function getDiplomacy($aid)
    {
        return $this->conn
            ->select()
            ->from('diplomacy')
            ->where('id = :aid', [':aid' => $aid])
            ->get();
    }

    //TODO: arrumar apartir daqui
    public function diplomacyCancelOffer($id)
    {
        return $this->conn
            ->delete('diplomacy', 'id = :id', [':id' => $id]);
    }

    public function diplomacyInviteAccept($id, $session_alliance)
    {
        return $this->conn
            ->update('diplomacy', ['accepted' => 1], 'id = :id AND alli2 = :session_alliance', [':id' => $id, ':session_alliance' => $session_alliance]);
    }

    public function diplomacyInviteDenied($id, $session_alliance)
    {
        return $this->conn
            ->delete('diplomacy', 'id = :id AND alli2 = :session_alliance', [':id' => $id, ':session_alliance' => $session_alliance]);
    }

    function diplomacyInviteCheck($session_alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli2 = :session_alliance AND accepted = 0')
            ->bind([':session_alliance' => $session_alliance])
            ->fetchAll();
    }

    function diplomacyExistingRelationships($session_alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli2 = :session_alliance AND accepted = 1')
            ->bind([':session_alliance' => $session_alliance])
            ->fetchAll();
    }

    function diplomacyExistingRelationships2($session_alliance)
    {
        return $this->conn
            ->select('*')
            ->from('diplomacy')
            ->where('alli1 = :session_alliance AND accepted = 1')
            ->bind([':session_alliance' => $session_alliance])
            ->fetchAll();
    }

    function diplomacyCancelExistingRelationship($id, $session_alliance)
    {
        return $this->conn
            ->delete('diplomacy', 'id = :id AND alli2 = :session_alliance', [':id' => $id, ':session_alliance' => $session_alliance]);
    }

    function getUserAlliance($id)
    {
        $q = $this->conn->select("alidata.tag")
            ->from("users")
            ->join("alidata", "users.alliance", "=", "alidata.id")
            ->where("users.id", "=", $id)
            ->execute()
            ->fetchColumn();
        return $q ? $q : "-";
    }

    function modifyResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        if (!$mode) {
            return $this->conn->update("vdata")
                ->set([
                    'wood' => "vdata.wood - :wood",
                    'clay' => "vdata.clay - :clay",
                    'iron' => "vdata.iron - :iron",
                    'crop' => "vdata.crop - :crop"
                ])
                ->where('wref', '=', $vid)
                ->execute([':wood' => $wood, ':clay' => $clay, ':iron' => $iron, ':crop' => $crop]);
        } else {
            return $this->conn->update("vdata")
                ->set([
                    'wood' => "vdata.wood + :wood",
                    'clay' => "vdata.clay + :clay",
                    'iron' => "vdata.iron + :iron",
                    'crop' => "vdata.crop + :crop"
                ])
                ->where('wref', '=', $vid)
                ->execute([':wood' => $wood, ':clay' => $clay, ':iron' => $iron, ':crop' => $crop]);
        }
    }

    function modifyOasisResource($vid, $wood, $clay, $iron, $crop, $mode)
    {
        if (!$mode) {
            return $this->conn->update("odata")
                ->set([
                    'wood' => "odata.wood - :wood",
                    'clay' => "odata.clay - :clay",
                    'iron' => "odata.iron - :iron",
                    'crop' => "odata.crop - :crop"
                ])
                ->where('wref', '=', $vid)
                ->execute([':wood' => $wood, ':clay' => $clay, ':iron' => $iron, ':crop' => $crop]);
        } else {
            return $this->conn->update("odata")
                ->set([
                    'wood' => "odata.wood + :wood",
                    'clay' => "odata.clay + :clay",
                    'iron' => "odata.iron + :iron",
                    'crop' => "odata.crop + :crop"
                ])
                ->where('wref', '=', $vid)
                ->execute([':wood' => $wood, ':clay' => $clay, ':iron' => $iron, ':crop' => $crop]);
        }
    }

    function getFieldLevel($vid, $field)
    {
        $q = $this->conn->select("f$field")
            ->from("fdata")
            ->where('vref', '=', $vid)
            ->execute()
            ->fetchColumn();
        return $q ? $q : false;
    }

    function getFieldType($vid, $field)
    {
        $q = $this->conn->select("f$field" . "t")
            ->from("fdata")
            ->where('vref', '=', $vid)
            ->execute()
            ->fetchColumn();
        return $q ? $q : false;
    }

    function getVSumField($uid, $field)
    {
        $q = $this->conn->select("SUM($field)")
            ->from("vdata")
            ->where('owner', '=', $uid)
            ->execute()
            ->fetchColumn();
        return $q ? $q : false;
    }

    function updateVillage($vid)
    {
        $time = time();
        return $this->conn->update("vdata")
            ->set(['lastupdate' => $time])
            ->where('wref', '=', $vid)
            ->execute();
    }

    function updateOasis($vid)
    {
        $time = time();
        return $this->conn->update("odata")
            ->set(['lastupdated' => $time])
            ->where('wref', '=', $vid)
            ->execute();
    }

    function updateOasis2($vid)
    {
        $time = time();
        return $this->conn->update("odata")
            ->set(['lastupdated2' => $time])
            ->where('wref', '=', $vid)
            ->execute();
    }

    function setVillageName($vid, $name)
    {
        return $this->conn->update("vdata")
            ->set(['name' => $name])
            ->where('wref', '=', $vid)
            ->execute();
    }

    function modifyPop($vid, $pop, $mode)
    {
        $field = (!$mode) ? 'pop + ' : 'pop - ';
        return $this->conn->update("vdata")
            ->set(['pop' => "pop $field $pop"])
            ->where('wref', '=', $vid)
            ->execute();
    }

    function addCP($ref, $cp)
    {
        return $this->conn->update("vdata")
            ->set(['cp' => "cp + $cp"])
            ->where('wref', '=', $ref)
            ->execute();
    }

    function addCel($ref, $cel, $type)
    {
        return $this->conn->update("vdata")
            ->set(['celebration' => $cel, 'type' => $type])
            ->where('wref', '=', $ref)
            ->execute();
    }

    function getCel()
    {
        $time = time();
        $q = $this->conn->select("*")
            ->from("vdata")
            ->where('celebration', '<', $time)
            ->where('celebration', '!=', 0)
            ->execute();
        return $this->mysql_fetch_all($q);
    }

    function clearCel($ref)
    {
        return $this->conn->update("vdata")
            ->set(['celebration' => 0, 'type' => 0])
            ->where('wref', '=', $ref)
            ->execute();
    }

    function setCelCp($user, $cp)
    {
        return $this->conn->update("users")
            ->set(['cp' => "cp + $cp"])
            ->where('id', '=', $user)
            ->execute();
    }

    function clearExpansionSlot($id)
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->conn->update("vdata")
                ->set(['exp' . $i => 0])
                ->where('exp' . $i, '=', $id)
                ->execute();
        }
    }

    function getInvitation($uid)
    {
        return $this->conn->select("*")
            ->from("ali_invite")
            ->where('uid', '=', $uid)
            ->execute();
    }

    function getInvitation2($uid, $aid)
    {
        return $this->conn->select("*")
            ->from("ali_invite")
            ->where('uid', '=', $uid)
            ->where('alliance', '=', $aid)
            ->execute();
    }

    function getAliInvitations($aid)
    {
        return $this->conn->select("*")
            ->from("ali_invite")
            ->where('alliance', '=', $aid)
            ->where('accept', '=', 0)
            ->execute();
    }

    function sendInvitation($uid, $alli, $sender)
    {
        $time = time();
        return $this->conn->insert("ali_invite")
            ->values(['uid' => $uid, 'alliance' => $alli, 'sender' => $sender, 'time' => $time, 'accept' => 0])
            ->execute();
    }

    function removeInvitation($id)
    {
        return $this->conn->delete("ali_invite")
            ->where('id', '=', $id)
            ->execute();
    }

    function delNotice($id, $uid)
    {
        return $this->conn->delete("ndata")
            ->where('id', '=', $id)
            ->where('uid', '=', $uid)
            ->execute();
    }

    function sendMessage($client, $owner, $topic, $message, $send, $alliance, $player, $coor, $report)
    {
        $time = time();
        return $this->conn->insert("mdata")
            ->values([
                'client' => $client,
                'owner' => $owner,
                'topic' => $topic,
                'message' => $message,
                'send' => $send,
                'time' => $time,
                'alliance' => $alliance,
                'player' => $player,
                'coor' => $coor,
                'report' => $report
            ])
            ->execute();
    }

    function setArchived($id)
    {
        return $this->conn->update("mdata")
            ->set(['archived' => 1])
            ->where('id', '=', $id)
            ->execute();
    }

    function setNorm($id)
    {
        return $this->conn->update("mdata")
            ->set(['archived' => 0])
            ->where('id', '=', $id)
            ->execute();
    }

    function getMessage($id, $mode)
    {
        switch ($mode) {
            case 1:
                return $this->conn->select()
                    ->from("mdata")
                    ->where('target', '=', $id)
                    ->where('send', '=', 0)
                    ->where('archived', '=', 0)
                    ->orderBy('time', 'DESC')
                    ->execute();
            case 2:
                return $this->conn->select()
                    ->from("mdata")
                    ->where('owner', '=', $id)
                    ->orderBy('time', 'DESC')
                    ->execute();
            case 3:
                return $this->conn->select()
                    ->from("mdata")
                    ->where('id', '=', $id)
                    ->execute();
            case 4:
                return $this->conn->update("mdata")
                    ->set(['viewed' => 1])
                    ->where('id', '=', $id)
                    ->where('target', '=', $session->uid)
                    ->execute();
            case 5:
                return $this->conn->update("mdata")
                    ->set(['deltarget' => 1, 'viewed' => 1])
                    ->where('id', '=', $id)
                    ->execute();
            case 6:
                return $this->conn->select()
                    ->from("mdata")
                    ->where('target', '=', $id)
                    ->where('send', '=', 0)
                    ->where('archived', '=', 1)
                    ->execute();
            case 7:
                return $this->conn->update("mdata")
                    ->set(['delowner' => 1])
                    ->where('id', '=', $id)
                    ->execute();
            case 8:
                return $this->conn->update("mdata")
                    ->set(['deltarget' => 1, 'delowner' => 1, 'viewed' => 1])
                    ->where('id', '=', $id)
                    ->execute();
            case 9:
                return $this->conn->select()
                    ->from("mdata")
                    ->where('target', '=', $id)
                    ->where('send', '=', 0)
                    ->where('archived', '=', 0)
                    ->where('deltarget', '=', 0)
                    ->orderBy('time', 'DESC')
                    ->execute();
            case 10:
                return $this->conn->select()
                    ->from("mdata")
                    ->where('owner', '=', $id)
                    ->where('delowner', '=', 0)
                    ->orderBy('time', 'DESC')
                    ->execute();
            case 11:
                return $this->conn->select()
                    ->from("mdata")
                    ->where('target', '=', $id)
                    ->where('send', '=', 0)
                    ->where('archived', '=', 1)
                    ->where('deltarget', '=', 0)
                    ->execute();
        }
    }

    function getDelSent($uid)
    {
        return $this->conn
            ->from("mdata")
            ->where('owner = :uid AND delowner = 1', [':uid' => $uid])
            ->orderBy('time', 'DESC')
            ->fetchAll();
    }

    function getDelInbox($uid)
    {
        return $this->conn
            ->from("mdata")
            ->where('target = :uid AND deltarget = 1', [':uid' => $uid])
            ->orderBy('time', 'DESC')
            ->fetchAll();
    }

    function getDelArchive($uid)
    {
        return $this->conn
            ->from("mdata")
            ->where('(target = :uid AND archived = 1 AND deltarget = 1) OR (owner = :uid AND archived = 1 AND delowner = 1)', [':uid' => $uid])
            ->orderBy('time', 'DESC')
            ->fetchAll();
    }

    function unarchiveNotice($id)
    {
        return $this->conn
            ->update("ndata", ['archive' => 0], 'id = :id', [':id' => $id]);
    }

    function archiveNotice($id)
    {
        return $this->conn
            ->update("ndata", ['archive' => 1], 'id = :id', [':id' => $id]);
    }

    function removeNotice($id)
    {
        return $this->conn
            ->update("ndata", ['del' => 1, 'viewed' => 1], 'id = :id', [':id' => $id]);
    }

    function noticeViewed($id)
    {
        return $this->conn
            ->update("ndata", ['viewed' => 1], 'id = :id', [':id' => $id]);
    }

    function addNotice($uid, $toWref, $ally, $type, $topic, $data, $time = 0)
    {
        if ($time == 0) {
            $time = time();
        }
        return $this->conn
            ->insert("ndata", [
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

    function getNotice($uid)
    {
        return $this->conn
            ->from("ndata")
            ->where('uid = :uid AND del = 0', [':uid' => $uid])
            ->orderBy('time', 'DESC')
            ->fetchAll();
    }

    function getNotice2($id, $field)
    {
        $result = $this->conn
            ->from("ndata", $field)
            ->where('id = :id', [':id' => $id])
            ->fetch();
        return $result[$field];
    }

    function getNotice3($uid)
    {
        return $this->conn
            ->from("ndata")
            ->where('uid = :uid')
            ->orderBy('time', 'DESC')
            ->fetchAll([':uid' => $uid]);
    }

    function getNotice4($id)
    {
        return $this->conn
            ->from("ndata")
            ->where('id = :id')
            ->orderBy('time', 'DESC')
            ->fetchAll([':id' => $id]);
    }

    function getNotice5($uid)
    {
        return $this->conn
            ->from("ndata")
            ->where('uid = :uid AND viewed = 0')
            ->orderBy('time', 'DESC')
            ->fetchAll([':uid' => $uid]);
    }

    function createTradeRoute($uid, $worlid, $from, $r1, $r2, $r3, $r4, $start, $deliveries, $merchant, $time)
    {
        $this->conn->update("users", ['gold' => 'gold - 2'], 'id = :uid', [':uid' => $uid]);
        return $this->conn
            ->insert("route", [
                'uid' => $uid,
                'wid' => $worlid,
                'from' => $from,
                'r1' => $r1,
                'r2' => $r2,
                'r3' => $r3,
                'r4' => $r4,
                'start' => $start,
                'deliveries' => $deliveries,
                'merchant' => $merchant,
                'timestamp' => $time
            ]);
    }

    function getTradeRoute($uid)
    {
        return $this->conn
            ->from("route")
            ->where('uid = :uid')
            ->orderBy('timestamp', 'ASC')
            ->fetchAll([':uid' => $uid]);
    }

    function getTradeRoute2($id)
    {
        return $this->conn
            ->from("route")
            ->where('id = :id')
            ->fetch([':id' => $id]);
    }

    function getTradeRouteUid($id)
    {
        return $this->conn
            ->from("route")
            ->where('id = :id')
            ->fetchColumn([':id' => $id], 'uid');
    }

    function editTradeRoute($id, $column, $value, $mode)
    {
        $data = [$column => ($mode ? "$column + $value" : $value)];
        return $this->conn
            ->update("route", $data, 'id = :id', [':id' => $id]);
    }

    function deleteTradeRoute($id)
    {
        return $this->conn
            ->delete("route", 'id = :id', [':id' => $id]);
    }

    function getAttacks($ref)
    {
        return $this->conn
            ->from("attacks")
            ->where('id = :ref')
            ->fetchAll([':ref' => $ref]);
    }

    function getAlliAttacks($aid)
    {
        return $this->conn
            ->from("ndata")
            ->where('ally = :aid')
            ->orderBy('time', 'DESC')
            ->fetchAll([':aid' => $aid]);
    }

    function addBuilding($worlid, $field, $type, $loop, $time, $master, $level)
    {
        $this->conn->update("fdata", ["f" . $field . "t" => $type], 'vref = :wid', [':wid' => $worlid]);
        return $this->conn
            ->insert("bdata", [
                'wid' => $worlid,
                'field' => $field,
                'type' => $type,
                'loopcon' => $loop,
                'timestamp' => $time,
                'master' => $master,
                'level' => $level
            ]);
    }

    function removeBuilding($d)
    {
        global $building;

        $jobLoopconID = -1;
        $SameBuildCount = 0;
        $jobs = $building->buildArray;

        foreach ($jobs as $key => $job) {
            if ($job['id'] == $d) {
                $jobDeleted = $key;
            }
            if ($job['loopcon'] == 1) {
                $jobLoopconID = $key;
            }
        }

        $fieldsCount = array_count_values(array_column($jobs, 'field'));
        $SameBuildCount = max($fieldsCount) - 1;

        if ($SameBuildCount > 0) {
            if ($SameBuildCount >= 3 && $jobDeleted == 0) {
                $uprequire = $building->resourceRequired($jobs[1]['field'], $jobs[1]['type'], $SameBuildCount == 4 || $SameBuildCount == 5 ? 1 : 2);
                $time = $uprequire['time'] + time();
                $this->conn->update("bdata", ["loopcon" => 0, "level" => "level-1", "timestamp" => $time], ["id" => $jobs[1]['id']]);
            } elseif ($SameBuildCount == 6 && $jobDeleted == 0) {
                $uprequire = $building->resourceRequired($jobs[2]['field'], $jobs[2]['type'], 1);
                $time = $uprequire['time'] + time();
                $this->conn->update("bdata", ["loopcon" => 0, "level" => "level-1", "timestamp" => $time], ["id" => $jobs[2]['id']]);
            } elseif ($SameBuildCount == 7 && $jobDeleted == 1) {
                $uprequire = $building->resourceRequired($jobs[2]['field'], $jobs[2]['type'], 1);
                $time = $uprequire['time'] + time();
                $this->conn->update("bdata", ["loopcon" => 0, "level" => "level-1", "timestamp" => $time], ["id" => $jobs[2]['id']]);
            }
            if ($SameBuildCount < 8) {
                $uprequire1 = $building->resourceRequired($jobs[$jobDeleted]['field'], $jobs[$jobDeleted]['type'], $SameBuildCount < 3 ? 2 : 1);
                $time1 = $uprequire1['time'];
                $timestamp1 = $time1;
                $this->conn->update("bdata", ["level" => "level-1", "timestamp" => $timestamp1], ["id" => $jobs[$jobDeleted]['id']]);
            } else {
                $uprequire1 = $building->resourceRequired($jobs[$jobDeleted]['field'], $jobs[$jobDeleted]['type'], 1);
                $time1 = $uprequire1['time'];
                $timestamp1 = $time1;
                $this->conn->update("bdata", ["level" => "level-1", "timestamp" => $timestamp1], ["id" => $jobs[$jobDeleted]['id']]);
            }
        } else {
            $field = $jobs[$jobDeleted]['field'];
            if ($field >= 19) {
                $x = "SELECT f$field FROM " . "fdata WHERE vref=" . $jobs[$jobDeleted]['wid'];
                $result = $this->conn->query($x) or die($this->conn->error());
                $fieldlevel = $result->fetch_row();
                if ($fieldlevel[0] == 0) {
                    $x = "UPDATE " . "fdata SET f$field=0 WHERE vref=" . $jobs[$jobDeleted]['wid'];
                    $this->conn->query($x) or die($this->conn->error());
                }
            }
            if (($jobLoopconID >= 0) && ($jobs[$jobDeleted]['loopcon'] != 1)) {
                if (($jobs[$jobLoopconID]['field'] <= 18 && $jobs[$jobDeleted]['field'] <= 18) || ($jobs[$jobLoopconID]['field'] >= 19 && $jobs[$jobDeleted]['field'] >= 19) || sizeof($jobs) < 3) {
                    $uprequire = $building->resourceRequired($jobs[$jobLoopconID]['field'], $jobs[$jobLoopconID]['type']);
                    $x = "UPDATE " . "bdata SET loopcon=0,timestamp=" . (time() + $uprequire['time']) . " WHERE wid=" . $jobs[$jobDeleted]['wid'] . " AND loopcon=1 AND master=0";
                    $this->conn->query($x) or die($this->conn->error());
                }
            }
        }
        $this->conn->delete("bdata", ["id" => $d]);
    }

    function addDemolition($worlid, $field)
    {
        global $building, $village;

        $this->conn->delete("bdata", ["field" => $field, "wid" => $worlid]);

        $uprequire = $building->resourceRequired($field, $village->resarray['f' . $field . 't']);
        $timestamp = time() + floor($uprequire['time'] / 2);

        $this->conn->insert("demolition", ["vref" => $worlid, "field" => $field, "level" => ($this->getFieldLevel($worlid, $field) - 1), "timetofinish" => $timestamp]);
    }

    function getDemolition($worlid = 0)
    {
        if ($worlid) {
            $condition = ["vref" => $worlid];
        } else {
            $condition = ["timetofinish[<=]" => time()];
        }

        $result = $this->conn->select("demolition", "*", $condition);

        return ($result) ? $result->fetchAll() : NULL;
    }

    function finishDemolition($worlid)
    {
        $this->conn->update("demolition", ["timetofinish" => time()], ["vref" => $worlid]);
    }

    function delDemolition($worlid)
    {
        $this->conn->delete("demolition", ["vref" => $worlid]);
    }

    function getJobs($worlid)
    {
        $result = $this->conn->select("bdata", "*", ["wid" => $worlid], ["ORDER" => ["master", "timestamp" => "ASC"]]);

        return ($result) ? $result->fetchAll() : NULL;
    }

    function FinishWoodcutter($worlid)
    {
        $time = time() - 1;
        $woodcutterResult = $this->conn->select("bdata", "*", ["wid" => $worlid, "type" => 1], ["ORDER" => ["master", "timestamp" => "ASC"], "LIMIT" => 1])->fetch();
        if ($woodcutterResult) {
            $woodcutterId = $woodcutterResult['id'];
            $this->conn->update("bdata", ["timestamp" => $time], ["id" => $woodcutterId]);

            $tribe = $this->getUserField($this->getVillageField($worlid, "owner"), "tribe", 0);
            if ($tribe == 1) {
                $loopconQuery = $this->conn->select("bdata", "*", ["wid" => $worlid, "loopcon" => 1, "field[<=]" => 18], ["ORDER" => ["master", "timestamp" => "ASC"], "LIMIT" => 1])->fetch();
            } else {
                $loopconQuery = $this->conn->select("bdata", "*", ["wid" => $worlid, "loopcon" => 1], ["ORDER" => ["master", "timestamp" => "ASC"], "LIMIT" => 1])->fetch();
            }
            if ($loopconQuery) {
                $wc_time = $woodcutterResult['timestamp'];
                $this->conn->update("bdata", ["timestamp[-]" => $wc_time], ["id" => $loopconQuery['id']]);
            }
        }
    }

    function FinishRallyPoint($worlid)
    {
        $time = time() - 1;
        $rallyPointResult = $this->conn->select("bdata", "*", ["wid" => $worlid, "type" => 16], ["ORDER" => ["master", "timestamp" => "ASC"], "LIMIT" => 1])->fetch();
        if ($rallyPointResult) {
            $rallyPointId = $rallyPointResult['id'];
            $this->conn->update("bdata", ["timestamp" => $time], ["id" => $rallyPointId]);

            $tribe = $this->getUserField($this->getVillageField($worlid, "owner"), "tribe", 0);
            if ($tribe == 1) {
                $loopconQuery = $this->conn->select("bdata", "*", ["wid" => $worlid, "loopcon" => 1, "field[>=]" => 19], ["ORDER" => ["master", "timestamp" => "ASC"], "LIMIT" => 1])->fetch();
            } else {
                $loopconQuery = $this->conn->select("bdata", "*", ["wid" => $worlid, "loopcon" => 1], ["ORDER" => ["master", "timestamp" => "ASC"], "LIMIT" => 1])->fetch();
            }
            if ($loopconQuery) {
                $rally_time = $rallyPointResult['timestamp'];
                $this->conn->update("bdata", ["timestamp[-]" => $rally_time], ["id" => $loopconQuery['id']]);
            }
        }
    }

    function getMasterJobs($worlid)
    {
        return $this->conn->select("bdata", "*", ["wid" => $worlid, "master" => 1], ["ORDER" => ["master", "timestamp" => "ASC"]])->fetchAll();
    }

    function getMasterJobsByField($worlid, $field)
    {
        return $this->conn->select("bdata", "*", ["wid" => $worlid, "field" => $field, "master" => 1], ["ORDER" => ["master", "timestamp" => "ASC"]])->fetchAll();
    }

    function getBuildingByField($worlid, $field)
    {
        return $this->conn->select("bdata", "*", ["wid" => $worlid, "field" => $field, "master" => 0])->fetchAll();
    }

    function getBuildingByType($worlid, $type)
    {
        return $this->conn->select("bdata", "*", ["wid" => $worlid, "type" => $type, "master" => 0])->fetchAll();
    }

    function getDorf1Building($worlid)
    {
        return $this->conn->select("bdata", "*", ["wid" => $worlid, "field[<]" => 19, "master" => 0])->fetchAll();
    }

    function getDorf2Building($worlid)
    {
        return $this->conn->select("bdata", "*", ["wid" => $worlid, "field[>]" => 18, "master" => 0])->fetchAll();
    }

    function updateBuildingWithMaster($id, $time, $loop)
    {
        return $this->conn->update(
            "bdata",
            ["master" => 0, "timestamp" => $time, "loopcon" => $loop],
            ["id" => $id]
        );
    }

    function getVillageByName($name)
    {
        $result = $this->conn->select("vdata", "wref", ["name" => $name, "LIMIT" => 1]);
        $dbarray = $result->fetch();
        return $dbarray['wref'];
    }

    function setMarketAcc($id)
    {
        return $this->conn->update(
            "market",
            ["accept" => 1],
            ["id" => $id]
        );
    }

    function sendResource($ref, $clay, $iron, $crop, $merchant, $mode)
    {
        if (!$mode) {
            $data = [
                "ref" => $ref,
                "clay" => $clay,
                "iron" => $iron,
                "crop" => $crop,
                "merchant" => $merchant
            ];
            return $this->conn->insert("send", $data);
        } else {
            return $this->conn->delete("send", ["id" => $ref]);
        }
    }

    function getResourcesBack($vref, $gtype, $gamt)
    {
        //Xtype (1) = wood, (2) = clay, (3) = iron, (4) = crop
        $column = '';
        switch ($gtype) {
            case 1:
                $column = 'wood';
                break;
            case 2:
                $column = 'clay';
                break;
            case 3:
                $column = 'iron';
                break;
            case 4:
                $column = 'crop';
                break;
            default:
                return false; // Tipo de recurso inválido
        }

        $data = [$column => $gamt];
        return $this->conn->update("vdata", $data, ["wref" => $vref]);
    }

    function getMarketField($vref, $field)
    {
        $data = $this->conn->select($field)->from("market")->where(['vref' => $vref])->fetch();
        return $data[$field] ?? null;
    }

    function removeAcceptedOffer($id)
    {
        $this->conn->delete("market")->where(['id' => $id])->execute();
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
                'time' => $time,
                'alliance' => $alliance,
                'merchant' => $merchant
            ];
            $this->conn->insert("market")->values($data)->execute();
            return $this->conn->lastInsertId();
        } else {
            $this->conn->delete("market")->where(['id' => $gtype, 'vref' => $vid])->execute();
            return true;
        }
    }

    function getMarket($vid, $mode)
    {
        $alliance = $this->getUserField($this->getVillageField($vid, "owner"), "alliance", 0);
        if (!$mode) {
            return $this->conn
                ->select('*')
                ->from("market")
                ->where(['vref' => $vid, 'accept' => 0])
                ->orderBy('id', 'DESC')
                ->execute()
                ->fetchAll();
        } else {
            return $this->conn
                ->select('*')
                ->from("market")
                ->where([
                    'AND' => [
                        ['vref', '!=', $vid],
                        ['accept', '=', 0],
                        ['OR' => [['alliance', '=', $alliance], ['alliance', '=', 0]]]
                    ]
                ])
                ->orderBy('id', 'DESC')
                ->execute()
                ->fetchAll();
        }
    }

    function getMarketInfo($id)
    {
        return $this->conn
            ->select('*')
            ->from("market")
            ->where(['id' => $id])
            ->execute()
            ->fetchAssoc();
    }

    function setMovementProc($moveid)
    {
        $q = "UPDATE " . "movement SET proc = 1 WHERE moveid = $moveid";
        return $this->conn->query($q);
    }

    function totalMerchantUsed($vid)
    {
        $time = time();

        $q1 = $this->conn
            ->select('SUM(' . 'send.merchant)')
            ->from('send, ' . 'movement')
            ->where('movement.from = :vid')
            ->andWhere('send.id = ' . 'movement.ref')
            ->andWhere('movement.proc = 0')
            ->andWhere(['sort_type' => 0])
            ->bindValue(':vid', $vid)
            ->execute()
            ->fetchRow();

        $q2 = $this->conn
            ->select('SUM(ref)')
            ->from('movement')
            ->where(['sort_type' => 2])
            ->andWhere('movement.to = :vid')
            ->andWhere(['proc' => 0])
            ->bindValue(':vid', $vid)
            ->execute()
            ->fetchRow();

        $q3 = $this->conn
            ->select('SUM(merchant)')
            ->from('market')
            ->where(['vref' => $vid])
            ->andWhere(['accept' => 0])
            ->execute()
            ->fetchRow();

        return $q1[0] + $q2[0] + $q3[0];
    }

    function getMovement($type, $village, $mode)
    {
        $time = time();
        if (!$mode) {
            $where = "from";
        } else {
            $where = "to";
        }
        switch ($type) {
            case 0:
                $query = $this->conn
                    ->select('*')
                    ->from('movement, ' . 'send')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere('movement.ref = ' . 'send.id')
                    ->andWhere('movement.proc = 0')
                    ->andWhere('movement.sort_type = 0')
                    ->bindValue(':village', $village)
                    ->execute();
                break;
            case 2:
                $query = $this->conn
                    ->select('*')
                    ->from('movement')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere('movement.proc = 0')
                    ->andWhere('movement.sort_type = 2')
                    ->bindValue(':village', $village)
                    ->execute();
                break;
            case 3:
            case 4:
                $query = $this->conn
                    ->select('*')
                    ->from('movement, ' . 'attacks')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere('movement.ref = ' . 'attacks.id')
                    ->andWhere('movement.proc = 0')
                    ->andWhere('(' . 'movement.sort_type = 3 OR ' . 'movement.sort_type = 4)')
                    ->orderBy('endtime ASC')
                    ->bindValue(':village', $village)
                    ->execute();
                break;
            case 5:
            case 9:
                $query = $this->conn
                    ->select('*')
                    ->from('movement')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere(['sort_type' => $type])
                    ->andWhere(['proc' => 0])
                    ->bindValue(':village', $village)
                    ->execute();
                break;
            case 6:
                $query = $this->conn
                    ->select('*')
                    ->from('movement, ' . 'odata, ' . 'attacks')
                    ->where('odata.conqured = :village')
                    ->andWhere('movement.to = ' . 'odata.wref')
                    ->andWhere('movement.ref = ' . 'attacks.id')
                    ->andWhere('movement.proc = 0')
                    ->andWhere('movement.sort_type = 3')
                    ->orderBy('endtime ASC')
                    ->bindValue(':village', $village)
                    ->execute();
                break;
            case 34:
                $query = $this->conn
                    ->select('*')
                    ->from('movement, ' . 'attacks')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere('movement.ref = ' . 'attacks.id')
                    ->andWhere('movement.proc = 0')
                    ->andWhere('(' . 'movement.sort_type = 3 OR ' . 'movement.sort_type = 4)')
                    ->orderBy('endtime ASC')
                    ->bindValue(':village', $village)
                    ->execute();
                break;
        }
        return $query->fetchAll();
    }

    function getMovement2($type, $village, $mode)
    {
        $time = time();
        if (!$mode) {
            $where = "from";
        } else {
            $where = "to";
        }
        switch ($type) {
            case 3:
                $query = $this->conn
                    ->select('*')
                    ->from('movement, ' . 'attacks')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere('movement.ref = ' . 'attacks.id')
                    ->andWhere('movement.proc = 0')
                    ->andWhere('movement.sort_type = 3')
                    ->andWhere('attacks.attack_type != 2')
                    ->orderBy('endtime DESC')
                    ->bindValue(':village', $village)
                    ->execute();
                break;
            case 34:
                $query = $this->conn
                    ->select('*')
                    ->from('movement, ' . 'attacks')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere('movement.ref = ' . 'attacks.id')
                    ->andWhere('movement.proc = 0')
                    ->andWhere('movement.sort_type = 3')
                    ->andWhere('attacks.attack_type != 3')
                    ->andWhere('attacks.attack_type != 4')
                    ->orWhere('movement.sort_type = 4')
                    ->orderBy('endtime DESC')
                    ->bindValue(':village', $village)
                    ->execute();
                break;
            case 5:
            case 9:
                $query = $this->conn
                    ->select('*')
                    ->from('movement')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere(['sort_type' => $type])
                    ->andWhere(['proc' => 0])
                    ->bindValue(':village', $village)
                    ->execute();
                break;
            case 7:
                $query = $this->conn
                    ->select('*')
                    ->from('movement, ' . 'attacks')
                    ->where('movement.' . $where . ' = :village')
                    ->andWhere('movement.ref = ' . 'attacks.id')
                    ->andWhere('movement.proc = 0')
                    ->andWhere('movement.sort_type = 3')
                    ->andWhere('attacks.attack_type = 2')
                    ->orderBy('endtime DESC')
                    ->bindValue(':village', $village)
                    ->execute();
                break;
        }
        return $query->fetchAll();
    }

    function addA2b($ckey, $timestamp, $to, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type)
    {
        $query = $this->conn
            ->insert('a2b')
            ->columns('ckey', 'time_check', 'to_vid', 'u1', 'u2', 'u3', 'u4', 'u5', 'u6', 'u7', 'u8', 'u9', 'u10', 'u11', 'type')
            ->values([$ckey, $timestamp, $to, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type])
            ->execute();

        return $query->lastInsertId();
    }

    function getA2b($ckey, $check)
    {
        $query = $this->conn
            ->select('*')
            ->from('a2b')
            ->where(['ckey' => $ckey, 'time_check' => $check])
            ->execute();

        return $query->fetch();
    }

    function addMovement($type, $from, $to, $ref, $data, $endtime, $send = 1, $wood = 0, $clay = 0, $iron = 0, $crop = 0, $ref2 = 0)
    {
        $query = $this->conn
            ->insert('movement')
            ->columns('type', 'from', 'to', 'ref', 'ref2', 'data', 'endtime', 'sort_type', 'proc', 'send', 'wood', 'clay', 'iron', 'crop')
            ->values([$type, $from, $to, $ref, $ref2, $data, $endtime, 0, $send, $wood, $clay, $iron, $crop])
            ->execute();

        return $query->lastInsertId();
    }

    function addAttack($vid, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type, $ctar1, $ctar2, $spy)
    {
        $query = $this->conn
            ->insert('attacks')
            ->columns('vid', 't1', 't2', 't3', 't4', 't5', 't6', 't7', 't8', 't9', 't10', 't11', 'type', 'ctar1', 'ctar2', 'spy')
            ->values([$vid, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11, $type, $ctar1, $ctar2, $spy])
            ->execute();

        return $query->lastInsertId();
    }

    function modifyAttack($aid, $unit, $amt)
    {
        $unit = 't' . $unit;
        $query = $this->conn
            ->update('attacks')
            ->set([$unit => $unit - $amt])
            ->where(['id' => $aid])
            ->execute();

        return $query->rowCount();
    }

    function modifyAttack2($aid, $unit, $amt)
    {
        $unit = 't' . $unit;
        $query = $this->conn
            ->update('attacks')
            ->set([$unit => $unit . ' + ' . $amt])
            ->where(['id' => $aid])
            ->execute();

        return $query->rowCount();
    }

    function getRanking()
    {
        $query = $this->conn
            ->select('id', 'username', 'alliance', 'ap', 'apall', 'dp', 'dpall', 'access')
            ->from('users')
            ->where('tribe', '<=', 3)
            ->andWhere('access', '<', INCLUDE_ADMIN ? 10 : 8)
            ->execute();

        return $query->fetchAll();
    }

    function getBuildList($type)
    {
        $query = $this->conn
            ->select('*')
            ->from('bdata')
            ->where('type', '=', $type)
            ->execute();

        return $query->fetchAll();
    }

    function getVRanking()
    {
        $query = $this->conn
            ->select('v.wref', 'v.name', 'v.owner', 'v.pop')
            ->from('vdata', 'v')
            ->leftJoin('users', 'u', 'v.owner = u.id')
            ->where('u.tribe', '<=', 3)
            ->andWhere('v.wref', '!=', '')
            ->andWhere('u.access', '<', INCLUDE_ADMIN ? 10 : 8)
            ->execute();

        return $query->fetchAll();
    }

    function getARanking($limit = "")
    {
        $query = $this->conn
            ->select('id', 'name', 'tag', 'oldrank', 'Aap', 'Adp')
            ->from('alidata')
            ->where('id', '!=', '')
            ->append($limit)
            ->execute();

        return $query->fetchAll();
    }

    function getARanking2()
    {
        $query = $this->conn
            ->select()
            ->from('alidata')
            ->where('id', '!=', '')
            ->execute();

        return $query->rowCount();
    }

    function getARanking3($limit = "")
    {
        $query = $this->conn
            ->select()
            ->from('alidata')
            ->where('id', '!=', '')
            ->append($limit)
            ->execute();

        return $query->fetchAll();
    }

    function getHeroRanking()
    {
        $query = $this->conn
            ->select()
            ->from('hero')
            ->execute();

        return $query->fetchAll();
    }

    function getAllMember($aid)
    {
        $query = $this->conn
            ->select('*')
            ->from('users')
            ->where('alliance', '=', $aid)
            ->orderBy('(SELECT sum(pop) FROM ' . 'vdata WHERE owner = ' . 'users.id)', 'desc')
            ->execute();

        return $query->fetchAll();
    }

    public function addUnits($vid)
    {
        return $this->conn->insert('units', ['vref' => $vid]);
    }

    public function getUnit($vid)
    {
        $result = $this->conn
            ->select()
            ->from('units')
            ->where('vref', '=', $vid)
            ->get();

        return $result ? $result : NULL;
    }

    public function getHUnit($vid)
    {
        $query = $this->conn
            ->select('hero')
            ->from('units')
            ->where('vref', '=', $vid)
            ->execute();

        $result = $query->fetch();
        return $result['hero'] != 0;
    }

    function getHero($uid = 0)
    {
        if (!$uid) {
            $query = $this->conn
                ->select()
                ->from('hero')
                ->execute();
        } else {
            $query = $this->conn
                ->select()
                ->from('hero')
                ->where('dead', '=', 0)
                ->andWhere('uid', '=', $uid)
                ->limit(1)
                ->execute();
        }

        return $query->fetchAll();
    }

    function modifyHero($column, $value, $heroid, $mode = 0)
    {
        if (!$mode) {
            $query = $this->conn
                ->update('hero')
                ->set([$column => $value])
                ->where('heroid', '=', $heroid)
                ->execute();
        } elseif ($mode == 1) {
            $query = $this->conn
                ->update('hero')
                ->set([$column => $column . ' + ' . $value])
                ->where('heroid', '=', $heroid)
                ->execute();
        } elseif ($mode == 2) {
            $query = $this->conn
                ->update('hero')
                ->set([$column => $column . ' - ' . $value])
                ->where('heroid', '=', $heroid)
                ->execute();
        }

        return $query->rowCount();
    }

    function modifyHero2($column, $value, $uid, $mode)
    {
        if (!$mode) {
            $query = $this->conn
                ->update('hero')
                ->set([$column => $value])
                ->where('uid', '=', $uid)
                ->execute();
        } elseif ($mode == 1) {
            $query = $this->conn
                ->update('hero')
                ->set([$column => $column . ' + ' . $value])
                ->where('uid', '=', $uid)
                ->execute();
        } elseif ($mode == 2) {
            $query = $this->conn
                ->update('hero')
                ->set([$column => $column . ' - ' . $value])
                ->where('uid', '=', $uid)
                ->execute();
        }

        return $query->rowCount();
    }

    public function addTech($vid)
    {
        return $this->conn->insert('tdata', ['vref' => $vid]);
    }

    public function addABTech($vid)
    {
        return $this->conn->insert('abdata', ['vref' => $vid]);
    }

    function getABTech($vid)
    {
        return $this->conn
            ->select()
            ->from('abdata')
            ->where('vref', '=', $vid)
            ->get();
    }

    function addResearch($vid, $tech, $time)
    {
        $query = $this->conn
            ->insert('research')
            ->values([
                'vref' => $vid,
                'tech' => $tech,
                'time' => $time
            ])
            ->execute();

        return $query->rowCount();
    }

    function getResearching($vid)
    {
        $query = $this->conn
            ->select()
            ->from('research')
            ->where('vref', '=', $vid)
            ->execute();

        return $query->fetchAll();
    }

    function checkIfResearched($vref, $unit)
    {
        $query = $this->conn
            ->select($unit)
            ->from('tdata')
            ->where('vref', '=', $vref)
            ->execute();

        $result = $query->fetch();
        return $result[$unit];
    }

    function getTech($vid)
    {
        $query = $this->conn
            ->select()
            ->from('tdata')
            ->where('vref', '=', $vid)
            ->execute();

        return $query->fetchAssoc();
    }

    function getTraining($vid)
    {
        $query = $this->conn->select()
            ->from('training')
            ->where('vref', '=', $vid)
            ->orderBy('id')
            ->execute();

        return $query->fetchAll();
    }

    function countTraining($vid)
    {
        $query = $this->conn->select()
            ->from('training', 'COUNT(*)')
            ->where('vref', '=', $vid)
            ->execute();

        $result = $query->fetch();
        return $result['COUNT(*)'];
    }

    function trainUnit($vid, $unit, $amt, $pop, $each, $time, $mode)
    {
        global $village, $building, $session, $technology;

        if (!$mode) {
            $barracks = array(1, 2, 3, 11, 12, 13, 14, 21, 22, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44);
            $greatbarracks = array(61, 62, 63, 71, 72, 73, 84, 81, 82, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104);
            $stables = array(4, 5, 6, 15, 16, 23, 24, 25, 26, 45, 46);
            $greatstables = array(64, 65, 66, 75, 76, 83, 84, 85, 86, 105, 106);
            $workshop = array(7, 8, 17, 18, 27, 28, 47, 48);
            $greatworkshop = array(67, 68, 77, 78, 87, 88, 107, 108);
            $residence = array(9, 10, 19, 20, 29, 30, 49, 50);
            $trapper = array(99);

            if (in_array($unit, $barracks)) {
                $queued = $technology->getTrainingList(1);
            } elseif (in_array($unit, $stables)) {
                $queued = $technology->getTrainingList(2);
            } elseif (in_array($unit, $workshop)) {
                $queued = $technology->getTrainingList(3);
            } elseif (in_array($unit, $residence)) {
                $queued = $technology->getTrainingList(4);
            } elseif (in_array($unit, $greatstables)) {
                $queued = $technology->getTrainingList(6);
            } elseif (in_array($unit, $greatbarracks)) {
                $queued = $technology->getTrainingList(5);
            } elseif (in_array($unit, $greatworkshop)) {
                $queued = $technology->getTrainingList(7);
            } elseif (in_array($unit, $trapper)) {
                $queued = $technology->getTrainingList(8);
            }
            $now = time();

            if ($each == 0) {
                $each = 1;
            }
            $time2 = $now + $each;
            if (count($queued) > 0) {
                $time += $queued[count($queued) - 1]['timestamp'] - $now;
                $time2 += $queued[count($queued) - 1]['timestamp'] - $now;
            }
            if ($queued[count($queued) - 1]['unit'] == $unit) {
                $time = $amt * $queued[count($queued) - 1]['eachtime'];
                $query = $this->conn->update('training')
                    ->set('amt', 'amt + ' . $amt)
                    ->set('timestamp', 'timestamp + ' . $time)
                    ->where('id', '=', $queued[count($queued) - 1]['id'])
                    ->execute();
            } else {
                $query = $this->conn->insert('training')
                    ->values(array(
                        'vref' => $vid,
                        'unit' => $unit,
                        'amt' => $amt,
                        'pop' => $pop,
                        'timestamp' => $time,
                        'eachtime' => $each,
                        'time2' => $time2
                    ))
                    ->execute();
            }
        } else {
            $query = $this->conn->delete('training')
                ->where('id', '=', $vid)
                ->execute();
        }

        return $query;
    }

    function getHeroTrain($vid)
    {
        $query = $this->conn->select()
            ->from('training')
            ->where('vref', '=', $vid)
            ->andWhere('unit', '=', 0)
            ->execute();

        return $this->mysql_fetch_assoc($query);
    }

    function trainHero($vid, $each, $mode)
    {
        if (!$mode) {
            $time = time();
            $query = $this->conn->insert('training')
                ->values(array(
                    'vref' => $vid,
                    'unit' => 0,
                    'amt' => 1,
                    'pop' => 6,
                    'timestamp' => $time,
                    'eachtime' => $each,
                    'time2' => $each
                ))
                ->execute();
        } else {
            $query = $this->conn->delete('training')
                ->where('id', '=', $vid)
                ->execute();
        }

        return $query;
    }

    function updateTraining($id, $trained, $each)
    {
        $tableName = "training";
        $data = array(
            'amt' => 'amt - ' . $trained,
            'timestamp2' => 'timestamp2 + ' . $each
        );
        $where = array('id' => $id);
        return $this->conn->update($tableName, $data, $where);
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
        $tableName = "units";
        $data = array(
            $unit => ($mode ? "$unit + $amt" : "$unit - $amt")
        );
        $where = array('vref' => $vref);
        return $this->conn->update($tableName, $data, $where);
    }

    function getEnforce($vid, $from)
    {
        $tableName = "enforcement";
        $columns = "*";
        $where = "`from` = $from AND vref = $vid";
        $result = $this->conn->select($tableName, $columns, $where);
        return mysql_fetch_assoc($result);
    }

    function checkEnforce($vid, $from)
    {
        $tableName = "enforcement";
        $columns = "*";
        $where = "`from` = ? AND vref = ?";
        $values = array($from, $vid);
        $result = $this->conn->select($tableName, $columns, $where, $values);
        if ($result->numRows() > 0) {
            return $result->insertId();
        } else {
            return true;
        }
    }

    function addEnforce($data)
    {
        $tableName = "enforcement";
        $enforceData = array(
            'vref' => $data['to'],
            '`from`' => $data['from']
        );
        $this->conn->insert($tableName, $enforceData);
        $id = $this->conn->insertId();
        if ($data['from'] != 0) {
            $owntribe = $this->getUserField($this->getVillageField($data['from'], "owner"), "tribe", 0);
        } else {
            $owntribe = 4;
        }
        $start = ($owntribe - 1) * 10 + 1;
        $end = ($owntribe * 10);
        //add unit
        $j = '1';
        for ($i = $start; $i <= $end; $i++) {
            $this->modifyEnforce($id, $i, $data['t' . $j . ''], 1);
            $j++;
        }
        return $id;
    }

    function addHeroEnforce($data)
    {
        $enforceData = array(
            'vref' => $data['to'],
            '`from`' => $data['from'],
            'hero' => 1
        );
        $this->conn->insert("enforcement", $enforceData);
    }

    function modifyEnforce($id, $unit, $amt, $mode)
    {
        if ($unit == 'hero') {
            $unit = 'hero';
        } else {
            $unit = 'u' . $unit;
        }
        $tableName = "enforcement";
        if (!$mode) {
            $data = array($unit => "$unit - $amt");
        } else {
            $data = array($unit => "$unit + $amt");
        }
        $where = "id = ?";
        $values = array($id);
        $this->conn->update($tableName, $data, $where, $values);
    }

    function getEnforceArray($id, $mode)
    {
        $tableName = "enforcement";
        if (!$mode) {
            $where = "id = ?";
            $values = array($id);
        } else {
            $where = "`from` = ?";
            $values = array($id);
        }
        return $this->conn->selectOne($tableName, "*", $where, $values);
    }

    function getEnforceVillage($id, $mode)
    {
        $tableName = "enforcement";
        if (!$mode) {
            $where = "`vref` = ?";
            $values = array($id);
        } else {
            $where = "`from` = ?";
            $values = array($id);
        }
        return $this->conn->selectAll($tableName, "*", $where, $values);
    }

    function getVillageMovement($id)
    {
        $vinfo = $this->getVillage($id);
        $vtribe = $this->getUserField($vinfo['owner'], "tribe", 0);
        $movingunits = array();

        $outgoingarray = $this->getMovement(3, $id, 0);
        if (!empty($outgoingarray)) {
            foreach ($outgoingarray as $out) {
                for ($i = 1; $i <= 10; $i++) {
                    $movingunits['u' . (($vtribe - 1) * 10 + $i)] += $out['t' . $i];
                }
            }
        }

        $returningarray = $this->getMovement(4, $id, 1);
        if (!empty($returningarray)) {
            foreach ($returningarray as $ret) {
                if ($ret['attack_type'] != 1) {
                    for ($i = 1; $i <= 10; $i++) {
                        $movingunits['u' . (($vtribe - 1) * 10 + $i)] += $ret['t' . $i];
                    }
                }
            }
        }

        $settlerarray = $this->getMovement(5, $id, 0);
        if (!empty($settlerarray)) {
            $movingunits['u' . ($vtribe * 10)] += 3 * count($settlerarray);
        }

        return $movingunits;
    }

    function getWW()
    {
        $tableName = "fdata";
        $where = "f99t = ?";
        $values = array(40);
        $result = $this->conn->select($tableName, "*", $where, $values);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    function getWWLevel($vref)
    {
        $tableName = "fdata";
        $columns = array("f99");
        $where = "vref = ?";
        $values = array($vref);
        $result = $this->conn->select($tableName, $columns, $where, $values);
        if ($result && $this->conn->numRows($result) > 0) {
            $row = $this->conn->fetchAssoc($result);
            return $row['f99'];
        } else {
            return false;
        }
    }

    function getWWOwnerID($vref)
    {
        $tableName = "vdata";
        $columns = array("owner");
        $where = "wref = ?";
        $values = array($vref);
        $result = $this->conn->select($tableName, $columns, $where, $values);
        if ($result && $this->conn->numRows($result) > 0) {
            $row = $this->conn->fetchAssoc($result);
            return $row['owner'];
        } else {
            return false;
        }
    }

    function getUserAllianceID($id)
    {
        $tableName = "users";
        $columns = array("alliance");
        $where = "id = ?";
        $values = array($id);
        $result = $this->conn->select($tableName, $columns, $where, $values);
        if ($result && $this->conn->numRows($result) > 0) {
            $row = $this->conn->fetchAssoc($result);
            return $row['alliance'];
        } else {
            return false;
        }
    }

    function getWWName($vref)
    {
        $tableName = "fdata";
        $columns = array("wwname");
        $where = "vref = ?";
        $values = array($vref);
        $result = $this->conn->select($tableName, $columns, $where, $values);
        if ($result && $this->conn->numRows($result) > 0) {
            $row = $this->conn->fetchAssoc($result);
            return $row['wwname'];
        } else {
            return false;
        }
    }

    function submitWWname($vref, $name)
    {
        $tableName = "fdata";
        $data = array("wwname" => $name);
        $where = "vref = ?";
        $values = array($vref);
        return $this->conn->update($tableName, $data, $where, $values);
    }

// Funções relacionadas a medalhas
    function addclimberpop($user, $cp)
    {
        $tableName = "users";
        $data = array("Rc" => "Rc + $cp");
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function addclimberrankpop($user, $cp)
    {
        $tableName = "users";
        $data = array("clp" => "clp + $cp");
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function removeclimberrankpop($user, $cp)
    {
        $tableName = "users";
        $data = array("clp" => "clp - $cp");
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function updateoldrank($user, $cp)
    {
        $tableName = "users";
        $data = array("oldrank" => $cp);
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function setclimberrankpop($user, $cp)
    {
        $tableName = "users";
        $data = array("clp" => $cp);
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function removeclimberpop($user, $cp)
    {
        $tableName = "users";
        $data = array("Rc" => "Rc - $cp");
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function addclimberpopAlly($user, $cp)
    {
        $tableName = "alidata";
        $data = array("Rc" => "Rc + $cp");
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function addclimberrankpopAlly($user, $cp)
    {
        $tableName = "alidata";
        $data = array("clp" => "clp + $cp");
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function removeclimberrankpopAlly($user, $cp)
    {
        $tableName = "alidata";
        $data = array("clp" => "clp - $cp");
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function updateoldrankAlly($user, $cp)
    {
        $tableName = "alidata";
        $data = array("oldrank" => $cp);
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function removeclimberpopAlly($user, $cp)
    {
        $tableName = "alidata";
        $data = array("Rc" => "Rc - $cp");
        $where = "id = ?";
        $values = array($user);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function modifyCommence($id)
    {
        $time = time();
        $tableName = "training";
        $data = array("commence" => $time);
        $where = "id = ?";
        $values = array($id);
        return $this->conn->update($tableName, $data, $where, $values);
    }

    function getTrainingList()
    {
        $tableName = "training";
        $columns = "*";
        $where = "vref != ''";
        $trainingList = $this->conn->select($tableName, $columns, $where);
        return $trainingList;
    }

    function getNeedDelete()
    {
        $tableName = "deleting";
        $columns = "uid";
        $time = time();
        $where = "timestamp < ?";
        $values = array($time);
        $needDelete = $this->conn->select($tableName, $columns, $where, $values);
        return $needDelete;
    }

    function countUser()
    {
        $tableName = "users";
        $columns = "count(id)";
        $where = "id != 0";
        $countUser = $this->conn->select($tableName, $columns, $where);
        return $countUser[0]['count(id)'];
    }

    function countAlli()
    {
        $tableName = "alidata";
        $columns = "count(id)";
        $where = "id != 0";
        $countAlli = $this->conn->select($tableName, $columns, $where);
        return $countAlli[0]['count(id)'];
    }

    function RemoveXSS($val)
    {
        return htmlspecialchars($val, ENT_QUOTES);
    }

    function getWoodAvailable($wref)
    {
        $tableName = "vdata";
        $columns = "wood";
        $where = "wref = ?";
        $values = array($wref);
        $woodAvailable = $this->conn->select($tableName, $columns, $where, $values);
        return $woodAvailable[0]['wood'];
    }

    function getClayAvailable($wref)
    {
        $tableName = "vdata";
        $columns = "clay";
        $where = "wref = ?";
        $values = array($wref);
        $clayAvailable = $this->conn->select($tableName, $columns, $where, $values);
        return $clayAvailable[0]['clay'];
    }

    function getIronAvailable($wref)
    {
        $tableName = "vdata";
        $columns = "iron";
        $where = "wref = ?";
        $values = array($wref);
        $ironAvailable = $this->conn->select($tableName, $columns, $where, $values);
        return $ironAvailable[0]['iron'];
    }

    function getCropAvailable($wref)
    {
        $tableName = "vdata";
        $columns = "crop";
        $where = "wref = ?";
        $values = array($wref);
        $cropAvailable = $this->conn->select($tableName, $columns, $where, $values);
        return $cropAvailable[0]['crop'];
    }

    function Getowner($vid)
    {
        $tableName = "vdata";
        $columns = "owner";
        $where = "wref = ?";
        $values = array($vid);
        $ownerResult = $this->conn->select($tableName, $columns, $where, $values);
        return $ownerResult[0]['owner'];
    }

    public function debug($time, $uid, $debug_info)
    {
        $tableName = "debug_info";
        $data = array(
            'time' => $time,
            'uid' => $uid,
            'debug_info' => $debug_info
        );
        if ($this->conn->insert($tableName, $data)) {
            return $this->conn->getLastInsertedId();
        } else {
            return false;
        }
    }

    function poulateOasisdata()
    {
        $selectQuery = $this->conn->select()
            ->from("wdata")
            ->where("oasistype != 0");
        $result2 = $this->conn->query($selectQuery);

        while ($row = $result2->fetch_assoc()) {
            $worlid = $row['id'];
            switch ($row['oasistype']) {
                case 1:
                    $tt =  "1000,1000,1000,1000,1000,1000";
                    break;
                case 2:
                    $tt =  "2000,1000,1000,2000,1000,2000";
                    break;
                case 3:
                    $tt =  "2000,1000,1000,2000,2000,2000";
                    break;
                case 4:
                    $tt =  "1000,1000,1000,1000,1000,1000";
                    break;
                case 5:
                    $tt =  "1000,2000,1000,2000,1000,2000";
                    break;
                case 6:
                    $tt =  "1000,2000,1000,2000,1000,2000";
                    break;
                case 7:
                    $tt =  "1000,1000,1000,1000,1000,1000";
                    break;
                case 8:
                    $tt =  "1000,1000,2000,2000,1000,2000";
                    break;
                case 9:
                    $tt =  "1000,1000,2000,2000,2000,2000";
                    break;
                case 10:
                    $tt =  "1000,1000,1000,1000,1000,1000";
                    break;
                case 11:
                    $tt =  "1000,1000,1000,2000,2000,2000";
                    break;
                case 12:
                    $tt =  "1000,1000,1000,2000,2000,2000";
                    break;
            }
            $basearray = $this->getOMInfo($worlid);
            $insertQuery = $this->conn->insert("odata")
                ->set(array(
                    'id' => $basearray['id'],
                    'oasistype' => $basearray['oasistype'],
                    'conqured' => 0,
                    'r0' => $tt,
                    'r1' => time(),
                    'r2' => time(),
                    'r3' => 100,
                    'r4' => 3,
                    'updateTime' => time(),
                    'loyalty' => 3,
                    'type' => 'Unoccupied oasis',
                    'name' => 'Unoccupied oasis'
                ));
            $this->conn->query($insertQuery);
        }
    }

    public function getAvailableExpansionTraining()
    {
        global $building, $session, $technology, $village;

        // Obtém o número máximo de slots de expansão disponíveis no aldeamento
        $selectQuery = $this->conn->select(array($this->conn->raw("(IF(exp1=0,1,0)+IF(exp2=0,1,0)+IF(exp3=0,1,0))")))
            ->from("vdata")
            ->where('wref', '=', $village->wid);
        $maxslots = $this->conn->getValue($selectQuery);

        // Calcula os slots ocupados pelos Settlers e Chiefs
        $settlersQuery = $this->conn->select(array($this->conn->raw("(u10+u20+u30)")))
            ->from("units")
            ->where('vref', '=', $village->wid);
        $settlers = $this->conn->getValue($settlersQuery);

        $chiefsQuery = $this->conn->select(array($this->conn->raw("(u9+u19+u29)")))
            ->from("units")
            ->where('vref', '=', $village->wid);
        $chiefs = $this->conn->getValue($chiefsQuery);

        // Leva em consideração os movimentos de tropas
        $settlers += 3 * count($this->getMovement(5, $village->wid, 0));
        $chiefs += count($this->getMovement(5, $village->wid, 0));

        // Calcula os slots ocupados por tropas em movimento
        $currentMovements = array(3, 4);
        foreach ($currentMovements as $movementType) {
            $movementsQuery = $this->conn->select(array($this->conn->raw("(SUM(t10)+SUM(t9))")))
                ->from("movement")
                ->where('type', '=', $movementType)
                ->where('from', '=', $village->wid);
            $movements = $this->conn->getValue($movementsQuery);
            $settlers += $movements['SUM(t10)'];
            $chiefs += $movements['SUM(t9)'];
        }

        // Calcula os slots ocupados pelas tropas de reforço
        $reinforcementsQuery = $this->conn->select(array($this->conn->raw("(SUM(u10)+SUM(u20)+SUM(u30))"), $this->conn->raw("(SUM(u9)+SUM(u19)+SUM(u29))")))
            ->from("enforcement")
            ->where('from', '=', $village->wid);
        $reinforcements = $this->conn->getRow($reinforcementsQuery);
        $settlers += $reinforcements['SUM(u10)'];
        $chiefs += $reinforcements['SUM(u9)'];

        // Calcula os slots disponíveis para Settlers e Chiefs
        $settlerslots = $maxslots * 3 - $settlers - $chiefs * 3;
        $chiefslots = $maxslots - $chiefs - floor(($settlers + 2) / 3);

        // Verifica se a pesquisa para Chiefs está disponível para a tribo atual
        if (!$technology->getTech(($session->tribe - 1) * 10 + 9)) {
            $chiefslots = 0;
        }

        return array("chiefs" => $chiefslots, "settlers" => $settlerslots);
    }

    function addArtefact($vref, $owner, $type, $size, $name, $desc, $effect, $img)
    {
        $data = array(
            'vref' => $vref,
            'owner' => $owner,
            'type' => $type,
            'size' => $size,
            'conquered' => time(),
            'name' => $name,
            'desc' => $desc,
            'effect' => $effect,
            'img' => $img
        );
        $insertQuery = $this->conn->insert('artefacts')->set($data);
        return $this->conn->execute($insertQuery);
    }

    function getOwnArtefactInfo($vref)
    {
        $selectQuery = $this->conn->select()->from('artefacts')->where('vref', '=', $vref);
        return $this->conn->fetchRow($selectQuery);
    }

    function getOwnArtefactInfo2($vref)
    {
        $selectQuery = $this->conn->select()->from('artefacts')->where('vref', '=', $vref);
        return $this->conn->fetchAll($selectQuery);
    }

    function getOwnArtefactInfo3($uid)
    {
        $selectQuery = $this->conn->select()->from('artefacts')->where('owner', '=', $uid);
        return $this->conn->fetchAll($selectQuery);
    }

    function getOwnArtefactInfoByType($vref, $type)
    {
        $selectQuery = $this->conn->select()->from('artefacts')->where('vref', '=', $vref)->where('type', '=', $type)->orderBy('size');
        return $this->conn->fetchRow($selectQuery);
    }

    function getOwnArtefactInfoByType2($vref, $type)
    {
        $selectQuery = $this->conn->select()->from('artefacts')->where('vref', '=', $vref)->where('type', '=', $type);
        return $this->conn->fetchAll($selectQuery);
    }

    function getOwnUniqueArtefactInfo($id, $type, $size)
    {
        $selectQuery = $this->conn->select()->from('artefacts')->where('owner', '=', $id)->where('type', '=', $type)->where('size', '=', $size);
        return $this->conn->fetchRow($selectQuery);
    }

    function getOwnUniqueArtefactInfo2($id, $type, $size, $mode)
    {
        if (!$mode) {
            $selectQuery = $this->conn->select()->from('artefacts')->where('owner', '=', $id)->where('active', '=', 1)->where('type', '=', $type)->where('size', '=', $size);
        } else {
            $selectQuery = $this->conn->select()->from('artefacts')->where('vref', '=', $id)->where('active', '=', 1)->where('type', '=', $type)->where('size', '=', $size);
        }
        return $this->conn->fetchAll($selectQuery);
    }

    function getFoolArtefactInfo($type, $vid, $uid)
    {
        $selectQuery = $this->conn->select()->from('artefacts')->where('vref', '=', $vid)->where('type', '=', 8)->where('kind', '=', $type)->orWhere('owner', '=', $uid)->where('size', '>', 1)->where('active', '=', 1)->where('type', '=', 8)->where('kind', '=', $type);
        return $this->conn->fetchAll($selectQuery);
    }

    function claimArtefact($vref, $ovref, $id)
    {
        $updateQuery = $this->conn->update('artefacts')->set(array('vref' => $vref, 'owner' => $id))->where('vref', '=', $ovref);
        $this->conn->execute($updateQuery);
    }

    function getArtefactDetails($id)
    {
        $selectQuery = $this->conn->select()->from('artefacts')->where('id', '=', $id);
        return $this->conn->fetchRow($selectQuery);
    }

    function HeroFace($uid)
    {
        $selectQuery = $this->conn->select()->from('heroface')->where('uid', '=', $uid);
        return $this->conn->fetchRow($selectQuery);
    }

    function addHeroFace($uid, $bread, $ear, $eye, $eyebrow, $face, $hair, $mouth, $nose, $color)
    {
        $data = array(
            'beard' => $bread,
            'ear' => $ear,
            'eye' => $eye,
            'eyebrow' => $eyebrow,
            'face' => $face,
            'hair' => $hair,
            'mouth' => $mouth,
            'nose' => $nose,
            'color' => $color,
            'foot' => 0,
            'helmet' => 0,
            'horse' => 0,
            'leftHand' => 'leftHand',
            'rightHand' => 'rightHand'
        );
        $insertQuery = $this->conn->insert('heroface')->set($data);
        return $this->conn->execute($insertQuery);
    }

    function modifyHeroFace($uid, $column, $value)
    {
        $updateQuery = $this->conn->update('heroface')->set($column, $value)->where('uid', '=', $uid);
        return $this->conn->execute($updateQuery);
    }

    function modifyHeroXp($column, $value, $uid)
    {
        $updateQuery = $this->conn->update('hero')->set($column, $column . ' + ' . $value)->where('uid', '=', $uid);
        return $this->conn->execute($updateQuery);
    }

    public function hasBeginnerProtection($vid)
    {
        $stmt = $this->conn->prepare("SELECT u.protect FROM " . "users u INNER JOIN " . "vdata v ON u.id = v.owner WHERE v.wref = :vid");
        $stmt->bindParam(':vid', $vid);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($result)) {
            if (time() < $result['protect']) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function addCLP($uid, $clp)
    {
        $q = "UPDATE " . "users SET clp = clp + ? WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ii', $clp, $uid);
        return $stmt->execute();
    }

    function sendwlcMessage($client, $owner, $topic, $message, $send)
    {
        $time = time();
        $q = "INSERT INTO " . "mdata VALUES (0, ?, ?, ?, ?, 1, 0, ?, ?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('iisssi', $client, $owner, $topic, $message, $send, $time);
        return $stmt->execute();
    }

    function getLinks($id)
    {
        $q = 'SELECT * FROM ' . 'links WHERE `userid` = ? ORDER BY `pos` ASC';
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function removeLinks($id, $uid)
    {
        $q = "DELETE FROM " . "links WHERE `id` = ? AND `userid` = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ii', $id, $uid);
        return $stmt->execute();
    }

    function getFarmlist($uid)
    {
        $q = 'SELECT * FROM ' . 'farmlist WHERE owner = ? ORDER BY name ASC';
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $dbarray = $result->fetch_array(MYSQLI_ASSOC);

        if ($dbarray['id'] != 0) {
            return true;
        } else {
            return false;
        }
    }

    function getRaidList($id)
    {
        $q = "SELECT * FROM " . "raidlist WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    function getAllAuction()
    {
        $q = "SELECT * FROM " . "auction WHERE finish = 0";
        $result = $this->conn->query($q);
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    function getVilFarmlist($wref)
    {
        $q = 'SELECT * FROM ' . 'farmlist WHERE wref = ? ORDER BY wref ASC';
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $wref);
        $stmt->execute();
        $result = $stmt->get_result();
        $dbarray = $result->fetch_array(MYSQLI_ASSOC);

        if ($dbarray['id'] != 0) {
            return true;
        } else {
            return false;
        }
    }

    function delFarmList($id, $owner)
    {
        $q = "DELETE FROM " . "farmlist WHERE id = ? AND owner = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ii', $id, $owner);
        return $stmt->execute();
    }

    function delSlotFarm($id)
    {
        $q = "DELETE FROM " . "raidlist WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    function createFarmList($wref, $owner, $name)
    {
        $q = "INSERT INTO " . "farmlist (`wref`, `owner`, `name`) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('iis', $wref, $owner, $name);
        return $stmt->execute();
    }

    function addSlotFarm($lid, $towref, $x, $y, $distance, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10)
    {
        $q = "INSERT INTO " . "raidlist (`lid`, `towref`, `x`, `y`, `distance`, `t1`, `t2`, `t3`, `t4`, `t5`, `t6`, `t7`, `t8`, `t9`, `t10`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('iiiiiiiiiiiiiii', $lid, $towref, $x, $y, $distance, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10);
        return $stmt->execute();
    }

    function editSlotFarm($eid, $lid, $wref, $x, $y, $dist, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10)
    {
        $q = "UPDATE " . "raidlist SET lid = ?, towref = ?, x = ?, y = ?, t1 = ?, t2 = ?, t3 = ?, t4 = ?, t5 = ?, t6 = ?, t7 = ?, t8 = ?, t9 = ?, t10 = ? WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('iiiiiiiiiiiiiiii', $lid, $wref, $x, $y, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $eid);
        return $stmt->execute();
    }

    function getBerichte($uid)
    {
        $q = "SELECT id FROM " . "ndata WHERE uid = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $dbarray = $result->fetch_array(MYSQLI_ASSOC);
        return $dbarray['id'];
    }

    function removeOases($wref)
    {
        $q = "UPDATE " . "odata SET conqured = 0, owner = 3, name = 'آبادی تسخیر نشده' WHERE wref = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $wref);
        return $stmt->execute();
    }

    function getArrayMemberVillage($uid)
    {
        $q = 'SELECT a.wref, a.name, b.x, b.y FROM ' . 'vdata AS a LEFT JOIN ' . 'wdata AS b ON b.id = a.wref WHERE owner = ? ORDER BY capital DESC, pop DESC';
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $array = $result->fetch_all(MYSQLI_ASSOC);
        return $array;
    }

    function getNoticeData($nid)
    {
        $q = "SELECT * FROM " . "ndata WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $nid);
        $stmt->execute();
        $result = $stmt->get_result();
        $dbarray = $result->fetch_array(MYSQLI_ASSOC);
        return $dbarray['data'];
    }

    function setSilver($uid, $silver, $mode)
    {
        if (!$mode) {
            $q = "UPDATE " . "users SET silver = silver - ? WHERE id = ?";
        } else {
            $q = "UPDATE " . "users SET silver = silver + ? WHERE id = ?";
        }
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ii', $silver, $uid);
        return $stmt->execute();
    }

    function setNewSilver($id, $newsilver)
    {
        $q = "UPDATE " . "auction SET newsilver = ? WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('ii', $newsilver, $id);
        return $stmt->execute();
    }

    function getAuctionSilver($uid)
    {
        $q = "SELECT * FROM " . "auction WHERE uid = ? AND finish = 0";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    function getAuctionData($id)
    {
        $q = "SELECT * FROM " . "auction WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    function delAuction($id)
    {
        $aucData = $this->getAuctionData($id);
        $btype = $aucData['btype'];
        if ($btype >= 7 || $btype != 12 || $btype != 13) {
            $this->editHeroNum($aucData['itemid'], $aucData['num'], 1);
            $this->editProcItem($aucData['itemid'], 0);
            $q = "DELETE FROM " . "auction WHERE id = ? AND finish = 0";
        } else {
            $this->editProcItem($aucData['itemid'], 0);
            $q = "DELETE FROM " . "auction WHERE id = ? AND finish = 0";
        }
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    function getAuctionUser($uid)
    {
        $q = "SELECT * FROM " . "auction WHERE owner = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_array(MYSQLI_ASSOC);
    }

    function addAuction($owner, $itemid, $btype, $type, $amount)
    {
        $time = time() + AUCTIONTIME;
        if ($btype == 7 || $btype == 8 || $btype == 9 || $btype == 10 || $btype == 11 || $btype == 13 || $btype == 14) {
            $silver = $amount;

            $itemData = $this->getItemData($itemid);
            if ($amount == $itemData['num']) {
                $q = "INSERT INTO " . "auction (`owner`, `itemid`, `btype`, `type`, `num`, `uid`, `bids`, `silver`, `newsilver`, `time`, `finish`) VALUES (?, ?, ?, ?, ?, 0, 0, ?, ?, ?, 0)";
                $this->editProcItem($itemid, 1);
            } else {
                $this->editHeroNum($itemid, $amount, 0);
                $q = "INSERT INTO " . "auction (`owner`, `itemid`, `btype`, `type`, `num`, `uid`, `bids`, `silver`, `newsilver`, `time`, `finish`) VALUES (?, ?, ?, ?, ?, 0, 0, ?, ?, ?, 0)";
                $this->editProcItem($itemid, 0);
            }
        } else {
            $silver = 100;
            $q = "INSERT INTO " . "auction (`owner`, `itemid`, `btype`, `type`, `num`, `uid`, `bids`, `silver`, `time`, `finish`) VALUES (?, ?, ?, ?, ?, 0, 0, ?, ?, 0)";
            $this->editProcItem($itemid, 1);
        }

        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('iiiiiiiii', $owner, $itemid, $btype, $type, $amount, $silver, $silver, $time);
        return $stmt->execute();
    }

    function addBid($id, $uid, $newsilver)
    {
        $q = "UPDATE " . "auction SET uid = ?, silver = newsilver + 1, newsilver = ?, bids = bids + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('iii', $uid, $newsilver, $id);
        return $stmt->execute();
    }

    function removeBidNotice($id)
    {
        $q = "DELETE FROM " . "auction WHERE id = ?";
        $stmt = $this->conn->prepare($q);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    //TODO só copiei

    function addHeroItem($uid, $btype, $type, $num)
    {
        $q = "INSERT INTO " . "heroitems (`uid`, `btype`, `type`, `num`, `proc`) VALUES ('$uid', '$btype', '$type', '$num', 0)";
    }

    function checkHeroItem($uid, $btype)
    {
        $q = "SELECT * FROM " . "heroitems WHERE uid = '$uid' and btype = '$btype' and proc = 0";
        $result = mysql_query($q, $this->conn);
        $dbarray = mysql_fetch_array($result);
        if ($dbarray['btype'] == $btype) {
            return $dbarray['id'];
        } else {
            return false;
        }
    }

    function checkAttack($wref, $toWref)
    {
        $q = "SELECT * FROM " . "movement WHERE `from` = '$wref' AND `to` = '$toWref' AND `proc` = '0' AND `sort_type` = '3'";
        $result = mysql_query($q, $this->conn);
        if (mysql_num_rows($result)) {
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }

    function getHeroItemID($uid, $btype)
    {
        $q = "SELECT * FROM " . "heroitems where uid = " . $uid . " AND btype = " . $btype . "";
        $result = mysql_query($q, $this->conn);
        $dbarray = mysql_fetch_array($result);
        return $dbarray['id'];
    }

    function getHeroItemID2($uid, $btype, $type)
    {
        $q = "SELECT * FROM " . "heroitems where uid = " . $uid . " AND btype = " . $btype . " AND type = " . $type . "";
        $result = mysql_query($q, $this->conn);
        $dbarray = mysql_fetch_array($result);
        return $dbarray['id'];
    }

    function getItemData($id)
    {
        $q = "SELECT * FROM " . "heroitems WHERE id = $id";
    }

    function editHeroNum($id, $num, $mode)
    {
        if ($mode == 0) {
            $q = "UPDATE " . "heroitems set num = num - $num where id = $id and proc = 0";
        } elseif ($mode == 1) {
            $q = "UPDATE " . "heroitems set num = num + $num where id = $id and proc = 0";
        } else {
            $q = "UPDATE " . "heroitems set num = $num where id = $id and proc = 0";
        }
    }

    function editHeroNum2($id, $num, $mode)
    {
        if ($mode == 0) {
            $q = "UPDATE " . "heroitems set num = num - $num where id = $id";
        } elseif ($mode == 1) {
            $q = "UPDATE " . "heroitems set num = num + $num where id = $id";
        } else {
            $q = "UPDATE " . "heroitems set num = $num where id = $id";
        }
    }

    function editHeroType($id, $type, $mode)
    {
        if ($mode == 0) {
            $q = "UPDATE " . "heroitems set type = type - $type where id = $id";
        } elseif ($mode == 1) {
            $q = "UPDATE " . "heroitems set type = type + $type where id = $id";
        } else {
            $q = "UPDATE " . "heroitems set type = $type where id = $id";
        }
    }

    function editProcItem($id, $mode)
    {
        if ($mode == 0) {
            $q = "UPDATE " . "heroitems set proc = 0 where id = $id";
        } else {
            $q = "UPDATE " . "heroitems set proc = 1 where id = $id";
        }
    }

    function editBid($id, $silver)
    {
        $q = "UPDATE " . "auction set silver = $silver where id = $id";
    }

    function checkBid($id, $newsilver)
    {
        $q = "SELECT * FROM " . "auction WHERE id = '$id'";
        $result = mysql_query($q, $this->conn);
        $dbarray = mysql_fetch_array($result);

        if ($dbarray['newsilver'] >= $newsilver) {
            return false;
        } else {
            return true;
        }
    }

    function getBidData($id)
    {
        $q = "SELECT * FROM " . "auction WHERE id = $id";
    }

    function setHeroInventory($uid, $field, $value)
    {
        $q = "UPDATE " . "heroinventory set $field = '$value' where uid = $uid";
    }

    function getHeroInventory($uid)
    {
        $q = "SELECT * FROM " . "heroinventory WHERE uid = $uid";
    }

    function getHeroData($uid)
    {
        $q = "SELECT * FROM " . "hero WHERE uid = $uid";
    }

    function getHeroData2($uid)
    {
        $q = "SELECT * FROM " . "hero WHERE dead = 0 and uid = $uid";
    }

    function getHeroData3($uid)
    {
        $q = "SELECT * FROM " . "hero WHERE dead = 0 and hide = 0 and uid = $uid";
    }

    function getFLData($id)
    {
        $q = "SELECT * FROM " . "farmlist where id = $id";
    }

    function getHeroField($uid, $field)
    {
        $q = "SELECT " . $field . " FROM " . "hero WHERE uid = $uid";
        $result = mysql_query($q, $this->conn);
        $dbarray = mysql_fetch_array($result);
        return $dbarray[$field];
    }

    function getVFH($uid)
    {
        $q = "SELECT wref FROM " . "vdata WHERE owner = $uid and capital = 1";
        $result = mysql_query($q, $this->conn);
        $dbarray = mysql_fetch_array($result);
        return $dbarray['wref'];
    }

    function addAdventure($wref, $uid)
    {
        $time = time() + (3600 * 120);
        $ddd = rand(0, 3);
        if ($ddd == 1) {
            $dif = 1;
        } else {
            $dif = 0;
        }
        $sql = mysql_query("SELECT * FROM " . "wdata ORDER BY id DESC LIMIT 1");
        $lastw = 641601;
        if (($wref - 10000) <= 10) {
            $w1 = rand(10, ($wref + 10000));
        } elseif (($wref + 10000) >= $lastw) {
            $w1 = rand(($wref - 10000), ($lastw - 1000));
        } else {
            $w1 = rand(($wref - 10000), ($wref + 10000));
        }

        $q = "INSERT into " . "adventure (`wref`, `uid`, `dif`, `time`, `end`) values ('$w1', '$uid', '$dif', '$time', 0)";
    }

    function addHero($uid)
    {
        $time = time();
        $hash = md5($time);
        $q = "INSERT into " . "hero (`uid`, `wref`, `level`, `speed`, `points`, `experience`, `dead`, `health`, `power`, `offBonus`, `defBonus`, `product`, `r0`, `autoregen`, `lastupdate`, `lastadv`, `hash`) values
				('$uid', 0, 0, '7', 0, '2', 0, '100', '0', 0, 0, '4', '1', '10', '$time', '$time', '$hash')";
    }

    function addNewProc($uid, $npw, $nemail, $act, $mode)
    {
        $time = time();
        if (!$mode) {
            $q = "INSERT into " . "newproc (uid, npw, act, time, proc) values ('$uid', '$npw', '$act', '$time', 0)";
        } else {
            $q = "INSERT into " . "newproc (uid, nemail, act, time, proc) values ('$uid', '$nemail', '$act', '$time', 0)";
        }

    }

    function checkProcExist($uid)
    {
        $q = "SELECT * FROM " . "newproc where uid = '$uid' and proc = 0";
        $result = mysql_query($q, $this->conn);
        if (mysql_num_rows($result)) {
            return false;
        } else {
            return true;
        }
    }

    function removeProc($uid)
    {
        $q = "DELETE FROM " . "newproc where uid = $uid";
    }

    function checkBan($uid)
    {
        $q = "SELECT * FROM " . "banlist WHERE uid = $uid";
        $result = mysql_query($q, $this->conn);
        if (mysql_num_rows($result)) {
            return true;
        } else {
            return false;
        }
    }

    function getNewProc($uid)
    {
        $q = "SELECT * FROM " . "newproc WHERE uid = $uid";
        $result = mysql_query($q, $this->conn);
        if (mysql_num_rows($result)) {
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }

    function getAdventure($uid, $wref)
    {
        $q = "SELECT * FROM " . "adventure WHERE uid = $uid and wref = '" . $wref . "'";
        $result = mysql_query($q, $this->conn);
        if (mysql_num_rows($result)) {
            return mysql_fetch_array($result);
        } else {
            return false;
        }
    }

    function editTableField($table, $field, $value, $refField, $ref)
    {
        $q = "UPDATE " . "" . $table . " set $field = '$value' where " . $refField . " = '$ref'";
    }

    function HeroItemsNum($uid)
    {
        $q = "SELECT * FROM " . "heroitems where uid = '$uid'";
    }

    function addHeroinventory($uid)
    {
        $q = "INSERT into " . "heroinventory (`uid`) values ('$uid')";
    }

    function config()
    {
        $q = "SELECT * FROM " . "config";
    }

    function getAllianceDipProfile($aid, $type)
    {
        $q = "SELECT * FROM " . "diplomacy WHERE alli1 = '$aid' AND type = '$type' AND accepted = '1'";
        $result = mysql_query($q, $this->conn);
        if (mysql_num_rows($result) == 0) {
            $q2 = "SELECT * FROM " . "diplomacy WHERE alli2 = '$aid' AND type = '$type' AND accepted = '1'";
            $result2 = mysql_query($q2, $this->conn);
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

    public function canClaimArtifact($vref, $type)
    {
        $DefenderFields = $this->getResourceLevel($vref);
        for ($i = 19; $i <= 38; $i++) {
            if ($AttackerFields['f' . $i . 't'] == 27) {
                $defcanclaim = FALSE;
                $defTresuaryLevel = $AttackerFields['f' . $i];
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

    function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
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

    function getCropProdstarv($wref)
    {
        global $bid4, $bid8, $bid9, $sesion, $technology;

        $basecrop = $grainmill = $bakery = 0;
        $owner = $this->getVillageField($wref, 'owner');
        $bonus = $this->getUserField($owner, b4, 0);

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
        $q = "SELECT type FROM `" . "odata` WHERE conqured = $wref";
        $oasis = $this->query_return($q);
        foreach ($oasis as $oa) {
            switch ($oa['type']) {
                case 1:
                case 2:
                    $wood += 1;
                    break;
                case 3:
                    $wood += 1;
                    $cropo += 1;
                    break;
                case 4:
                case 5:
                    $clay += 1;
                    break;
                case 6:
                    $clay += 1;
                    $cropo += 1;
                    break;
                case 7:
                case 8:
                    $iron += 1;
                    break;
                case 9:
                    $iron += 1;
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

    function getFieldDistance($worlid)
    {
        $q = "SELECT * FROM " . "vdata where owner > 4 and wref != $worlid";
        $array = $this->query_return($q);
        $coor = $this->getCoor($worlid);
        $x1 = intval($coor['x']);
        $y1 = intval($coor['y']);
        $prevdist = 0;
        $q2 = "SELECT * FROM " . "vdata where owner = 4";
        $array2 = mysql_fetch_array(mysql_query($q2));
        $vill = $array2['wref'];
        if (mysql_num_rows(mysql_query($q)) > 0) {
            foreach ($array as $village) {
                $coor2 = $this->getCoor($village['wref']);
                $max = 2 * WORLD_MAX + 1;
                $x2 = intval($coor2['x']);
                $y2 = intval($coor2['y']);
                $distanceX = min(abs($x2 - $x1), abs($max - abs($x2 - $x1)));
                $distanceY = min(abs($y2 - $y1), abs($max - abs($y2 - $y1)));
                $dist = sqrt(pow($distanceX, 2) + pow($distanceY, 2));
                if ($dist < $prevdist or $prevdist == 0) {
                    $prevdist = $dist;
                    $vill = $village['wref'];
                }
            }
        }
        return $vill;
    }

    function addGeneralAttack($casualties)
    {
        $time = time();
        $q = "INSERT INTO " . "general values (0,'$casualties','$time',1)";
    }

    function getAttackByDate($time)
    {
        $q = "SELECT * FROM " . "general where shown = 1";
        $result = $this->query_return($q);
        $attack = 0;
        foreach ($result as $general) {
            if (date("j. M", $time) == date("j. M", $general['time'])) {
                $attack += 1;
            }
        }
        return $attack;
    }

    function getAttackCasualties($time)
    {
        $q = "SELECT * FROM " . "general where shown = 1";
        $result = $this->query_return($q);
        $casualties = 0;
        foreach ($result as $general) {
            if (date("j. M", $time) == date("j. M", $general['time'])) {
                $casualties += $general['casualties'];
            }
        }
        return $casualties;
    }

    function addFriend($uid, $column, $friend)
    {
        $q = "UPDATE " . "users SET $column = $friend WHERE id = $uid";
    }

    function deleteFriend($uid, $column)
    {
        $q = "UPDATE " . "users SET $column = 0 WHERE id = $uid";
    }

    function checkFriends($uid)
    {
        $user = $this->getUserArray($uid, 1);
        for ($i = 0; $i <= 19; $i++) {
            if ($user['friend' . $i] == 0 && $user['friend' . $i . 'wait'] == 0) {
                for ($j = $i + 1; $j <= 19; $j++) {
                    $k = $j - 1;
                    if ($user['friend' . $j] != 0) {
                        $friend = $this->getUserField($uid, "friend" . $j, 0);
                        $this->addFriend($uid, "friend" . $k, $friend);
                        $this->deleteFriend($uid, "friend" . $j);
                    }
                    if ($user['friend' . $j . 'wait'] == 0) {
                        $friendwait = $this->getUserField($uid, "friend" . $j . "wait", 0);
                        $this->addFriend($sessionuid, "friend" . $k . "wait", $friendwait);
                        $this->deleteFriend($uid, "friend" . $j . "wait");
                    }
                }
            }
        }
    }

    function addPrisoners($worlid, $from, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11)
    {
        $q = "INSERT INTO " . "prisoners values (0,$worlid,$from,$t1,$t2,$t3,$t4,$t5,$t6,$t7,$t8,$t9,$t10,$t11)";
    }

    function updatePrisoners($worlid, $from, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $t11)
    {
        $q = "UPDATE " . "prisoners set t1 = t1 + $t1, t2 = t2 + $t2, t3 = t3 + $t3, t4 = t4 + $t4, t5 = t5 + $t5, t6 = t6 + $t6, t7 = t7 + $t7, t8 = t8 + $t8, t9 = t9 + $t9, t10 = t10 + $t10, t11 = t11 + $t11 where wid = $worlid and from = $from";
    }

    function getPrisoners($worlid)
    {
        $q = "SELECT * FROM " . "prisoners where wref = $worlid";
    }

    function getPrisoners2($worlid, $from)
    {
        $q = "SELECT * FROM " . "prisoners where wref = $worlid and from = $from";
    }

    function getPrisonersByID($id)
    {
        $q = "SELECT * FROM " . "prisoners where id = $id";
    }

    function getPrisoners3($from)
    {
        $q = "SELECT * FROM " . "prisoners where from = $from";
    }

    function deletePrisoners($id)
    {
        $q = "DELETE from " . "prisoners where id = '$id'";
    }


}