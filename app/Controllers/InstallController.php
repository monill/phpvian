<?php

namespace PHPvian\Controllers;

use Exception;
use PHPvian\Libs\Connection;
use PHPvian\Libs\Database;

class InstallController
{
    private $conn, $database, $databaseFile, $htaccessFile;

    public function __construct()
    {
        $this->conn = new Connection();
        $this->database = new Database();
        $this->databaseFile = dirname(__DIR__) . '/../storage/database.sql';
        $this->htaccessFile = dirname(__DIR__) . '/../public/.htaccess';
    }

    public function index()
    {
        return view('install/index');
    }

    public function requirements()
    {
        $extensions = ['BCMath', 'Ctype', 'Fileinfo', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'pdo_mysql', 'Tokenizer', 'XML', 'cURL', 'GD'];
        $phpversion = version_compare(PHP_VERSION, '8.1', '>=');
        return view('install/requirements', [
            'extensions' => $extensions,
            'phpversion' => $phpversion
        ]);
    }

    public function files()
    {
        $folderPermissions = [
            '/storage',
            '/storage/cache',
            '/storage/logs'
        ];

        $database = file_exists($this->databaseFile);
        $htaccess = file_exists($this->htaccessFile);

        return view('install/files', [
            'folderPermissions' => $folderPermissions,
            'database' => $database,
            'htaccess' => $htaccess,
        ]);
    }

    public function database()
    {
        return view('install/database');
    }

    public function postDatabase()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Check if all fields have been sent
            if (isset($_POST['db_host'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass'])) {
                $content = "<?php\n\nreturn [\n";
                $content .= "\t'DB_TYPE' => 'mysql',\n";
                $content .= "\t'DB_HOST' => '{$_POST['db_host']}',\n";
                $content .= "\t'DB_PORT' => '{$_POST['db_port']}',\n";
                $content .= "\t'DB_NAME' => '{$_POST['db_name']}',\n";
                $content .= "\t'DB_USER' => '{$_POST['db_user']}',\n";
                $content .= "\t'DB_PASS' => '{$_POST['db_pass']}',\n";
                $content .= "];\n";

                // Try creating the file
                if (file_put_contents(__DIR__ . "/../../config/database.php", $content) !== false) {
                    echo "Configuration file created successfully!";
                    redirect('/installer/import');
                } else {
                    echo "An error occurred while creating the configuration file.";
                }
            } else {
                echo "Please fill in all fields.";
            }
        } else {
            echo "Invalid request method.";
        }
    }

    public function import()
    {
        $connection = $this->conn->testConnection();
        return view('install/import', ['connection' => $connection]);
    }

    public function importDatabase()
    {
        try {
            $sql = file_get_contents($this->databaseFile);
            $this->conn->exec($sql);
        } catch (Exception $e) {
            echo 'Error importing SQL file: ' . $e->getMessage();
        }
        redirect('/installer/config');
    }

    public function config()
    {
        return view('install/config');
    }

    public function postConfig()
    {
        if (!input_exists()) {
            redirect('/installer/config');
        }

        $data = [
            'server_name' => input('servername'),
            'speed' => input('speed'),
            'roundlenght' => input('roundlenght'),
            'increase' => input('incspeed'),
            'heroattrspeed' => input('heroattrspeed'),
            'itemattrspeed' => input('itemattrspeed'),
            'world_max' => input('world_max'),
            'natars_max' => input('natars_max'),
            'reg_open' => input('reg_open'),
            'domain_url' => input('server_url'),
            'homepage_url' => input('server_url'),
            'server_url' => input('server_url'),
            'storagemultiplier' => input('storagemultiplier'),
            'minprotecttime' => input('minbeginner'),
            'maxprotecttime' => input('maxbeginner'),
            'plus_time' => input('plus_time'),
            'plus_prodtime' => input('plus_production'),
            'auctiontime' => input('auction_time'),
            'ts_threshold' => input('ts_threshold'),
            'medalinterval' => input('medalinterval'),
            'lastgavemedal' => input('medalinterval'),
            'great_wks' => input('great_wks'),
            'ww' => input('ww'),
            'peace' => input('peace'),
            'newsbox1' => input('box1'),
            'newsbox2' => input('box2'),
            'newsbox3' => input('box3'),
            'log_build' => input('log_build'),
            'log_tech' => input('log_tech'),
            'log_login' => input('log_login'),
            'log_gold' => input('log_gold'),
            'log_admin' => input('log_admin'),
            'log_users' => input('log_users'),
            'log_war' => input('log_war'),
            'log_market' => input('log_market'),
            'log_illegal' => input('log_illegal'),
            'winmoment' => 0,
            'stats_lasttime' => time(),
            'minimap_time' => time(),
            'last_checkall' => time(),
            'freegold_lasttime' => time(),
            'check_db' => input('check_db'),
            'checkall_time' => input('check_db'),
            'stats_time' => input('stats'),
            'taskmaster' => input('quest'),
            'auth_email' => input('activate'),
            'limit_mailbox' => input('limit_mailbox'),
            'max_mails' => input('max_mails'),
            'timeout' => input('timeout'),
            'autodel' => input('autodel'),
            'autodeltime' => input('autodeltime'),
            'demolish_lvl' => input('demolish'),
            'village_expand' => input('village_expand'),
            'commence' => time()
        ];
        $this->conn->insert('config', $data);
        redirect('/installer/world');
    }

    public function world()
    {
        return view('install/world');
    }

    public function createWorld()
    {
        set_time_limit(0);
        error_reporting(0);

        $world_max = setting('world_max');
        $natars_max = setting('natars_max');

        for ($y = $world_max; $y >= -$world_max; $y--) {
            for ($x = -$world_max; $x <= $world_max; $x++) {
                if (abs($x) <= 2 && abs($y) <= 2 && ($x != 0 || $y != 0)) {
                    $fieldtype = 3;
                } else {
                    $random = random_int(1, 1000);
                    if ($random <= 900) {
                        $fieldtype = ceil($random / 80);
                        $oasistype = 0;
                    } else {
                        $distance = sqrt(($x * $x) + ($y * $y));
                        if ($distance <= $natars_max) {
                            $oasistype = min(12, ceil(($random - 900) / 8));
                        } else {
                            $oasistype = min(12, ceil(($random - 900) / 12) + 3);
                        }
                        $fieldtype = 0;
                    }
                }

                $image = match ($oasistype) {
                    1, 2, 3 => 'forest' . random_int(0, 5),
                    4, 5, 6 => 'clay' . random_int(0, 7),
                    7, 8, 9 => 'hill' . random_int(0, 6),
                    10, 11, 12 => 'lake' . random_int(0, 7),
                    default => 'grassland' . random_int(0, 11),
                };

                $this->conn->insert('wdata', [
                    'fieldtype' => $fieldtype,
                    'oasistype' => $oasistype,
                    'x' => $x,
                    'y' => $y,
                    'image' => $image,
                    'occupied' => 0
                ]);
            }
        }

        redirect('/installer/multihunter');
    }

    public function multihunter()
    {
        return view('install/multihunter');
    }

    public function setMultihunter()
    {
        $password = input('mhpwd');

        if (empty($password)) {
            $password = md5_gen();
        }

        $this->insertUsers($password);

        $worldid4 = $this->database->getWref(1, 0);
        $this->setupVillages($worldid4, 4, 'Multihunter', 1);
        $worldid2 = $this->database->getWref(0, 0);
        $this->setupVillages($worldid2, 2, '1', 0);

        $speed = setting('speed');
        $speed = $speed > 5 ? 5 : $speed;

        $this->updateUnits($worldid2, $speed);

        for ($i = 1; $i <= 13; $i++) {
            $nareadis = setting('natars_max');
            do {
                $x = rand(3, intval(floor($nareadis)));
                if (rand(1, 10) > 5) $x = $x * -1;
                $y = rand(3, intval(floor($nareadis)));
                if (rand(1, 10) > 5) $y = $y * -1;
                $dis = sqrt(($x * $x) + ($y * $y));
                $villageid = $this->database->getWref($x, $y);
                $status = $this->database->getVillageState($villageid);
            } while (($dis > $nareadis) || $status != 0);

            $this->setupVillages($villageid, 2, 'Natars', 1);
            $this->updateNatars($villageid, $speed);
        }

        redirect('/installer/oasis');
    }

    protected function insertUsers($password)
    {
        $users = [
            ['username' => 'Support', 'password' => md5($password), 'email' => 'support@phpvian.com', 'tribe' => 1, 'access' => 8, 'timestamp' => time(), 'desc1' => '[#support]', 'desc2' => '[#support]', 'protect' => 0, 'quest' => 25],
            ['username' => 'Natars', 'password' => md5($password), 'email' => 'natars@phpvian.com', 'tribe' => 5, 'access' => 8, 'timestamp' => time(), 'desc1' => '[#natars]', 'desc2' => '[#natars]', 'protect' => 0, 'quest' => 25, 'fquest' => 35],
            ['username' => 'Nature', 'password' => md5($password), 'email' => 'nature@phpvian.com', 'tribe' => 4, 'access' => 2, 'timestamp' => time(), 'desc1' => '[#nature]', 'desc2' => '[#nature]', 'protect' => 0, 'quest' => 25],
            ['username' => 'Multihunter', 'password' => md5($password), 'email' => 'multihunter@phpvian.com', 'tribe' => 0, 'access' => 9, 'timestamp' => time(), 'desc1' => '[#multihunter]', 'desc2' => '[#multihunter]', 'protect' => 0, 'quest' => 25],
        ];

        foreach ($users as $user) {
            $this->conn->insert('users', $user);
        }
    }

    protected function setupVillages($worldid, $userid, $username, $capital)
    {
        $status = $this->database->getVillageState($worldid);
        if ($status == false) {
            $this->database->setFieldTaken($worldid);
            $this->database->addVillage($worldid, $userid, $username, $capital);
            $this->database->addResourceFields($worldid, $this->database->getVillageType($worldid));
            $this->database->addUnits($worldid);
            $this->database->addTech($worldid);
            $this->database->addABTech($worldid);
        }
    }

    protected function updateUnits($worldid, $speed)
    {
        $data = [
            'u41' => (94700 * $speed),
            'u42' => (295231 * $speed),
            'u43' => (180747 * $speed),
            'u44' => (1048 * $speed),
            'u45' => (364401 * $speed),
            'u46' => (217602 * $speed),
            'u47' => (2034 * $speed),
            'u48' => (1040 * $speed),
            'u49' => 1,
            'u50' => 9
        ];
        $this->conn->upgrade('vdata', ['pop' => 781], 'wref = :wref', [':wref' => $worldid]);
        $this->conn->upgrade('units', $data, 'vref = :vref', [':vref' => $worldid]);
    }

    protected function updateNatars($worldid, $speed)
    {
        $vdata = [
            'pop' => 238,
            'natar' => 1,
            'name' => 'WW Village',
            'capital' => 0
        ];
        $units = [
            'u41' => (random_int(1000, 2000) * $speed),
            'u42' => (random_int(1500, 2000) * $speed),
            'u43' => (random_int(2300, 2800) * $speed),
            'u44' => (random_int(235, 575) * $speed),
            'u45' => (random_int(1200, 1900) * $speed),
            'u46' => (random_int(1500, 2000) * $speed),
            'u47' => (random_int(500, 900) * $speed),
            'u48' => (random_int(100, 300) * $speed),
            'u49' => (random_int(1, 5) * $speed),
            'u50' => (random_int(1, 5) * $speed)
        ];
        $fdata = [
            'f22t' => 27, 'f22' => 10, 'f28t' => 25, 'f28' => 10, 'f19t' => 23, 'f19' => 10, 'f99t' => 40,
            'f26' => 0, 'f26t' => 0, 'f21' => 1, 'f21t' => 15, 'f39' => 1, 'f39t' => 16
        ];
        $this->conn->upgrade('vdata', $vdata, 'wref = :wref', [':wref' => $worldid]);
        $this->conn->upgrade('units', $units, 'vref = :vref', [':vref' => $worldid]);
        $this->conn->upgrade('fdata', $fdata, 'vref = :vref', [':vref' => $worldid]);
    }

    public function oasis()
    {
        return view('install/oasis');
    }

    public function setOasis()
    {
        $this->populateOasisData();
        $this->populateOasis();
        $this->populateOasisUnitsLow();

        redirect('/installer/complete');
    }

    protected function populateOasisData()
    {
        $speed = setting('speed');

        $worlds = $this->conn->select('id')->from('wdata')->where('oasistype != 0')->get();

        foreach ($worlds as $world) {
            $time = time();
            $base = $this->database->getOMInfo($world['id']);
            $data = [
                "wref" => $base['id'],
                "type" => $base['oasistype'],
                "conqured" => 0,
                'wood' => 750 * $speed / 10,
                'iron' => 750 * $speed / 10,
                'clay' => 750 * $speed / 10,
                'maxstore' => 800 * $speed / 10,
                'crop' => 750 * $speed / 10,
                'maxcrop' => 800 * $speed / 10,
                'lasttrain' => $time,
                'lastfarmed' => $time,
                'lastupdated' => $time,
                "loyalty" => 100,
                'owner' => 3,
                "name" => 'Unoccupied oasis'
            ];
            $this->conn->insert('odata', $data);
        }
    }

    protected function populateOasis()
    {
        $worlds = $this->conn->select('id')->from('wdata')->where('oasistype != 0')->get();
        foreach ($worlds as $world) {
            $this->database->addUnits($world['id']);
        }
    }

    protected function populateOasisUnitsLow()
    {
        $worlds = $this->conn->select('id')->from('wdata')->where('oasistype != 0')->get();

        foreach ($worlds as $world) {
            $base = $this->database->getMInfo($world['id']);
            $oasisValues = $this->generateOasisValues($base['oasistype']);
            $this->conn->upgrade('units', $oasisValues, 'vref = :vref', [':vref' => $world['id']]);
        }
    }

    protected function generateOasisValues($oasistype)
    {
        $speed = setting('speed');
        return match ($oasistype) {
            1, 2 => [
                'u35' => intval(random_int(5, 30) * ($speed / 10)),
                'u36' => intval(random_int(5, 30) * ($speed / 10)),
                'u37' => intval(random_int(0, 30) * ($speed / 10))
            ],
            3 => [
                'u35' => intval(random_int(5, 30) * ($speed / 10)),
                'u36' => intval(random_int(5, 30) * ($speed / 10)),
                'u37' => intval(random_int(1, 30) * ($speed / 10)),
                'u39' => intval(random_int(0, 10) * ($speed / 10)),
                'u40' => intval(random_int(0, 20) == 1 ? random_int(0, 31) * ($speed / 10) : 0)
            ],
            4, 5 => [
                'u31' => intval(random_int(5, 40) * ($speed / 10)),
                'u32' => intval(random_int(5, 30) * ($speed / 10)),
                'u35' => intval(random_int(0, 25) * ($speed / 10))
            ],
            6 => [
                'u31' => intval(random_int(5, 40) * ($speed / 10)),
                'u32' => intval(random_int(5, 30) * ($speed / 10)),
                'u35' => intval(random_int(1, 25) * ($speed / 10)),
                'u38' => intval(random_int(0, 15) * ($speed / 10)),
                'u40' => intval(random_int(0, 20) == 1 ? random_int(0, 31) * ($speed / 10) : 0)
            ],
            7, 8 => [
                'u31' => intval(random_int(5, 40) * ($speed / 10)),
                'u32' => intval(random_int(5, 30) * ($speed / 10)),
                'u34' => intval(random_int(0, 25) * ($speed / 10))
            ],
            9 => [
                'u31' => intval(random_int(5, 40) * ($speed / 10)),
                'u32' => intval(random_int(5, 30) * ($speed / 10)),
                'u34' => intval(random_int(1, 25) * ($speed / 10)),
                'u37' => intval(random_int(0, 15) * ($speed / 10)),
                'u40' => intval(random_int(0, 20) == 1 ? random_int(0, 31) * ($speed / 10) : 0)
            ],
            10, 11 => [
                'u31' => intval(random_int(5, 40) * ($speed / 10)),
                'u33' => intval(random_int(5, 30) * ($speed / 10)),
                'u37' => intval(random_int(1, 25) * ($speed / 10)),
                'u39' => intval(random_int(0, 25) * ($speed / 10))
            ],
            12 => [
                'u31' => intval(random_int(5, 40) * ($speed / 10)),
                'u33' => intval(random_int(5, 30) * ($speed / 10)),
                'u38' => intval(random_int(1, 25) * ($speed / 10)),
                'u39' => intval(random_int(0, 25) * ($speed / 10)),
                'u40' => intval(random_int(0, 20) == 1 ? random_int(0, 31) * ($speed / 10) : 0)
            ]
        };
    }

    public function complete()
    {
        return view('install/complete');
    }
}
