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
            // Verifique se todos os campos foram enviados
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

                // Tente criar o arquivo
                if (file_put_contents(__DIR__ . "/../../config/database.php", $content) !== false) {
                    echo "Arquivo de configuração criado com sucesso!";
                } else {
                    echo "Ocorreu um erro ao criar o arquivo de configuração.";
                }

            } else {
                echo "Por favor, preencha todos os campos.";
            }
        } else {
            echo "Invalid request method.";
        }
    }

    public function importDatabase()
    {
        $this->db->importSQL($this->databaseFile);

        // Redireciona para uma página de confirmação
        header("Location: confirmation.php");
        exit;
    }

}
