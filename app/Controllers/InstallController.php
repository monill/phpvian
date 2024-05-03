<?php

namespace PHPvian\Controllers;

use PHPvian\Libs\Database;

class InstallController
{
    private $db, $databaseFile, $htaccessFile;

    public function __construct()
    {
        $this->db = new Database();
        $this->databaseFile = dirname(__DIR__) . '/../storage/database.sql';
        $this->htaccessFile = dirname(__DIR__) . '/../public/.htaccess';
    }

    /*
     * Step 1
     */
    public function index()
    {
        return view('install/index');
    }

    /*
     * Step 2
     */
    public function requirements()
    {
        $extensions = ['BCMath', 'Ctype', 'Fileinfo', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'pdo_mysql', 'Tokenizer', 'XML', 'cURL', 'GD'];
        $phpversion = version_compare(PHP_VERSION, '8.1', '>=');
        return view('install/requirements', [
            'extensions' => $extensions,
            'phpversion' => $phpversion
        ]);
    }

    /*
     * Step 3
     */
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

    /*
     * Step 4
     */
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
        $connection = $this->db->testConnection();
        return view('install/import', ['connection' => $connection]);
    }

    public function importDatabase()
    {
        try {
            // Read the contents of the SQL file
            $sql = file_get_contents($this->databaseFile);

            // Execute the contents of the SQL file as a single query
            $this->db->exec($sql);
        } catch (\Exception $e) {
            echo "Erro ao importar o arquivo SQL: " . $e->getMessage();
        }

        // Redireciona para uma página de confirmação
        redirect('installer/config');
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

        $this->db->insert('config', [
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
        ]);

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
                    $ftype = 3;
                } else {
                    $random = random_int(1, 1000);
                    if ($random <= 900) {
                        $ftype = ceil($random / 80);
                        $otype = 0;
                    } else {
                        $distance = sqrt(($x * $x) + ($y * $y));
                        if ($distance <= $natars_max) {
                            $otype = ceil(($random - 900) / 8);
                        } else {
                            $otype = ceil(($random - 900) / 12) + 3;
                        }
                        $ftype = 0;
                    }
                }

                $image = match ($otype) {
                    1, 2, 3 => 'forest' . random_int(0, 5),
                    4, 5, 6 => 'clay' . random_int(0, 7),
                    7, 8, 9 => 'hill' . random_int(0, 6),
                    10, 11, 12 => 'lake' . random_int(0, 7),
                    default => 'grassland' . random_int(0, 11),
                };

                $this->db->insert('wdata', [
                    'fieldtype' => $ftype,
                    'oasistype' => $otype,
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
        dd($_POST);
    }

}
