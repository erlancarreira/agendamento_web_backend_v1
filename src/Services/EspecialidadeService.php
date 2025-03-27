<?php
namespace App\Services;

use App\Api\Especialidade;
use App\Config\AppConfig;
use App\Helpers\Helper;


class EspecialidadeService {

    private function isEspecialidadePermitida(array $especialidade, array $especialidadesNaoPermitidas): bool {
        return !in_array($especialidade['idEspecialidade'], $especialidadesNaoPermitidas);
    }

    private function getEspecialidadesConfig(string $client): array {
        // Obtenha a configuração específica para o client
        return AppConfig::$configuracoes[$client] ?? [
            'ESPECIALIDADES_EXCLUIDAS'   => [],
            'ESPECIALIDADES_BLOQUEADAS'  => [],
            'ESPECIALIDADES_EM_DESTAQUE' => [],
            'ULTIMAS_ESPECIALIDADES'     => [],
        ];
    }

    private function getEspecialidadeByOrder(
        array $items, 
        array $especialidadesExcluidas,
        array $especialidadesBloqueadas,
        array $especialidadesEmDestaque,
        array $ultimasEspecialidades 
    ): array {
        
        $especialidadeMap = []; // Evita duplicatas usando idEspecialidade como chave
        $lastItems = [];
        $especialidadeList = [];
        $especialidadeSemDestaque = [];
    
        foreach ($items as $item) {
            
            $idEspecialidade = $item['idEspecialidade'];
            $descricaoEspecialidade = $item['descricaoEspecialidade'];
    
            // Ignora se a especialidade já foi processada ou se está na lista de excluídos
            if (isset($especialidadeMap[$idEspecialidade]) || Helper::isEspecialidadeExcluida($idEspecialidade, $especialidadesExcluidas)) {
                continue;
            }
    
            // Marca a especialidade como bloqueada, se necessário
            $item['bloqueado'] = Helper::isEspecialidadeBloqueada($idEspecialidade, $especialidadesBloqueadas);
    
            if (Helper::isUltimaEspecialidade($idEspecialidade, $ultimasEspecialidades)) {
                $item['descricaoEspecialidade'] = Helper::limparDescricaoEspecialidade($descricaoEspecialidade);
                $lastItems[] = $item;
            } elseif (Helper::isEspecialidadeDestaque($idEspecialidade, $especialidadesEmDestaque)) {
                $item['descricaoEspecialidade'] = Helper::limparDescricaoEspecialidade($descricaoEspecialidade);
                $especialidadeList[] = $item;
            } else {
                $especialidadeSemDestaque[] = $item;
            }
    
            // Adiciona ao mapa para evitar duplicatas
            $especialidadeMap[$idEspecialidade] = true;
        }
    
        // Ordenação das especialidades em destaque
        $especialidadeDestaqueOrdenada = array_map(function ($idEspecialidade) use ($especialidadeList) {
            foreach ($especialidadeList as $especialidade) {
                if ($especialidade['idEspecialidade'] === $idEspecialidade) {
                    return $especialidade;
                }
            }
            return null;
        }, array_filter($especialidadesEmDestaque, fn($id) => isset($especialidadeMap[$id])));
    
        // Ordena especialidades sem destaque
        usort($especialidadeSemDestaque, fn($a, $b) => Helper::ordenarEspecialidades($a, $b));
    
        return array_merge($especialidadeDestaqueOrdenada, $especialidadeSemDestaque, $lastItems);
    }
    

    public function get(): array {
        
        
        $client       = $_ENV['CLIENT'];
        $config       = $this->getEspecialidadesConfig($client);
        $response     = Especialidade::get();

        $data = $this->getEspecialidadeByOrder(
            $response,
             $config['ESPECIALIDADES_EXCLUIDAS'  ],
            $config['ESPECIALIDADES_BLOQUEADAS' ],
            $config['ESPECIALIDADES_EM_DESTAQUE'],
               $config['ULTIMAS_ESPECIALIDADES'    ],    
        );

       

        return $data;
    }
}