<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\DatabaseManager;
use Dotenv\Dotenv;

try {
    echo "Iniciando teste de conexão com o banco de dados...\n\n";

    // Carrega variáveis de ambiente
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    echo "✓ Variáveis de ambiente carregadas\n";

    // Testa se todas as variáveis necessárias estão definidas
    $requiredVars = [
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD'
    ];

    foreach ($requiredVars as $var) {
        if (empty($_ENV[$var])) {
            throw new Exception("Variável de ambiente $var não está definida");
        }
        echo "✓ Variável $var está definida\n";
    }

    echo "\nTentando conectar ao banco de dados...\n";
    echo "Host: {$_ENV['DB_HOST']}\n";
    echo "Porta: {$_ENV['DB_PORT']}\n";
    echo "Banco: {$_ENV['DB_DATABASE']}\n\n";

    // Inicializa o gerenciador de banco de dados
    DatabaseManager::initialize();

    // Testa a conexão
    DatabaseManager::testConnection();

    echo "✓ Conexão estabelecida com sucesso!\n";
    exit(0);

} catch (Exception $e) {
    echo "\n❌ Erro: " . $e->getMessage() . "\n";
    
    if (isset($_ENV['displayErrorDetails']) && $_ENV['displayErrorDetails'] === 'true') {
        echo "\nStack trace:\n";
        echo $e->getTraceAsString() . "\n";
    }
    
    exit(1);
}
