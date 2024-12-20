<?php

// Certifique-se de carregar o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php'; // Ajuste o caminho conforme necessário
require_once __DIR__ . '/../src/Config/config.php';

// Inicializa a aplicação
use Config\App;
App::init();

// Executa a tarefa específica (processar rastreamentos)
App::getController()->processarRastreios();

echo "Rastreamentos processados com sucesso!";
