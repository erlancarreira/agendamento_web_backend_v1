<?php

namespace App\Factories;

use App\Api\Convenio;
use App\Api\Paciente;

use App\Interfaces\PacienteInterface;
use App\Services\AcorPaciente;
use App\Services\DefaultPaciente;

class PacienteFactory
{
    
    public static function create(string $client): PacienteInterface
    {
        $paciente = new Paciente();
        $convenio = new Convenio();

        if ($client === 'ACOR') {
            
            return new AcorPaciente($paciente, $convenio);
        }

        return new DefaultPaciente($paciente, $convenio);
    }
}