<?php

namespace App\Controllers;

use App\Utils\LoggerHelper;
use App\Config\App;
use InvalidArgumentException;  // Incluindo a classe InvalidArgumentException, que está sendo usada no código
use Exception;  // Incluindo a classe Exception, que está sendo usada no código

class Controller
{
    private $logger;
    private $db;
    private $correiosApi;
    private $whatsAppApi;

    public function __construct($logger, $db, $correiosApi, $whatsAppApi)
    {
        if (!$logger || !$db || !$correiosApi || !$whatsAppApi) {
            throw new InvalidArgumentException('Dependências inválidas no Controller.');
        }
        $this->logger = $logger;
        $this->db = $db;
        $this->correiosApi = $correiosApi;
        $this->whatsAppApi = $whatsAppApi;
    }

    public function processarRastreios()
    {
        $sql = "SELECT id, destinatario, codRastreio, numeroTelefone, nomeProduto, validate1, validate2 
                FROM rastreioapi 
                WHERE validate1 = 0 OR validate2 = 0 
                ORDER BY id DESC";
        $result = $this->db->executar($sql, null);

        // Preparar os dados em lotes de até 50 rastreios
        $loteRastreios = array_chunk(array_map([$this, 'formatarParametros'], $result), 50);

        foreach ($loteRastreios as $lote) {
            $this->processarLoteRastreios($lote);
        }
    }

    private function formatarParametros($parametros)
    {
        return [
            'id' => $parametros['id'],
            'destinatario' => $parametros['destinatario'],
            'codRastreio' => $this->limparCodigo($parametros['codRastreio']),
            'numeroTelefone' => $this->limparCodigo($parametros['numeroTelefone']),
            'nomeProduto' => $parametros['nomeProduto'] ?? null,
            'statusPostado' => $parametros['validate1'],
            'statusEntrega' => $parametros['validate2']
        ];
    }

    private function processarLoteRastreios(array $lote)
    {
        $codigos = array_column($lote, 'codRastreio');
        try {
            $responseCorreios = $this->correiosApi->rastrearObjetosLote($codigos);
        } catch (Exception $e) {
            $this->logErro("Falha ao conectar com a API dos Correios: {$e->getMessage()}", 'erro', 'processarLoteRastreios');
            return;
        }

        if ($responseCorreios['httpcode'] == 200) {
            $objetos = $responseCorreios['response']['objetos'];
            foreach ($objetos as $objeto) {
                $this->processarObjeto($objeto, $lote);
            }
        } else {
            $this->logErro("Erro na API dos Correios: {$responseCorreios['response']}", 'erro', 'processarLoteRastreios');
        }
    }

    private function processarObjeto($objeto, array $lote)
    {
        $codigoRastreio = $objeto['codObjeto'];

        $parametros = current(array_filter($lote, fn($item) => $item['codRastreio'] === $codigoRastreio));
        if (isset($objeto['eventos']) && is_array($objeto['eventos']) && count($objeto['eventos']) > 0) {
            $descricao = $objeto['eventos'][0]['descricao'];
            $dataHoraUTC = $objeto['eventos'][0]['dtHrCriado'];
            $mensagem = $this->construirMensagem($parametros, $descricao);

            $this->enviarMensagemSeNecessario($parametros, $descricao, $mensagem, $dataHoraUTC);
        }
    }

    private function enviarMensagemSeNecessario($parametros, $descricao, $mensagem, $dataHoraUTC)
    {
        if ($parametros['statusPostado'] == 0 && stripos($descricao, 'postado') !== false) {
            if ($this->whatsAppApi->sendMessage($parametros['numeroTelefone'], $mensagem)) {
                $this->atualizarStatusRastreamento($parametros['codRastreio'], 1, $dataHoraUTC);
            }
        }

        if ($parametros['statusEntrega'] == 0 && stripos($descricao, 'saiu para entrega') !== false) {
            if ($this->whatsAppApi->sendMessage($parametros['numeroTelefone'], $mensagem)) {
                $this->atualizarStatusRastreamento($parametros['codRastreio'], 2, $dataHoraUTC);
            }
        }
    }

    private function construirMensagem($parametros, $descricao)
    {
        $mensagem = "Olá {$parametros['destinatario']}, essa é uma mensagem automática!\n\n";
        $mensagem .= "Seu produto está a caminho! ☢\n";
        $mensagem .= "Aqui estão os detalhes do seu envio:\n";
        $mensagem .= "- Nome do Produto: {$parametros['nomeProduto']}\n";
        $mensagem .= "- Código de Rastreamento: {$parametros['codRastreio']}\n";
        $mensagem .= "- Status Atual: $descricao\n\n";
        $mensagem .= "Você pode acompanhar o progresso do seu pedido através do site dos Correios ou clicando no link abaixo:\n";
        $mensagem .= "https://rastreamento.correios.com.br/app/index.php\n\n";

        if ($parametros['statusPostado'] == 0 && stripos($descricao, 'postado') !== false) {
            $mensagem .= "Em breve voltamos com atualização da entrega.\n";
            $mensagem .= "Segue uma pesquisa para avaliar nosso atendimento https://forms.office.com/r/mEieYinYNM.\n";
        }
        $mensagem .= "Caso tenha alguma dúvida sobre o processo de entrega, entre em contato com o número (34) 98852-6101!\n";
        $mensagem .= "☢ Usina, a Marca em que você confia! ☢";

        return $mensagem;
    }

    private function limparCodigo($codigo)
    {
        return preg_replace('/[^\w]/', '', $codigo);
    }

    private function atualizarStatusRastreamento($codigoRastreio, $validate, $dataHoraUTC)
    {
        $coluna = $validate == 1 ? 'validate1' : 'validate2';
        $dataHoraColuna = $validate == 1 ? 'statusPostadoHora' : 'statusEntregaHora';

        $this->db->executar("UPDATE rastreioapi SET $coluna = 1, $dataHoraColuna = :dataHora WHERE codRastreio LIKE :codigoRastreio", [
            ':dataHora' => $dataHoraUTC,
            ':codigoRastreio' => "%$codigoRastreio%"
        ]);

        $this->logErro("A mensagem do código de rastreio: $codigoRastreio foi enviada com sucesso.", 'sucesso', 'atualizarStatusRastreamento');
    }

    private function logErro($mensagem, $tipo, $contexto)
    {
        $this->logger->registrarLogErro($mensagem, $tipo, $contexto);
    }
}
