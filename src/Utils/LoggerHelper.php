<?php
namespace App\Utils;

use App\Services\Logger;

class LoggerHelper
{
    public static function logError(Logger $logger, string $message, array $response, string $class): void
    {
        $logger->registrarLogErro(
            "$message. Status: {$response['httpcode']}, Erro: {$response['error']}, Resposta: " . json_encode($response['data']),
            'erro',
            $class
        );
    }
}
