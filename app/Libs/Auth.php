<?php

namespace PHPvian\Libs;

use PHPvian\Models\Logging;

class Auth
{
    private $generator, $database, $conn, $logging;
    private $time;
    private $userarray;
    private $userinfo;
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
        Session::set('logged_in', true);

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

        $user = $this->conn->select('sessid')->from('users')->where('username = :username', [':username' => $_SESSION['username']])->first();
        if (strlen($user['sessid'] ?? '') > 134) {
            $this->database->updateUserField($_SESSION['username'], 'sessid', '(NULL)', 0);
        }

        $sessid = $user['sessid'] != '' ? $user['sessid'] . '+' . $_SESSION['sessid'] : $_SESSION['sessid'];
        $this->database->updateUserField($_SESSION['userID'], 'sessid', $sessid, 0);

        $ua = $_SERVER['HTTP_USER_AGENT'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $id = session_id();
        $_SESSION['hash'] = htmlspecialchars(sha1("$ua $ip $id"));

        redirect('/resources');
    }

    private function populateVar()
    {
        $user = $this->database->getUser($_SESSION['username'], 1);
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
        $_SESSION['ok'] = $user['ok'];
        $this->time = time();
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
        if ($herodetail['lastadv'] <= ($this->time - $aday)) {
            if ($herodetail['lastadv'] <= $this->time - $tenday) {
                $herodetail['lastadv'] = $this->time - $tenday + $aday;
            }
            $this->database->addAdventure($this->database->getVFH($herodetail['uid']), $herodetail['uid']);
            $herodetail['lastadv'] += $aday;
        }
        $this->database->modifyHero2('lastadv', $herodetail['heroid'], 0, 0);
    }

    public function logout()
    {
        Session::set('logged_in', false);
        $user = $this->conn->select('id, username, sessid')
            ->from('users')
            ->where('username = :uname', [':uname' => Session::get('username')])
            ->limit(1)
            ->first();

        $this->database->updateUserField($user['id'], 'sessid', '(NULL)', 0);
        $this->database->UpdateOnline('logout', $user['username'], 0);

        Session::destroySession();

        redirect('/login');
    }

}
