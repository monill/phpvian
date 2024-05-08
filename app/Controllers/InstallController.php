<?php

namespace PHPvian\Controllers;

use Exception;
use PHPvian\Libs\Connection;
use PHPvian\Libs\Database;

class InstallController
{
    private $conn, $db, $databaseFile, $htaccessFile;

    public function __construct()
    {
        $this->conn = new Connection();
        $this->db = new Database();
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
                // Construa o conteúdo do arquivo
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
            echo "Error importing SQL file: " . $e->getMessage();
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
            'commence' => input('commence') + time()
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

        if (!$password) {
            return;
        }

        $this->insertUsers($password);

        $worldid2 = $this->db->getWref(0, 0);
        $this->setupVillages($worldid2, 2, 'WW Village', 0);
        $worldid4 = $this->db->getWref(1, 0);
        $this->setupVillages($worldid4, 4, 'Multihunter', 1);

        $this->updateUnits($worldid2);

        for ($i = 1; $i <= 13; $i++) {
            $natars_max = setting('natars_max');

            do {
                $x = rand(3, intval(floor($natars_max)));
                $y = rand(3, intval(floor($natars_max)));
                if (rand(1, 10) > 5) {
                    $x = $x * -1;
                }
                if (rand(1, 10) > 5) {
                    $y = $y * -1;
                }
                $distance = sqrt(($x * $x) + ($y * $y));
                $villageId = $this->db->getWref($x, $y);
                $status = $this->db->getVillageState($villageId);
            } while (($distance > $natars_max) || $status != 0);

            if ($status == false) {
                $this->updateNatars($worldid2);
            }
        }

        redirect('/installer/oasis');
    }

    protected function insertUsers($password)
    {
        $users = [
            ['username' => 'Support', 'password' => md5($password), 'email' => 'support@phpvian.com', 'tribe' => 1, 'access' => 8, 'timestamp' => time(), 'desc1' => '[#support]', 'desc2' => '[#support]', 'protect' => 0, 'quest' => 25, 'fquest' => 35],
            ['username' => 'Natars', 'password' => md5($password), 'email' => 'natars@phpvian.com', 'tribe' => 5, 'access' => 8, 'timestamp' => time(), 'desc1' => '[#natars]', 'desc2' => '[#natars]', 'protect' => 0, 'quest' => 25, 'fquest' => 35],
            ['username' => 'Nature', 'password' => md5($password), 'email' => 'nature@phpvian.com', 'tribe' => 4, 'access' => 2, 'timestamp' => time(), 'desc1' => '[#nature]', 'desc2' => '[#nature]', 'protect' => 0, 'quest' => 25, 'fquest' => 35],
            ['username' => 'Multihunter', 'password' => md5($password), 'email' => 'multihunter@phpvian.com', 'tribe' => 4, 'access' => 9, 'timestamp' => time(), 'desc1' => '[#multihunter]', 'desc2' => '[#multihunter]', 'protect' => 0, 'quest' => 25, 'fquest' => 35],
        ];

        foreach ($users as $user) {
            $this->conn->insert('users', $user);
        }
    }

    protected function setupVillages($worldid, $userid, $username, $capital)
    {
        $status = $this->db->getVillageState($worldid);
        if ($status == false) {
            $this->db->setFieldTaken($worldid);
            $this->db->addVillage($worldid, $userid, $username, $capital);
            $this->db->addResourceFields($worldid, $this->db->getVillageType($worldid));
            $this->db->addUnits($worldid);
            $this->db->addTech($worldid);
            $this->db->addABTech($worldid);
        }
    }

    protected function updateUnits($worldid2)
    {
        $speed = setting('speed');
        $data = [
            'u41' => 274700 * $speed,
            'u42' => 995231 * $speed,
            'u43' => 10000,
            'u44' => 3048 * $speed,
            'u45' => 964401 * $speed,
            'u46' => 617602 * $speed,
            'u47' => 6034 * $speed,
            'u48' => 3040 * $speed,
            'u49' => 1,
            'u50' => 9
        ];
        $this->conn->from('units')->input($data)->where('vref = :wid', [':wid' => $worldid2])->update();
    }

    protected function updateNatars($worldid)
    {
        $speed = setting('speed');
        $this->conn->from('vdata')->input(['pop' => 238, 'natar' => 1])->where('wref = :wid', [':wid' => $worldid])->update();
        $this->conn->from('units')->input(['u41' => random_int(3000, 6000) * $speed, 'u42' => random_int(4500, 6000) * $speed, 'u43' => 10000, 'u44' => random_int(635, 1575) * $speed, 'u45' => random_int(3600, 5700) * $speed,'u46' => random_int(4500, 6000) * $speed, 'u47' => random_int(1500, 2700) * $speed, 'u48' => random_int(300, 900) * $speed, 'u49' => 0, 'u50' => 9])->where('vref = :wid', [':wid' => $worldid])->update();
        $this->conn->from('fdata')->input(['f22t' => 27, 'f22' => 10, 'f28t' => 25, 'f28' => 10, 'f19t' => 23, 'f19' => 10, 'f99t' => 40, 'f26' => 0, 'f26t' => 0, 'f21' => 1, 'f21t' => 15, 'f39' => 1, 'f39t' => 16])->where(':vref = :wid', ['wid' => $worldid])->update();
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
            $base = $this->db->getOMInfo($world['id']);
            $data = [
                "wref" => $base['id'],
                "type" => $base['oasistype'],
                "conqured" => 0,
                'wood' => 750 * $speed / 10,
                'iron' => 750 * $speed / 10,
                'clay' => 750 * $speed / 10,
                'woodp' => 0,
                'ironp' => 0,
                'clayp' => 0,
                'maxstore' => 800 * $speed / 10,
                'crop' => 750 * $speed / 10,
                'cropp' => 0,
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
            $this->db->addUnits($world['id']);
        }
    }

    protected function populateOasisUnitsLow()
    {
        $worlds = $this->conn->select('id')->from('wdata')->where('oasistype != 0')->get();

        foreach ($worlds as $world) {
            $worldid = $world['id'];
            $base = $this->db->getMInfo($worldid);
            $oasisValues = $this->generateOasisValues($base['oasistype']);
            $this->conn->from('units')->input($oasisValues)->where('`vref` = :vref', [':vref' => $worldid])->update();
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
