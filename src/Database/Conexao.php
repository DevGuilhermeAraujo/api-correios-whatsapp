<?php
namespace App\Database;  // Defina o namespace adequado para o seu projeto

require_once __DIR__ . '/../Config/config.php';

use PDO;
use Exception;
//use App\Config\App;  // Certifique-se de que a classe App está corretamente configurada para buscar instâncias

class Conexao
{
    private $host;
    private $port;
    private $user;
    private $pass;
    private $dbName;
    private $pdo = null;
    public $errorCode = 0;

    public function __construct()
    {
        // Carrega as configurações a partir do arquivo .env
        $this->loadConfig();

        try {
            // Criação da conexão PDO utilizando as variáveis carregadas do .env
            $this->pdo = new PDO(
                "mysql:host=$this->host;port=$this->port;dbname=$this->dbName",
                $this->user,
                $this->pass
            );
            // Configura o modo de erro para exceções
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {  // Alterado para \PDOException
            $this->errorCode = $e->getCode();
            error_log("Erro na conexão: " . $e->getMessage());
            throw new Exception("Erro na conexão com o banco de dados.");
        }
    }

    private function loadConfig(): void
    {
        $requiredEnvVars = [
            'DB_HOST', 
            'DB_PORT', 
            'DB_DATABASE', 
            'DB_USERNAME', 
            'DB_PASSWORD'
        ];

        foreach ($requiredEnvVars as $var) {
            if (!isset($_ENV[$var])) {
                throw new \Exception("Erro: Variável de ambiente $var não carregada corretamente.");
            }
        }

        // Atribui as variáveis de ambiente às propriedades da classe
        $this->host = $_ENV['DB_HOST'];
        $this->port = $_ENV['DB_PORT'];
        $this->dbName = $_ENV['DB_DATABASE'];
        $this->user = $_ENV['DB_USERNAME'];
        $this->pass = $_ENV['DB_PASSWORD'];
    }

    /**
     * Executa uma query SQL no banco de dados.
     *
     * @param string $sql A query SQL a ser executada
     * @param array $parametros Os parâmetros da query (opcional)
     * @param bool $fullObject Define se deve retornar o objeto de statement completo
     * @return mixed Retorna o resultado da query ou o objeto de statement, dependendo de $fullObject
     */
    public function executar($sql, $parametros = [], $fullObject = false)
    {
        $stmt = $this->pdo->prepare($sql);

        // Verifica se $parametros é null ou não é um array
        if (!is_array($parametros)) {
            $parametros = []; // Define $parametros como um array vazio se não for um array válido
        }

        foreach ($parametros as $chave => $valor) {
            // Vincula os parâmetros
            $stmt->bindValue($chave, $valor);
        }

        // Executando a query
        $stmt->execute();

        if ($fullObject) {
            return $stmt;
        } else {
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retornando um array associativo
        }
    }

    /**
     * Obtém o último ID inserido pela última instrução SQL que modificou a tabela
     *
     * @return int O último ID inserido
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Obtém o código de erro da última operação PDO
     *
     * @return mixed O código de erro da última operação PDO
     */
    public function getErrorCode()
    {
        return $this->pdo->errorCode();
    }

    /**
     * Obtém informações sobre o erro da última operação PDO
     *
     * @return array Informações sobre o erro da última operação PDO
     */
    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }
}
