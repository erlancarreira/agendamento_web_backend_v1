<?php

namespace App\Http\Client\Clinnet;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Api
{
    private static $client;
    protected static $aliasEmpresa;
    protected static $hash;
    protected static $idUnidade;

    public static function setAliasEmpresa($empresa) {
        self::$aliasEmpresa = $empresa ?? '';
    }

    public static function setHash($hash) {
        self::$hash = $hash ?? '';
    }

    public static function setIdUnidade($idUnidade) {
        self::$idUnidade = $idUnidade ?? '';
    }

    public static function setCredentials(array $credentials) {
        self::setHash($credentials['hash'] ?? '');
        self::setAliasEmpresa($credentials['aliasEmpresa'] ?? '');
        self::setIdUnidade($credentials['idUnidade'] ?? '');
    }

    private static function getClient(): Client
    {
        if (self::$client === null) {
            self::$client = new Client([
                'base_uri' => 'https://clinnet.com.br/', 
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                
                'verify' => false 
            ]);
        }

        return self::$client;
    }

    public static function request(string $method, string $pathname, array $options = []): array
    {
        try {
            $client = self::getClient();
            
            // Adiciona as credenciais ao corpo da requisição
            if (!isset($options['json'])) {
                $options['json'] = [];
            }
            
            $options['json']['hash']         = self::$hash;
            $options['json']['aliasEmpresa'] = self::$aliasEmpresa;
            $options['json']['idUnidade']    = self::$idUnidade;
            
            $response = $client->request($method, $pathname, $options);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null,
            ];
        }
    }

    public static function get(string $pathname, array $query = []): array
    {
        $options = [];
        if (!empty($query)) {
            $options['query'] = $query;
        }

        return self::request('GET', $pathname, $options);
    }

    public static function post(string $pathname, array $body = []): array
    {
        $options = [];
        if (!empty($body)) {
            $options['json'] = $body;
        }

        return self::request('POST', $pathname, $options);
    }

    public static function put(string $pathname, array $body = []): array
    {
        $options = [];
        if (!empty($body)) {
            $options['json'] = $body;
        }

        return self::request('PUT', $pathname, $options);
    }

    public static function delete(string $pathname, array $body = []): array
    {
        $options = [];
        if (!empty($body)) {
            $options['json'] = $body;
        }

        return self::request('DELETE', $pathname, $options);
    }
}
