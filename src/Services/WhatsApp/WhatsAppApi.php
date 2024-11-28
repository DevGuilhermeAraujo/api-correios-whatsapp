<?php
namespace App\Services\WhatsApp;

use App\Utils\HttpClient;
use App\Utils\LoggerHelper;
//use App\Config\App;

class WhatsAppApi
{
    private $logger;
    private $apiKey;
    private $sessao;
    private $apiUrl;

    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        // Variáveis de ambiente obrigatórias para autenticação na API do WhatsApp
        $requiredEnvVars = ['WHATSAPP_API_KEY', 'WHATSAPP_SESSAO', 'WHATSAPP_URL'];

        foreach ($requiredEnvVars as $var) {
            if (!isset($_ENV[$var])) {
                throw new \Exception("Erro: Variável de ambiente $var não carregada corretamente.");
            }
        }

        // Carrega as variáveis de ambiente para as variáveis da classe
        $this->apiKey = $_ENV['WHATSAPP_API_KEY'];
        $this->sessao = $_ENV['WHATSAPP_SESSAO'];
        $this->apiUrl = $_ENV['WHATSAPP_URL'];
    }

    public function sendMessage(string $number, string $message): array
    {
        if ($this->checkApiStatus()) {
            $url = $this->apiUrl . '/client/sendMessage/' . $this->sessao;

            $headers = [
                'accept' => '*/*',
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ];

            $payload = json_encode([
                'chatId' => $number . '@c.us',
                'contentType' => 'string',
                'content' => $message
            ]);

            // Faz a requisição através do método centralizado
            $response = HttpClient::makeRequest('POST', $url, $headers, $payload);

            if ($response['httpcode'] !== 200) {
                //$this->logError('Erro ao enviar mensagem', $response);
                return ['status' => 'erro', 'mensagem' => 'Falha ao enviar a mensagem.'];
            }

            return ['status' => 'sucesso', 'response' => $response['data']];
        }
    }

    public function checkApiStatus(): array
    {
        $url = $this->apiUrl . '/session/status/' . $this->sessao;

        $headers = [
            'accept' => 'application/json',
            'x-api-key' => $this->apiKey
        ];

        // Faz a requisição através do método centralizado
        $response = HttpClient::makeRequest('GET', $url, $headers);

        if ($response['httpcode'] !== 200) {
            //$this->logError('Erro ao verificar status da API', $response);
            return ['status' => 'erro', 'mensagem' => 'Falha ao verificar status da API do WhatsApp.'];
        }

        return ['status' => 'sucesso', 'response' => $response['data']];
    }

    public function getNumberId($numero)
    {
        if ($this->checkApiStatus()) {
            $url = $this->apiUrl . '/client/getNumberId/' . $this->sessao;
            $headers = [
                'accept'  => '*/*',
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ];

            $numero = $this->addBrazilianDDD($numero);

            $payload = json_encode([
                'number' => $numero
            ]);

            // Faz a requisição através do método centralizado
            $response = HttpClient::makeRequest('POST', $url, $headers, $payload);

            if ($response['httpcode'] !== 200) {
                //$this->logError('Erro ao obter número', $response);
                return ['status' => 'erro', 'mensagem' => 'Falha ao enviar a mensagem.'];
            }

            return [
                'status' => 'sucesso', 
                'response' => $response['data']['result']['user'] ?? $numero
            ];            
        } else {
            //$this->logError('Não foi possível comunicar com a API de WhatsApp', $response);
            return false;
        }
    }

    private function addBrazilianDDD($numero)
    {
        // Remove espaços, traços, parênteses e outros caracteres extras
        $numero = preg_replace('/\s+|\-|\(|\)/', '', $numero);

        // Verifica se o número possui pelo menos 12 dígitos após o '55'
        if (substr($numero, 0, 2) != '55' && strlen($numero) == 11) {
            $numero = '55' . $numero;
        }

        // Confirma o número final para debug
        $this->logger->registrarLogErro("Número formatado para envio: $numero", 'info', 'whatsAppApi');

        return $numero;
    }
}
