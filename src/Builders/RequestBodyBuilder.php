<?php

namespace App\Builders;

use InvalidArgumentException;

class RequestBodyBuilder
{
    private string $method;
    private array $params;

    public function __construct(string $method, array $params)
    {
        $this->method = $method;
        $this->params = $params;
    }

    private function getDefaultParams() {
        return [
            'hash'         => $this->params['hash'],  
            'aliasEmpresa' => $this->params['aliasEmpresa'] 
        ];  
    }

    public function build(): array
    {
        switch ($this->method) {
            case 'getHorariosConsultaMensal':
                return $this->buildGetHorariosConsultaMensal();
            case 'getConsultaRetornoAtendimentoPaciente': 
                return $this->buildGetConsultasRetornoAtendimentoPaciente();
            default:
                throw new InvalidArgumentException('Método inválido');
        }
    }

    private function buildGetConsultasRetornoAtendimentoPaciente(): array 
    {
        $defaultParams = $this->getDefaultParams();

        return [
            'funcao'    => 'getConsultaRetornoAtendimentoPaciente',
            'idPessoa'  => $this->params['idPessoa'],
            'idUnidade' => $this->params['idUnidade'],
            ...$defaultParams,                
        ];
    }

    private function buildGetHorariosConsultaMensal(): array
    {
        $defaultParams = $this->getDefaultParams();

        $dataNascimento = $this->params['dataNascimento'] ?? $this->params['dataNascimentoPaciente'];
        
        return [
            ...$defaultParams,
            'funcao'          => 'getHorariosConsultaMensal'     ,
            'idUnidade'       => $this->params['idUnidade']      ,
            'idConvenio'      => $this->params['idConvenio']     ,
            'idPlano'         => $this->params['idPlano']        ,
            'idEspecialidade' => $this->params['idEspecialidade'],
            'dataNascimento'  => $dataNascimento,
            
        ];
    }
}