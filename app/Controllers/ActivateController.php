<?php

namespace PHPvian\Controllers;

use PHPvian\Libs\Auth;
use PHPvian\Libs\Cookie;
use PHPvian\Libs\Database;
use PHPvian\Libs\Session;
use PHPvian\Models\Message;

class ActivateController extends Controller
{
    private $database, $message;

    public function __construct()
    {
        parent::__construct();
        $this->database = new Database();
        $this->message = new Message();
    }

    public function index()
    {
        return view('activate/index');
    }

    public function postActivate()
    {
        $character = input('character');
        $sector = input('sector');
        $userID = Session::get('userID');

        $tribes = [
            'romans' => 1,
            'teutons' => 2,
            'gauls' => 3
        ];
        $tribe = $tribes[$character];

        $reg2 = $this->database->checkreg($userID);

        if ($reg2['reg2'] == 1) {
            $this->database->settribe($tribe, $userID);
            $reg = $this->database->checkname($userID);
            $name = $reg['username'];
            Cookie::set('COOKUSR', $name, 7200);
            $this->database->addHero($userID);
            $this->database->addHeroFace($userID);
            $this->database->updateUserField($userID, 'activate', '(NULL)', 0);

            $this->generateBase($sector, $userID, $name);

            $this->database->modifyUnit($this->database->getVFH($userID), 'hero', 1, 1);
            $this->database->modifyHero2('wref', $this->database->getVFH($userID), $userID, 0);
            for ($s = 1; $s <= 3; $s++) {
                $this->database->addAdventure($this->database->getVFH($userID), $userID);
            }
            $this->database->setreg2($userID);
            $this->database->modifyGold($userID, 40, 1);
            $this->conn->upgrade('users', ['protect' => time() + (setting('minprotecttime') * 2)], 'id = :uid', [':uid' => $userID]);
            $this->conn->upgrade('users', ['plus' => time() + 21600], 'id = :uid', [':uid' => $userID]);
            $this->conn->insert('users_setting', ['id' => $userID]);

            (new Auth())->login($name);
        }
    }

    protected function generateBase($coordinate, $userID, $username)
    {
        $coordinateMap = [
            'random' => 0,
            'sw' => 1,
            'se' => 2,
            'ne' => 3,
            'nw' => 4,
        ];
        $sector = $coordinateMap[$coordinate];

        $wid = $this->database->generateBase($sector);
        $this->database->setFieldTaken($wid);
        $this->database->addVillage($wid, $userID, $username, 1);
        $this->database->addResourceFields($wid, $this->database->getVillageType($wid));
        $this->database->addUnits($wid);
        $this->database->addTech($wid);
        $this->database->addABTech($wid);
        $this->database->updateUserField($userID, 'access', 2, 0);
        $this->database->updateUserField($userID, 'location', 'NULL', 0);

        $this->message->sendWelcome($userID, $username);
    }
}
