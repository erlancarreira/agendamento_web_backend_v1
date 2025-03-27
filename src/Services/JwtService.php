<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * Serviço para gerenciamento de tokens JWT
 */
class JwtService {
    /**
     * Tempo de expiração do token em segundos (1 hora)
     */
    private const TOKEN_EXPIRATION = 3600;

    /**
     * Chave secreta para assinatura do token
     */
    private string $key;

    /**
     * Inicializa o serviço JWT
     * @throws Exception quando JWT_SECRET não está configurado
     */
    public function __construct() {
        if (empty($_ENV['JWT_SECRET'])) {
            throw new Exception('JWT_SECRET não configurado no ambiente', 500);
        }
        $this->key = $_ENV['JWT_SECRET'];
    }

    /**
     * Gera um novo token JWT
     * 
     * @param array $data Dados a serem incluídos no token
     * @return string Token JWT gerado
     */
    public function generateToken(array $data): string {
        $issuedAt = time();
        $expire = $issuedAt + self::TOKEN_EXPIRATION;

        $payload = [
            'iat'  => $issuedAt,
            'exp'  => $expire,
            'data' => $data
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    /**
     * Renova um token JWT existente
     * 
     * @param string $token Token JWT atual
     * @return string Novo token JWT
     * @throws Exception se o token for inválido
     */
    public function renewToken(string $token): string {
        $data = $this->validateToken($token);
        return $this->generateToken($data);
    }

    /**
     * Valida um token JWT
     * 
     * @param string $token Token JWT a ser validado
     * @return array Dados contidos no token
     * @throws Exception se o token for inválido ou expirado
     */
    public function validateToken(string $token): array {
        try {
            if (empty($token)) {    
                throw new Exception('Token não fornecido', 400);
            }
    
            if (!preg_match('/^[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+$/', $token)) {
                throw new Exception('Token malformado', 400);
            }
    
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            
            if (!isset($decoded->data)) {
                throw new Exception('Token inválido: payload incorreto', 400);
            }

            return (array)$decoded->data;
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new Exception('Token expirado', 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new Exception('Token inválido', 401);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
