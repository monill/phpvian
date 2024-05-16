<?php

namespace PHPvian\Libs;

use PHPvian\Models\Logging;

class Auth
{
    private bool $logged_in = false;
    private $generator, $database, $conn, $logging;
    private $time;
    private $userarray;
    private $username;
    private $email;
    private $gpack;
    private $uid;
    private $access;
    private $plus;
    private $goldclub;
    private $villages;
    private $tribe;
    private $isAdmin;
    private $alliance;
    private $checker;
    private $mchecker;
    private $gold;
    private $is_sitter;
    private $silver;
    private $cp;
    private $oldrank;
    private $bonus1;
    private $bonus2;
    private $bonus3;
    private $bonus4;

    public function __construct()
    {
        $this->generator = new Generator();
        $this->database = new Database();
        $this->conn = new Connection();
        $this->logging = new Logging();
    }

    public function login($username)
    {
        $this->logged_in = true;

        $_SESSION['sessid'] = md5($_SERVER['HTTP_ACCEPT_LANGUAGE'] . $_SERVER['REMOTE_ADDR']);
        $_SESSION['username'] = $username;
        $_SESSION['checker'] = $this->generator->generateRandStr(3);
        $_SESSION['mchecker'] = $this->generator->generateRandStr(5);
        $_SESSION['qst'] = $this->database->getUserField($_SESSION['username'], 'quest', 1);
        $_SESSION['chat_config'] = $this->database->getUserField($_SESSION['username'], 'chat_config', 1);

        if (!isset($_SESSION['wid'])) {
            $userId = $this->database->getUserField($_SESSION['username'], 'id', 1);
            $data = $this->conn->select('wref')->from('vdata')->where('owner = :owner', [':owner' => $userId])->first();
            $_SESSION['wid'] = $data['wref'];
        } else {
            if ($_SESSION['wid'] == '') {
                $userId = $this->database->getUserField($_SESSION['username'], 'id', 1);
                $data = $this->conn->select('wref')->from('vdata')->where('owner = :owner', [':owner' => $userId])->first();
                $_SESSION['wid'] = $data['wref'];
            }
        }

        $this->populateVar();

        $this->logging->addLoginLog($this->uid);
        $this->database->addActiveUser($_SESSION['username'], $this->time);

        $sessid = $this->conn->select('sessid')->from('users')->where('username = :username', [':username' => $_SESSION['username']])->first();
        if (strlen($sessid) > 134) {
            $this->database->updateUserField($_SESSION['username'], 'sessid', 'NULL', 0);
        }
        $sessid = $sessid ? $sessid . '+' . $_SESSION['sessid'] : $_SESSION['sessid'];
        $this->database->updateUserField($_SESSION['username'], 'sessid', $sessid, 0);

        $ua = $_SERVER['HTTP_USER_AGENT'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $id = session_id();
        $_SESSION['hash'] = htmlspecialchars(sha1("$ua $ip $id"));

        redirect('/village');
    }

    private function populateVar()
    {
        $user = $this->database->getUser($_SESSION['username']);
        $this->userarray = $this->userinfo = $user;
        $this->username = $user['username'];
        $this->email = $user['email'];
        $this->uid = $_SESSION['uid'] = $user['id'];
        $this->gpack = $user['gpack'];
        $this->access = $user['access'];
        $this->plus = ($user['plus'] > $this->time);
        $this->goldclub = $user['goldclub'];
        $this->villages = $this->database->getVillagesID($this->uid);
        $this->tribe = $user['tribe'];
        $this->isAdmin = $this->access >= 9;
        $this->alliance = $user['alliance'];
        $this->checker = $_SESSION['checker'];
        $this->mchecker = $_SESSION['mchecker'];
        $this->gold = $user['gold'];
        $this->is_sitter = $this->database->checkSitter($_SESSION['username']);
        $this->silver = $user['silver'];
        $this->cp = $user['cp'];
        $this->oldrank = $user['oldrank'];
        $this->evasion = $user['evasion'];
        $_SESSION['ok'] = $user['ok'];
        $time = $this->time;
        if ($user['b1'] > $this->time) {
            $this->bonus1 = 1;
        }
        if ($user['b2'] > $this->time) {
            $this->bonus2 = 1;
        }
        if ($user['b3'] > $this->time) {
            $this->bonus3 = 1;
        }
        if ($user['b4'] > $this->time) {
            $this->bonus4 = 1;
        }
        // Check hero adventure
        $herodetail = $this->database->getHero($this->uid);
        $aday = min(86400 / setting('speed'), 100);
        $tenday = min(86400 / setting('speed'), 600);
        $endat = time() + 900;
        if ($herodetail['lastadv'] <= ($time - $aday)) {
            if ($herodetail['lastadv'] <= $time - $tenday) {
                $herodetail['lastadv'] = $time - $tenday + $aday;
            }
            $dif = rand(0, 2);
            $this->database->addAdventure($this->database->getVFH($herodetail['uid']), $herodetail['uid'], $endat, $dif);
            $this->database->addAdventure($this->database->getVFH($herodetail['uid']), $herodetail['uid'], $endat + rand(10, 20), $dif);
            $herodetail['lastadv'] += $aday;
        }
        $this->database->modifyHero(0, $herodetail['heroid'], 'lastadv', $time);
    }


}