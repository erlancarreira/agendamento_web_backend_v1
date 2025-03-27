<?php

namespace App\Validators\Agenda;

use InvalidArgumentException;

class agendarValidator
{
    public function validate(array $data): void
    {
        $this->validateIdAgenda($data['idAgenda'] ?? null);
        
        $this->validateIdPessoa($data['idPessoa'] ?? null);
        $this->validateNomePessoa($data['nomePessoa'] ?? null);
        $this->validateIdPlano($data['idPlano'] ?? null);
        $this->validateIdEspecialidade($data['idEspecialidade'] ?? null);
        $this->validateIdProcedimento($data['idProcedimento'] ?? null);
    }

    private function validateIdAgenda(?int $idAgenda): void
    {
        if (empty($idAgenda) || !is_int($idAgenda)) {
            throw new InvalidArgumentException('ID da Agenda inválido.');
        }
    }

    private function validateIdPessoa(?int $idPessoa): void
    {
        if (empty($idPessoa) || !is_int($idPessoa)) {
            throw new InvalidArgumentException('ID da Pessoa inválido.');
        }
    }

    private function validateNomePessoa(?string $nomePessoa): void
    {
        if (empty($nomePessoa)) {
            throw new InvalidArgumentException('Nome da Pessoa é obrigatório.');
        }
    }

    private function validateIdPlano(?int $idPlano): void
    {
        if (empty($idPlano) || !is_int($idPlano)) {
            throw new InvalidArgumentException('ID do Plano inválido.');
        }
    }

    private function validateIdEspecialidade(?int $idEspecialidade): void
    {
        if (empty($idEspecialidade) || !is_int($idEspecialidade)) {
            throw new InvalidArgumentException('ID da Especialidade inválido.');
        }
    }

    private function validateIdProcedimento(?int $idProcedimento): void
    {
        if (empty($idProcedimento) || !is_int($idProcedimento)) {
            throw new InvalidArgumentException('ID do Procedimento inválido.');
        }
    }
}
