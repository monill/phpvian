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
            $reg2 = $this->database->checkname($userID);
            $name = $reg2['username'];
            Cookie::set('COOKUSR', $name, 7200);
            $this->database->addHero($userID);
            $this->database->addHeroFace($userID);
            $this->database->updateUserField($userID, 'activate', 'NULL', 1);
            $this->database->settribe($tribe, $userID);

            $this->generateBase($sector, $userID, $name);

            $this->database->modifyUnit($this->database->getVFH($userID), 'hero', 1, 1);
            $this->database->modifyHero($userID, 0, 'wref', $this->database->getVFH($userID));
            for ($s = 1; $s <= 3; $s++) {
                $this->database->addAdventure($this->database->getVFH($userID), $userID);
            }
            $this->database->setreg2($userID);
            $this->database->modifyGold($userID, 40, 1);
            $this->conn->from('users')->set('protect', time() + (setting('minprotecttime') * 2))->where('id = :userID', [':userID' => $userID])->update();
            $this->conn->insert('users_setting', ['id' => $userID]);
            $this->conn->from('users')->set('plus', time() + 21600)->where('id = :userID', [':userID' => $userID])->update();

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
        $this->database->updateUserField($userID, 'access', 2, 1);
        $this->database->updateUserField($userID, 'location', 'NULL', 1);

        $this->message->sendWelcome($userID, $username);
    }
}