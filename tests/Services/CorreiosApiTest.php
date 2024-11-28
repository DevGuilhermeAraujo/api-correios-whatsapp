<?php
use PHPUnit\Framework\TestCase;
use App\Services\Correios\CorreiosApi;
use App\Services\Logger;
use App\Database\Conexao;

class CorreiosApiTest extends TestCase
{
    public function testApiPma()
    {
        $logger = $this->createMock(Logger::class);
        $db = $this->createMock(Conexao::class);
        $api = new CorreiosApi($logger, $db);

        $result = $api->apiPMA();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }
}
