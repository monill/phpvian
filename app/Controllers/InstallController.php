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
        $extensions = ['BCMath', 'Ctype', 'Fileinfo', 'JSON', 'Mbstring', 'OpenSSL', 'PDO','pdo_mysql', 'Tokenizer', 'XML', 'cURL',  'GD'];
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

        dd($_POST);
    }

}
