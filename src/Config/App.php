<?php

namespace Config;

use App\Database\Conexao;
use App\Services\Logger;
use App\Services\Correios\CorreiosApi;
use App\Services\WhatsApp\WhatsAppApi;
use App\Controllers\Controller;

class App
{
    private static Conexao $db;
    private static Logger $logger;
    private static CorreiosApi $correiosApi;
    private static WhatsAppApi $whatsAppApi;
    private static Controller $controller;

    public static function init(): void
    {
        // Inicializa as dependências principais
        self::$db = new Conexao();
        self::$logger = new Logger(self::$db);
        self::$correiosApi = new CorreiosApi(self::$logger, self::$db);
        self::$whatsAppApi = new WhatsAppApi(self::$logger);
        self::$controller = new Controller(self::$logger, self::$db, self::$correiosApi, self::$whatsAppApi);
    }

    // Métodos para acessar as instâncias
    public static function getDb(): Conexao
    {
        return self::$db;
    }

    public static function getLogger(): Logger
    {
        return self::$logger;
    }

    public static function getCorreiosApi(): CorreiosApi
    {
        return self::$correiosApi;
    }

    public static function getWhatsAppApi(): WhatsAppApi
    {
        return self::$whatsAppApi;
    }

    public static function getController(): Controller
    {
        return self::$controller;
    }
}
