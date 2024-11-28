<?php
// Inclui o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Use a classe App corretamente
use Config\App;

try {
    // Inicializa as dependências da aplicação
    App::init(); // Inicializa a classe App e todas as suas dependências

    // Instancia a classe Conexao através do método getDb da classe App
    $conexao = App::getDb();
    $correios = App::getCorreiosApi();

    // Se a conexão foi bem-sucedida, exibe a mensagem
    echo "Conexão com o banco de dados foi bem-sucedida!";
} catch (Exception $e) {
    // Caso haja erro, exibe a mensagem de erro
    echo "Erro na conexão: " . $e->getMessage();
}
