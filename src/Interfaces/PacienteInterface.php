<?php

namespace App\Interfaces;

interface PacienteInterface
{
    public function byCPF(array $params): array;

    public function get(array $params): array;
}
