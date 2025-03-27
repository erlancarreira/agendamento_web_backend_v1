<?php

declare(strict_types=1);

namespace App\Domain\DomainException;

use Exception;

class DatabaseException extends DomainException
{
    public static function queryError(string $message): self
    {
        return new self('Erro ao consultar banco de dados: ' . $message, 500);
    }

    public static function connectionError(string $message): self
    {
        return new self('Erro de conexão com banco de dados: ' . $message, 500);
    }

    public static function validationError(string $message): self
    {
        return new self('Erro de validação: ' . $message, 400);
    }
}
