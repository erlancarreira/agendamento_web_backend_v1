<?php

namespace App\Factories;

//use App\Interfaces\ParticularConvenioInterface;
use App\Services\PageAcor;
use App\Services\PageDefault;

use App\Api\Paciente;

class PageFactory
{
    
    public static function create(string $client)
    {
        $paciente = new Paciente();

        if ($client === 'ACOR') {
            
            return new PageACor($paciente );
        }

        return new PageDefault($paciente );
    }
}