<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\DomainException\DatabaseException;
use Illuminate\Database\Capsule\Manager as Capsule;
use PDO;
use PDOException;
use Exception;

class DatabaseManager
{
    private static array $requiredEnvVars = [
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD'
    ];

    /**
     * Configura e inicializa a conexão com o banco de dados
     * @throws DatabaseException
     */
    public static function initialize(): void
    {
        try {
            self::validateEnvironment();

            $host = self::getEnvVar('DB_HOST');
            
            // Verifica se o host é válido
            if (!self::isHostAccessible($host)) {
                throw new Exception(
                    "Não foi possível resolver o host do banco de dados: '$host'. " .
                    "Verifique se o endereço está correto e se há conexão com o servidor."
                );
            }

            $capsule = new Capsule;

            $capsule->addConnection([
                'driver'    => 'mysql',
                'url'       => self::getEnvVar('DATABASE_URL', ''),
                'host'      => $host,
                'port'      => self::getEnvVar('DB_PORT'),
                'database'  => self::getEnvVar('DB_DATABASE'),
                'username'  => self::getEnvVar('DB_USERNAME'),
                'password'  => self::getEnvVar('DB_PASSWORD'),
                'unix_socket' => self::getEnvVar('DB_SOCKET', ''),
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
                'options'   => self::getConnectionOptions(),
            ]);

            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            // Testa a conexão
            try {
                $capsule->getConnection()->getPdo();
            } catch (PDOException $e) {
                $error = $e->getMessage();
                if (strpos($error, 'getaddrinfo') !== false) {
                    throw new Exception("Erro de DNS: Não foi possível resolver o endereço do host '$host'");
                } elseif (strpos($error, 'Connection refused') !== false) {
                    throw new Exception("Conexão recusada pelo servidor '$host'. Verifique se o servidor MySQL está em execução e acessível na porta especificada.");
                } elseif (strpos($error, 'Access denied') !== false) {
                    throw new Exception("Acesso negado. Verifique suas credenciais de banco de dados.");
                } else {
                    throw $e;
                }
            }

        } catch (Exception $e) {
            throw DatabaseException::connectionError($e->getMessage());
        }
    }

    /**
     * Valida se todas as variáveis de ambiente necessárias estão definidas
     * @throws DatabaseException
     */
    private static function validateEnvironment(): void
    {
        $missingVars = [];

        foreach (self::$requiredEnvVars as $var) {
            if (empty($_ENV[$var])) {
                $missingVars[] = $var;
            }
        }

        if (!empty($missingVars)) {
            throw DatabaseException::validationError(
                'Variáveis de ambiente obrigatórias não definidas: ' . implode(', ', $missingVars)
            );
        }
    }

    /**
     * Verifica se um host está acessível
     */
    private static function isHostAccessible(string $host): bool
    {
        // Remove protocolo se existir
        $host = preg_replace('(^https?://)', '', $host);
        
        // Tenta resolver o DNS do host
        $dns = @dns_get_record($host, DNS_A + DNS_AAAA);
        
        if ($dns === false || empty($dns)) {
            // Se falhar, tenta com gethostbyname
            $ip = @gethostbyname($host);
            // Se o IP retornado for igual ao host, significa que não foi possível resolver
            return $ip !== $host;
        }
        
        return true;
    }

    /**
     * Obtém uma variável de ambiente com fallback
     */
    private static function getEnvVar(string $name, ?string $default = null): string
    {
        return $_ENV[$name] ?? $default ?? '';
    }

    /**
     * Configura as opções de conexão do PDO
     */
    private static function getConnectionOptions(): array
    {
        $options = [
            // Define timeout de conexão de 5 segundos
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if (extension_loaded('pdo_mysql')) {
            $sslCa = getenv('MYSQL_ATTR_SSL_CA');
            if ($sslCa) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
            }
        }

        return $options;
    }

    /**
     * Testa a conexão com o banco
     * @throws DatabaseException
     */
    public static function testConnection(): void
    {
        try {
            $pdo = Capsule::connection()->getPdo();
            
            // Tenta executar uma query simples
            $pdo->query('SELECT 1');
            
        } catch (PDOException $e) {
            $message = self::formatPDOError($e);
            throw DatabaseException::connectionError($message);
        } catch (Exception $e) {
            throw DatabaseException::connectionError("Falha no teste de conexão: " . $e->getMessage());
        }
    }

    /**
     * Formata mensagens de erro do PDO para serem mais amigáveis
     */
    private static function formatPDOError(PDOException $e): string
    {
        $message = $e->getMessage();
        $host = self::getEnvVar('DB_HOST');

        if (strpos($message, 'getaddrinfo') !== false) {
            return "Não foi possível conectar ao host '$host'. Verifique:\n" .
                   "1. Se o endereço do host está correto\n" .
                   "2. Se há conexão com a internet\n" .
                   "3. Se o servidor está acessível";
        }

        if (strpos($message, 'Connection refused') !== false) {
            return "Conexão recusada pelo servidor '$host'. Verifique:\n" .
                   "1. Se o servidor MySQL está em execução\n" .
                   "2. Se a porta está correta e acessível\n" .
                   "3. Se há algum firewall bloqueando a conexão";
        }

        if (strpos($message, 'Access denied') !== false) {
            return "Acesso negado ao banco de dados. Verifique:\n" .
                   "1. Se o usuário e senha estão corretos\n" .
                   "2. Se o usuário tem permissão para acessar o banco\n" .
                   "3. Se o banco de dados existe e está correto";
        }

        return "Erro de conexão com o banco de dados: $message";
    }
}
