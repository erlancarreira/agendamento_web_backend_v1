<?php
namespace App\Services;
use App\Api\Convenio;
use App\Config\AppConfig;
use App\Helpers\Helper;

class ConvenioService {

    private $convenio;

    public function __construct() {
        $this->convenio = new Convenio();
    }

    // private function getConveniosAllows(array $convenio): bool {

    //     $hasConvenio = in_array($convenio['idConvenio'], AppConfig::GET_CONVENIOS_PERMITIDOS);

    //     return $hasConvenio;
    // }

    // public function get(array $params): array {
        
    //     $response = [];

    //     $data = Convenio::get($params);
        
    //     if (count($data) > 0) {
    //         foreach( $data as $convenio) {
    //             if ($this->getConveniosAllows($convenio)) {
    //                 array_push($response, $convenio);
    //             }
    //         }     
    //     }

    //     return $response;
    // }

    public function getById(string $id)
    {
        try {
            // Obtém todos os convênios
            $convenios = $this->convenio->get();

            //throw new \Exception(json_encode($convenios));

            // Filtra para encontrar o convênio com o ID correspondente
            $convenioData = array_values(array_filter($convenios, fn($conv) => $conv['idConvenio'] === $id));

            // Se não encontrar, lança uma exceção
            if (empty($convenioData)) {
                throw new \Exception("Convênio com ID {$id} não encontrado.");
            }

            return $convenioData[0]; // Retorna o primeiro item encontrado

        } catch (\Exception $error) {
            error_log("Erro ao buscar convênio: " . $error->getMessage() . " | Trace: " . $error->getTraceAsString());
            throw $error;
        }
    }


    public function getConveniosNotAlloweds(array $convenio, string $client): bool {

        return in_array($convenio['idConvenio'], AppConfig::$configuracoes[$client]['CONVENIOS_BLOQUEADOS']);
    }

    public function handleConvenio(array $state, array $convenios): array {

        $data         = Helper::convenioHandleValidation($state, $convenios);
        $modalDetails = Helper::pacienteShowDetails($data);

        return [
            'type_code'  => AppConfig::USER_EXIST_HAS_CONVENIO, 
            'userExists' => true,
            'redirect'   => AppConfig::$redirects['REDIRECT_PARA_ESPECIALIDADES'],
            'modal'      => $modalDetails, 
            'state'      => $data
        ];
    }

    
}
