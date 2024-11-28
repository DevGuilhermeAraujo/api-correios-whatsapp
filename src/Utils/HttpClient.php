<?php

namespace App\Utils;

use GuzzleHttp\Client;

class HttpClient
{
    public static function makeRequest(string $method, string $url, array $headers = [], string $body = null): array
    {
        $client = new Client();

        try {
            $options = ['headers' => $headers];
            if ($body) {
                $options['body'] = $body;
            }

            $response = $client->request($method, $url, $options);

            return [
                'httpcode' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true),
                'error' => null
            ];
        } catch (\Exception $e) {
            echo "Erro na requisição: " . $e->getMessage();
            return [
                'httpcode' => 0,
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
