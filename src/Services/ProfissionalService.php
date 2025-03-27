<?php

namespace App\Services;

use App\Api\Agenda;
use App\Config\AppConfig;
use App\Helpers\Helper;
use Exception;

class ProfissionalService
{

    private $perfis;

    private $client;

    private $qtHorariosExibir;
    
    public function __construct()
    {
        $this->client = $_ENV['CLIENT'];
        $this->perfis = [];
    }

    private function setQuantidadeHorariosExibir(string $client): void {
        $this->qtHorariosExibir = AppConfig::$configuracoes[$client]['QUANTIDADE_HORARIOS_EXIBIR'];
    }

    private function getQuantidadeHorariosExibir(): int {
        return $this->qtHorariosExibir;
    }

    private function createNewItem(array $item): array
    {
        return [
            'idAgenda'         => $item['idAgenda'],
            'dataAgenda'       => $item['dataAgenda'],
            'diaSemana'        => $item['diaSemana'],
            'horarioAgenda'    => $item['horarioAgenda'],
            'descricaoUnidade' => $item['descricaoUnidade'],
            'idProfissional'   => $item['idProfissional'],
            'nomeProfissional' => $item['nomeProfissional'],
            'idAgCad'          => $item['idAgCad'],
            'tipoRestricao'    => $item['tipoRestricao'],
        ];
    }

    

    public function getDias($params)
    {
        try {

            $this->setQuantidadeHorariosExibir($this->client);
            
            $items = Agenda::getHorariosConsultaMensal($params);

            $perfis = $this->getAgendaPorDiaSelecionado([
                'items' => $items, 
                'diaEscolhido' => $params['diaEscolhido'] 
            ]);

            return $perfis; 
        
        } catch (Exception $e) {

            throw new Exception($e->getMessage(), $e->getCode());
            
        } 
    }

    public function getAgendaPorDiaSelecionado(array $request): array {
        try {
            
            $profissionais = $this->getTodasDatasHorasPorDiaEscolhido($request);
            $agrupados = [];

            foreach ($profissionais as $profissional) {
                $key = $profissional['idProfissional'] . '-' . $profissional['dataAgenda'];

                if (!isset($agrupados[$key])) {
                    // Inicializa o item no array agrupado
                    $agrupados[$key] = [
                        ...$this->createNewItem($profissional), 'horarios' => [] 
                        // Inicializa o array de horários vazio
                    ];
                }

                // Cria um novo item com os campos desejados para adicionar ao array de horários
                $newItem = $this->createNewItem($profissional);

                // Verifica se o limite de horários foi atingido antes de adicionar
                if (count($agrupados[$key]['horarios']) < $this->getQuantidadeHorariosExibir()) {
                    $agrupados[$key]['horarios'][] = $newItem;
                }
            }

            // Retorna o array agrupado como resposta
            return array_values($agrupados);

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    
    public function getTodasDatasHorasPorDiaEscolhido(array $request): array
    {
        if (!isset($request['items']) || !is_array($request['items'])) {
            throw new \InvalidArgumentException('Invalid request: items key is missing or not an array');
        }

        return array_reduce($request['items'], function ($acc, $item) use ($request) {

            $item['horarios'] = [];

            $profissionalDia = $item['dataAgenda'] ?? null;

            if ($request['diaEscolhido'] === $profissionalDia) {
                $acc[] = $item;
            }

            return $acc;
            
        }, []);
    }


    
}
