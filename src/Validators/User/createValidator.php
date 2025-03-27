<?php

namespace App\Validators\User;

use InvalidArgumentException;

class createValidator
{
    public function validate(array $data): void
    {
        $this->validateCpf($data['cpfPaciente'] ?? null);
        $this->validateNome($data['nomePaciente'] ?? null);
        $this->validateSexo($data['sexoPaciente'] ?? null);
        $this->validateDataNascimento($data['dataNascimentoPaciente'] ?? null);
        $this->validateEmail($data['emailPaciente'] ?? null);
        $this->validateDdd($data['dddCelularPaciente'] ?? null);
        $this->validateNumeroCelular($data['numeroCelularPaciente'] ?? null);
    }

    private function validateCpf(?string $cpf): void
    {
        if (empty($cpf) || !preg_match('/^\d{11}$/', $cpf)) {
            throw new InvalidArgumentException('CPF inválido.');
        }
    }

    private function validateNome(?string $nome): void
    {
        if (empty($nome)) {
            throw new InvalidArgumentException('Nome do paciente é obrigatório.');
        }
    }

    private function validateSexo(?string $sexo): void
    {
        if (empty($sexo) || !in_array($sexo, ['M', 'F'], true)) {
            throw new InvalidArgumentException('Sexo inválido. Deve ser "M" ou "F".');
        }
    }

    private function validateDataNascimento(?string $dataNascimento): void
    {
        if (empty($dataNascimento) || !preg_match('/^\d{2}/\d{2}/\d{4}$/', $dataNascimento)) {
            throw new InvalidArgumentException('Data de nascimento inválida. Formato esperado: DD/MM/YYYY.');
        }
    }

    private function validateEmail(?string $email): void
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('E-mail inválido.');
        }
    }

    private function validateDdd(?string $ddd): void
    {
        if (empty($ddd) || !preg_match('/^\d{2}$/', $ddd)) {
            throw new InvalidArgumentException('DDD do celular inválido.');
        }
    }

    private function validateNumeroCelular(?string $numeroCelular): void
    {
        if (empty($numeroCelular) || !preg_match('/^\d{8,9}$/', $numeroCelular)) {
            throw new InvalidArgumentException('Número do celular inválido.');
        }
    }
}
