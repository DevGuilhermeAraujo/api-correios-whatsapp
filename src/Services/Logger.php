<?php
namespace App\Services;

class Logger {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Registra um erro no banco de dados.
     *
     * @param string $mensagem A mensagem de erro a ser registrada
     * @param string $tipo O tipo de erro (padrão: 'erro')
     * @param string $classe A classe onde ocorreu o erro (opcional)
     */
    public function registrarLogErro($mensagem, $tipo = 'erro', $classe = '') {
        // Verifica se houve erro na conexão com o banco de dados antes de prosseguir
        if ($this->db->getErrorCode() != '00000') {
            die("Falha na conexão com o banco de dados.");
        }

        $sql = "INSERT INTO logs (tipo, mensagem, classe, data_hora) VALUES (:tipo, :mensagem, :classe, NOW())";
        $parametros = [
            ':tipo' => $tipo,
            ':mensagem' => $mensagem,
            ':classe' => $classe
        ];
        
        try {
            $this->db->executar($sql, $parametros);
        } catch (\PDOException $e) {
            // Em caso de falha ao registrar no banco, loga o erro para um arquivo de log padrão
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }

    /**
     * Recupera todos os logs de erro registrados no banco de dados.
     *
     * @return array|string Retorna um array com os logs de erro ou uma mensagem se não houver logs.
     */
    public function recuperarLogErros() {
        // Verifica se houve erro na conexão com o banco de dados antes de prosseguir
        if ($this->db->errorCode() != '00000') {
            die("Falha na conexão com o banco de dados.");
        }

        $sql = "SELECT tipo, mensagem, classe, data_hora FROM logs ORDER BY data_hora DESC";
        $logs = $this->db->executar($sql);

        if (empty($logs)) {
            return 'Nenhum log de erro registrado.';
        }
        
        return $logs;
    }
}