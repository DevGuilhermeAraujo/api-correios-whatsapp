<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Carrega as variáveis de ambiente do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// Agora você pode usar essas variáveis diretamente em todo o projeto sem precisar recarregar o `.env`.
