<?php

namespace App\Services\Correios;

use App\Utils\HttpClient;
use App\Utils\LoggerHelper;
//use App\Config\App;

class CorreiosApi
{
    private $logger;
    private $db;
    private $user;
    private $apiKey;
    private $numero;

    public function __construct($logger, $db)
    {
        $this->logger = $logger;
        $this->db = $db;
        // Carrega configurações do ambiente
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $requiredEnvVars = ['CORREIOS_USER', 'CORREIOS_API_KEY', 'CORREIOS_NUMERO'];

        foreach ($requiredEnvVars as $var) {
            if (!isset($_ENV[$var])) {
                throw new \Exception("Erro: Variável de ambiente $var não carregada corretamente.");
            }
        }

        $this->user = $_ENV['CORREIOS_USER'];
        $this->apiKey = $_ENV['CORREIOS_API_KEY'];
        $this->numero = $_ENV['CORREIOS_NUMERO'];
    }

    public function apiPMA()
    {
        $token = $this->getTokenFromDatabase();

        $url = 'https://api.correios.com.br/prepostagem/v2/prepostagens?status=PREPOSTADO&page=0&size=50';

        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $response = HttpClient::makeRequest('GET', $url, $headers);

        if ($response['httpcode'] !== 200) {
            LoggerHelper::logError($this->logger, 'Erro na API PMA', $response, __CLASS__);
            return ['status' => 'erro', 'mensagem' => 'Erro ao acessar API dos Correios.'];
        }

        return ['httpcode' => $response['httpcode'], 'status' => 'sucesso', 'response' => $response['data']];
    }

    public function getToken(): array
    {
        $url = 'https://api.correios.com.br/token/v1/autentica/cartaopostagem';
        $credentials = base64_encode("{$this->user}:{$this->apiKey}");

        $headers = [
            'accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $credentials,
        ];

        $body = json_encode(['numero' => $this->numero]);

        $response = HttpClient::makeRequest('POST', $url, $headers, $body);

        if ($response['httpcode'] !== 201) {
            LoggerHelper::logError($this->logger, 'Erro ao obter token', $response, __CLASS__);
            return ['status' => 'erro', 'mensagem' => 'Falha ao obter token dos Correios.'];
        }

        return $response['data'];
    }

    public function rastrearObjetosLote(array $codigos): array
    {
        $codigosQueryString = implode('&codigosObjetos=', $codigos);
        $url = "https://api.correios.com.br/srorastro/v1/objetos?codigosObjetos=$codigosQueryString&resultado=U";

        $token = $this->getTokenFromDatabase();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        $response = HttpClient::makeRequest('GET', $url, $headers);

        if ($response['httpcode'] !== 200) {
            LoggerHelper::logError($this->logger, 'Erro no rastreamento de objetos', $response, __CLASS__);
            return ['status' => 'erro', 'mensagem' => 'Erro ao rastrear objetos dos Correios.'];
        }

        return ['httpcode' => $response['httpcode'], 'status' => 'sucesso', 'response' => $response['data']];
    }

    public function getTokenFromDatabase(): string
    {
        $sql = "SELECT token FROM correiosToken WHERE data_expiracao > NOW() ORDER BY id DESC LIMIT 1";
        $result = $this->db->executar($sql, null);

        if (!empty($result)) {
            return $result[0]['token'];
        }

        $newToken = $this->getToken();



        $this->storeTokenInDatabase($newToken['token'], $newToken['expiraEm']);

        return $newToken['token'];
    }

    private function storeTokenInDatabase(string $token, string $expiry): void
    {
        $sql = "INSERT INTO correiosToken (token, data_expiracao) VALUES (:token, :dataExpiracao)";
        $params = [':token' => $token, ':dataExpiracao' => $expiry];
        $this->db->executar($sql, $params, true);
    }
}
