<?php

namespace PHPvian\Libs;

class Lang
{
    public function __construct()
    {

    }

    public function get($file, $translate, $lang = 'en')
    {
        $dir = dirname(__DIR__) . "/../resources/lang/$lang/$file.json";

        if (file_exists($dir)) {
            $jsonContent = file_get_contents($dir);

            // Decodifica o conteúdo JSON em um array associativo
            $data = json_decode($jsonContent, true);

            // Verifica se a decodificação foi bem-sucedida
            if ($data !== null && isset($data[$translate])) {
                return $data[$translate];
            } else {
                // Se a decodificação falhar, retorna uma mensagem de erro
                return "Translation for key '$translate' not found in file '$file.json' for language '$lang'.";
            }
        } else {
            // Se o arquivo não existir, retorna uma mensagem de erro
            return "Translation file '$file.json' not found for language '$lang'.";
        }
    }

}
