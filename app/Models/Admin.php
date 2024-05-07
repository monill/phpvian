<?php

namespace PHPvian\Models;

use PHPvian\Libs\Connection;
use PHPvian\Libs\Database;

class Admin
{
    private $conn, $db;

    public function __construct()
    {
        $this->conn = new Connection();
        $this->db = new Database();
    }

    public function login($username, $password)
    {
        $result = $this->conn->select('users', '*', 'username = :username AND access >= :access', [':username' => $username, ':access' => MULTIHUNTER]);

        if ($result && $result['password'] == md5($password)) {
            $this->insertAdminLog("$username logged in (IP: <b>{$_SERVER['REMOTE_ADDR']}</b>)");
            return true;
        } else {
            $this->insertAdminLog("<b>IP: {$_SERVER['REMOTE_ADDR']} tried to log in with username <u>{$username}</u> but access was denied!</b>");
            return false;
        }
    }

    public function saveGold($req)
    {
        $data = [
            'gold' => $req['gold'],
            'boughtgold' => $req['boughtgold'],
            'giftgold' => $req['giftgold'],
            'transferedgold' => $req['transferedgold'],
            'seggold' => $req['seggold'],
            'usedgold' => $req['usedgold']
        ];
        $this->conn->update('users', $data, 'id = :uid', [':uid' => $req['uid']]);
    }

    public function saveSilver($req)
    {
        $data = [
            'silver' => $req['silver'],
            'giftsilver' => $req['giftsilver'],
            'gessilver' => $req['gessilver'],
            'sisilver' => $req['sisilver'],
            'bisilver' => $req['bisilver']
        ];
        $this->conn->update('users', $data, 'id = :uid', [':uid' => $req['uid']]);
    }

    public function reviveHeroNow($uid)
    {
        $varray = $this->db->getProfileVillages($uid);
        foreach ($varray as $vil) {
            $data = ['hero' => 0];
            $where = '`vref` = :wref';
            $bind = [':wref' => $vil['wref']];
            $this->conn->update('units', $data, $where, $bind);
            $this->conn->update('enforcement', $data, $where, $bind);
            $this->conn->update('trapped', $data, $where, $bind);
            $this->conn->update('attacks', ['t11' => 0], '`from` = :wref', $bind);
        }

        $heroData = $this->db->getHero($uid);
        $this->conn->update('units', ['hero' => 1], '`vref` = :wref', [':wref' => $heroData['wref']]);
        $this->db->modifyHero($uid, 0, 'health', 100, 0);
    }

    public function saveTroops($req)
    {
        $id = $req['id'];
        $village = $this->db->getVillage($id);
        $user = $this->db->getUser($village['owner'], 1);

        $data = [];
        if ($user) {
            $tribe = $user['tribe'];
            $start = ($tribe - 1) * 10 + 1;
            for ($i = $start; $i <= $start + 9; $i++) {
                $data["u$i"] = $req["u$i"];
            }
            $this->conn->update('units', $data, 'vref = :vref', [':vref' => $id]);
            $this->insertAdminLog("Changed troop amount in village <a href='index.php?p=village&did=$id'>" . $village['name'] . "</a>");
        }
    }

    public function editGoldClub($uid, $v)
    {
        $this->conn->update('users', ['goldclub' => $v], '`id` = :uid', [':uid' => $uid]);
    }

    public function recountPopUser($uid)
    {
        $villages = $this->db->getProfileVillages($uid);
        foreach ($villages as $village) {
            $vid = $village['wref'];
            $this->recountPop($vid);
        }
    }

    public function recountPop($vid)
    {
        $fdata = $this->db->getResourceLevel($vid);
        $popTot = 0;
        for ($i = 1; $i <= 40; $i++) {
            $lvl = $fdata["f$i"];
            $building = $fdata["f${i}t"];
            if ($building) {
                $popTot += $this->buildingPOP($building, $lvl);
            }
        }
        $lvl = $fdata["f99"];
        $building = $fdata["f99t"];
        if ($building) {
            $popTot += $this->buildingPOP($building, $lvl);
        }
        $this->conn->update('vdata', ['pop' => $popTot], 'wref = :vid', [':vid' => $vid]);
    }

    public function buildingPOP($f, $lvl)
    {
        $dataarray = $this->db->getBuildingData($f);
        $popT = 0;
        for ($i = 0; $i <= $lvl; $i++) {
            $popT += $dataarray[$i]['pop'];
        }
        return $popT;
    }

    public function addVillage($post)
    {
        $wid = $this->getWref($post['x'], $post['y']);
        $uid = $post['uid'];

        if ($this->isVillageExists($wid)) {
            return;
        }

        $this->insertAdminLog("Added new village <b><a href='index.php?p=village&did=$wid'>$wid</a></b> to user <b><a href='index.php?p=player&uid=$uid'>$uid</a></b>");

        $this->db->setFieldTaken($wid);
        $this->db->addVillage($wid, $uid, 'New Village', 0);
        $this->db->addResourceFields($wid, $this->db->getVillageType($wid));
        $this->db->addUnits($wid);
        $this->db->addTech($wid);
        $this->db->addABTech($wid);
    }

    private function isVillageExists($wid)
    {
        $status = $this->db->getVillageState($wid);
        return $status == 1;
    }

    private function insertAdminLog($message)
    {
        $this->conn->insert('admin_log', [
            'user_id' => $_SESSION['id'] ?? 0,
            'log_message' => $message,
            'timestamp' => time()
        ]);
    }

    public function punish($post)
    {
        $uid = $post['uid'];
        $villages = $this->db->getProfileVillages($uid);
        $admid = $post['admid'];
        $user = $this->db->getUser($uid, 1);

        foreach ($villages as $village) {
            $vid = $village['wref'];
            if ($post['punish']) {
                $popOld = $village['pop'];
                $proc = 100 - $post['punish'];
                $pop = max(floor(($popOld / 100) * $proc), 2);
                $this->punishBuilding($vid, $proc, $pop);
            }
            if ($post['del_troop']) {
                $unit = $this->db->getFirstUnitByTribe($user['tribe']);
                $this->delUnits($vid, $unit);
            }
            if ($post['clean_ware']) {
                $this->db->cleanWarehouse($vid);
            }
        }
        $this->insertAdminLog("Punished user: <a href='index.php?p=player&uid=$uid'>$uid</a> with <b>-{$post['punish']}%</b> population");
    }

    public function punishBuilding($vid, $proc, $pop)
    {
        $this->conn->update('vdata', ['pop' => $pop], 'wref = ?', [$vid]);
        $fdata = $this->db->getResourceLevel($vid);
        foreach ($fdata as $key => $level) {
            if (str_starts_with($key, 'f') && $level > 1) {
                $zm = max(($level / 100) * $proc, 1);
                $zm = floor($zm);
                $fieldNumber = substr($key, 1);
                $this->conn->update('fdata', ["f$fieldNumber" => $zm], 'vref = ?', [$vid]);
            }
        }
    }

    public function delUnits($vid, $unit)
    {
        for ($i = $unit; $i <= 9 + $unit; $i++) {
            $this->delUnits2($vid, $i);
        }
    }

    public function delUnits2($vid, $unit)
    {
        $this->conn->update('units', ["u$unit" => 0], 'vref = ?', [$vid]);
    }

    public function delPlayer($uid, $pass)
    {
        $currentUser = $this->db->getUserBySession();
        if (!$currentUser || !$this->checkPass($pass, $currentUser['id'])) {
            return;
        }

        $username = $this->db->getUserField($uid, "username", 0);
        $villages = $this->db->getProfileVillages($uid);
        foreach ($villages as $village) {
            $this->delVillage($village['wref']);
        }

        $this->insertAdminLog("Deleted user <a>$username</a>");
        $this->conn->delete('users', 'id = ?', [$uid]);
    }

    public function checkPass($password, $uid)
    {
        $userData = $this->conn->select('users', 'password', 'id = ?', [$uid]);
        return $userData && $userData['password'] === md5($password);
    }

    public function delVillage($wref)
    {
        $isCapital = $this->conn->select('vdata', 'capital', 'wref = ?', [$wref]);

        if ($isCapital && $isCapital['capital'] == 1) {
            $this->insertAdminLog("Deleted village <b>$wref</b>");
            $this->conn->delete('vdata', 'wref = ? AND capital = ?', [$wref, 1]);
            $this->conn->delete('units', 'vref = ?', [$wref]);
            $this->conn->delete('bdata', 'wid = ?', [$wref]);
            $this->conn->delete('abdata', 'wid = ?', [$wref]);
            $this->conn->delete('fdata', 'vref = ?', [$wref]);
            $this->conn->delete('training', 'vref = ?', [$wref]);
            $this->conn->delete('movement', '`from` = ?', [$wref]);
            $this->conn->update('wdata', ['occupied' => 0], 'id = ?', [$wref]);
        }
    }

    public function getUserActive()
    {
        $time = time() - (60 * 5);
        return $this->conn->select('users', '*', 'timestamp > ? AND username != ?', [$time, 'support']);
    }

    public function getAllUsers()
    {
        return $this->conn->select('users', '*', 'username != ?', ['support']);
    }

    public function delBan($uid)
    {
        $name = $this->db->getUserField($uid, "username", 0);
        $this->insertAdminLog("Unbanned user <a href='index.php?p=player&uid=$uid'>$name</a>");
        $this->conn->update('users', ['access' => USER], 'id = ?', [$uid]);
        $this->conn->update('banlist', ['active' => 0], 'uid = ?', [$uid]);
    }

    public function addBan($uid, $end, $reason)
    {
        $name = $this->db->getUserField($uid, "username", 0);
        $this->insertAdminLog("Banned user <a href='index.php?p=player&uid=$uid'>$name</a>");
        $this->conn->update('users', ['access' => 0], 'id = ?', [$uid]);
        $admin = $_SESSION['id'] ?? 0;
        $this->conn->insert('banlist', ['uid' => $uid, 'name' => $name, 'reason' => $reason, 'time' => time(), 'end' => $end, 'admin' => $admin, 'active' => 1]);
    }

    public function searchPlayer($player)
    {
        return $this->conn->select('users', '*', "username LIKE ? AND username != ?", ["%$player%", 'support']);
    }

    public function searchEmail($email)
    {
        return $this->conn->select('users', 'id, email', "email LIKE ? AND username != ?", ["%$email%", 'support']);
    }

    public function searchVillage($village)
    {
        return $this->conn->select('vdata', '*', "`name` LIKE '%$village%' OR `wref` LIKE '%$village%'");
    }

    public function searchAlliance($alliance)
    {
        return $this->conn->select('alidata', '*', "`name` LIKE '%$alliance%' OR `tag` LIKE '%$alliance%' OR `id` LIKE '%$alliance%'");
    }

    public function searchIp($ip)
    {
        return $this->conn->select('login_log', '*', "`ip` LIKE '%$ip%'");
    }

    public function searchBanned()
    {
        return $this->conn->select('banlist', '*', 'active = 1');
    }

    public function delBanned()
    {
        return $this->conn->select('banlist');
    }

}