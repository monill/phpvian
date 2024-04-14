<?php

namespace PHPvian\Controllers;

use PHPvian\Libs\Database;

class InstallController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function index()
    {
        return view('install/index');
    }

    public function process()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Define os dados do banco de dados com base nos valores do $_POST
            $data = [
                'DB_TYPE' => $_POST['mysql'] ?? 'mysql',
                'DB_HOST' => $_POST['sserver'] ?? '',
                'DB_PORT' => $_POST['sport'] ?? '',
                'DB_NAME' => $_POST['sdb'] ?? '',
                'DB_USER' => $_POST['suser'] ?? '',
                'DB_PASS' => $_POST['spass'] ?? '',
            ];

            // Gera a string de configuração formatada
            $configString = "<?php\n\nreturn " . var_export($data, true) . ";\n";

            // Define o caminho absoluto para o arquivo de configuração
            $configFilePath = __DIR__ . "/../../config/database.php";

            // Salva a string de configuração no arquivo
            file_put_contents($configFilePath, $configString);

            $destFilePath = __DIR__ . "/../../storage/database.sql";
            $this->db->importSQL($destFilePath);

            // Redireciona para uma página de confirmação
            header("Location: confirmation.php");
            exit;
        } else {
            echo "Invalid request method.";
        }

    }
}
