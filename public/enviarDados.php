<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Ajuste o caminho conforme necessário
use Config\App;

App::init();

class CorreiosProcessor
{
    public static function enviarDados()
    {
        $result = App::getCorreiosApi()->apiPMA();

        $data = $result['response'] ?? [];
        $httpCode = $result['httpcode'] ?? 500;

        if ($httpCode !== 200 || empty($data)) {
            self::registrarLogErro("Erro na API dos Correios. Código: $httpCode, Resposta: " . json_encode($data));
            return;
        }

        foreach ($data['itens'] ?? [] as $objeto) {
            if (!self::isServicoValido($objeto['codigoServico'] ?? '')) {
                continue;
            }
            self::processarObjeto($objeto);
        }
    }

    private static function processarObjeto(array $objeto)
    {
        $codigoRastreio = $objeto['codigoObjeto'] ?? '';
        $destinatario = $objeto['destinatario']['nome'] ?? '';

        if (empty($destinatario) || !self::validarTelefone($objeto, $codigoRastreio) || self::rastreamentoExistente($codigoRastreio)) {
            return;
        }

        $observacao = $objeto['observacao'] ?? '';
        self::inserirRastreio($destinatario, $codigoRastreio, $objeto, $observacao);
    }

    private static function isServicoValido(string $codigoServico): bool
    {
        return in_array($codigoServico, ['03298', '03220']);
    }

    private static function validarTelefone(array &$objeto, string $codigoRastreio): bool
    {
        $ddd = $objeto['destinatario']['dddCelular'] ?? null;
        $celular = $objeto['destinatario']['celular'] ?? null;

        if (empty($ddd) || empty($celular)) {
            self::registrarLogErro("Erro: Campos DDD ou Celular ausentes para o código: $codigoRastreio");
            return false;
        }

        $telefoneCompleto = $ddd . $celular;
        $telefoneModificado = App::getWhatsAppApi()->getNumberId($telefoneCompleto);

        if (!$telefoneModificado || empty($telefoneModificado['response'])) {
            self::registrarLogErro("Erro ao validar telefone para o código: $codigoRastreio");
            return false;
        }

        $objeto['destinatario']['telefoneModificado'] = $telefoneModificado;
        return true;
    }

    public static function rastreamentoExistente(string $codigoRastreio): bool
    {
        $result = App::getDb()->executar(
            "SELECT COUNT(*) as total FROM rastreioapi WHERE codRastreio = :codigoRastreio",
            [':codigoRastreio' => $codigoRastreio]
        );
        return isset($result[0]['total']) && $result[0]['total'] > 0;
    }

    private static function inserirRastreio(string $destinatario, string $codigoRastreio, array $objeto, string $observacao)
    {
        $telefone = $objeto['destinatario']['telefoneModificado']['response'] ?? '';
        $sql = "INSERT INTO rastreioapi (destinatario, codRastreio, numeroTelefone, nomeProduto, validate1, validate2, lastUpdate) 
                VALUES (:destinatario, :codRastreio, :numeroTelefone, :nomeProduto, 0, 0, NOW())";

        $params = [
            ':destinatario' => $destinatario,
            ':codRastreio' => $codigoRastreio,
            ':numeroTelefone' => $telefone,
            ':nomeProduto' => $observacao
        ];

        if (!App::getDb()->executar($sql, $params)) {
            self::registrarLogErro("Erro ao inserir rastreio no banco: $codigoRastreio");
        }
    }

    private static function registrarLogErro(string $mensagem)
    {
        App::getLogger()->registrarLogErro("[CorreiosProcessor] " . $mensagem, 'erro', 'CorreiosProcessor');
    }
}

CorreiosProcessor::enviarDados();
