<?php 
namespace App\Helpers;
use App\Api\Agenda;

use App\Config\AppConfig;
use Exception;


class Helper {

    public static function getClientInHeaders($request) {

        $parsedBody = $request->getParsedBody() ?? [];

        // Verifica se o header 'client' foi encontrado
        if (!isset($parsedBody['aliasEmpresa']) || empty($parsedBody['aliasEmpresa']) || $parsedBody['aliasEmpresa'] === 'undefined') {
           throw new Exception("O parametro Client é requerido", 500);
        }

        return $parsedBody['aliasEmpresa'];

    }
    public static function isProduction(): bool {
        return $_ENV['APP_ENV'] === 'production';
    }

    public static function getNonNullValues(array $data): array
    {
        return array_filter($data, function($value) {
            return !is_null($value);
        });
    }

    public static function getPatientData(array $data): array
    {
        if (count($data) === 0) return $data;

        $patientData = self::getNonNullValues($data[0]);
       
        $patient = [
            'idPessoa'          => $patientData['idPessoa'] ?? null,
            'cpfPaciente'       => $patientData['cpf'] ?? null,
            'nomePaciente'      => $patientData['nomePessoa'] ?? null,
            'dataNascimento'    => $patientData['dataNascimento'] ?? null,
            'sexo'              => $patientData['sexo'] ?? null,
            "idConvenio"        => $patientData['idConvenio'] ?? null,
            "idPlano"           => $patientData['idPlano'] ?? null,
            "descricaoConvenio" => $patientData['descricaoConvenio'] ?? null,
            "descricaoPlano"    => $patientData['descricaoPlano'] ?? null,
        ];

        return $patient;
    }

    public static function getDataNotNull(array $data): array
    {
        if (empty($data)) {
            return $data;
        }

        return self::getPatientData($data);
    }

    public static function getHistoricoAgendamento(array $state, array $params): array
    {
        try {

            $response          = Agenda::getHistoricoAgendamentoPaciente($params);  
            
            $ultimoAgendamento = $response[0] ?? [];
            
            $hasAgendamento    = isset($ultimoAgendamento['dataFutura']) && $ultimoAgendamento['dataFutura'] === '1';
        
            if ($hasAgendamento) {
                
                $result = [
                    'userExists' => true,
                    'redirect' => !empty($ultimoAgendamento['redirect']) ? $ultimoAgendamento['redirect'] : '/especialidade',
                    'state' => [
                        'cpfPaciente'               => $params['cpfPaciente'],
                        'idPlano'           => '',
                        'idConvenio'        => '',
                        'descricaoConvenio' => $ultimoAgendamento['descricaoConvenio'],                    
                        'idPessoa'          => $state['idPessoa'],
                        
                        ...$state
                    ],
                ];
        
                return $result;
            }
        
            return [];
        
        } catch (Exception $error) {
            
            throw $error;
        }
    }

    public static function pacienteShowDetails(array $state): array
    {
        // Definindo o nome baseado na existência das chaves
        $name        = $state['nomePaciente'] ?? ($state['nomePessoa'] ?? '');
        $firstName   = self::getSplitName($name);
        $descricao   = $state['descricaoConvenio'];
        $description = sprintf('%s, o seu convênio continua sendo <b>%s</b>?', $firstName, self::capitalizeWords($descricao));

        // Retorno dos dados
        return [
            'title'        => 'Confirme o convênio',
            'description'  => $description,
            'labelConfirm' => 'Sim',
            'labelCancel'  => 'Não',
            'disableEscapeKeyDown' => true
        ];
    }

    public static function convenioHandleValidation(array $state, array $convenios): array
    {
        try {
            
            // Encontrar dados do convênio
            $convenioData = null;
            foreach ($convenios as $conv) {
                if ($state['idConvenio'] === $conv['idConvenio']) {
                    $convenioData = $conv;
                    break;
                }
            }
        
            // Se não encontrar, lança uma exceção
            if ($convenioData === null) {
                throw new Exception('Convênio não encontrado');
            }
        
            // Construir o resultado
            $result = array_merge([
                
                'nomePaciente'   => $state['nomePaciente'],
                'dataNascimento' => $state['dataNascimento'],
                'cpfPaciente'    => $state['cpfPaciente'],
                'idPlano'        => $state['idPlano'],
                'idPessoa'       => $state['idPessoa']
            ], $convenioData);
        
            return $result;
        
        } catch (Exception $error) {
            // Registrar o erro
            error_log($error->getMessage() . ' ERROR ');
            // Lançar novamente a exceção
            throw $error;
        }
    }

    public static function getFallback(): array
    {
        return [
            "title"                => 'Central de Agendamento',
            "description"          => 'Favor entrar em contato com a Central.',
            "labelConfirm"         => 'Ok',
            //"redirect"             => '/central-agendamento',
            "disableCancel"        => true,
            "disableEscapeKeyDown" => true,
        ];
    }

    public static function getFallbackPrimeiroAgendamento(): array
    {
        return [
            "title"                => 'Central de Agendamento',
            "description"          => 'Para o primeiro agendamento, favor entrar em contato com a central.',
            "labelConfirm"         => 'Ok',
            "disableCancel"        => true,
            "disableEscapeKeyDown" => true,
        ];
    }

    public static function getRedirect($key): string {
        if (isset(AppConfig::$redirects[$key])) {
            return AppConfig::$redirects[$key];
        }
        throw new Exception(json_encode("Key not found in AppConfig: {$key}"));
    }

    // Função para capitalizar palavras
    public static function capitalizeWords($string) {
        if (is_null($string) || empty($string)) return $string;
        return ucwords(strtolower($string));
    }

    // Função para obter o primeiro nome
    public static function getSplitName($name) {
        $nameParts = explode(" ", $name);
        return $nameParts[0];
    }

    public static function handleException(Exception $error): array {
        return [
            'message' => $error->getMessage(), 
            'code' => $error->getCode()
        ];
    }

    public static function isEspecialidadeBloqueada($idEspecialidade, $especialidadesBloqueadas) {
        return in_array($idEspecialidade, $especialidadesBloqueadas);
    }

    public static function isUltimaEspecialidade($idEspecialidade, $ultimasEspecialidades) {
        return in_array($idEspecialidade, $ultimasEspecialidades);
    }

    public static function isEspecialidadeExcluida($idEspecialidade, $especialidadesExcluidas) {
        return in_array($idEspecialidade, $especialidadesExcluidas);
    }

    public static function isEspecialidadeDestaque($idEspecialidade, $especialidades) {
        return in_array($idEspecialidade, $especialidades);
    }

    public static function limparDescricaoEspecialidade($descricao) {
        return preg_replace('/[^A-Za-zÀ-ú ]/', '', $descricao);
    }

    public static function ordenarEspecialidades($a, $b) {
        $aSort = strtolower($a['descricaoEspecialidade']);
        $bSort = strtolower($b['descricaoEspecialidade']);
        return strcmp($aSort, $bSort);
    }

    // Função de auxílio para verificar se uma data existe no array
    public static function dateExists(array $acc, string $date): bool {

        foreach ($acc as $item) {
            if ($item['dataAgenda'] === $date) {
                return true;
            }
        }

        return false;
    }

    // Função principal para obter dias únicos
    public static function getUniqueDays(array $days): array {

        if (!isset($days) || (is_array($days) && count($days) === 0)) {
            return [];
        }

        $data = array_reduce($days, function ($acc, $item) {
            if (!Helper::dateExists($acc, $item['dataAgenda'])) {
                $acc[] = $item;
            }
            return $acc;
        }, []);

        
        return $data;

    }

    // Função de auxílio para normalizar a descrição da especialidade
    public static function normalizeSpecialty(string $specialty): string {
        // Remove caracteres não alfabéticos e converte para minúsculas
        return strtolower(preg_replace('/[^a-zA-Z]/', '', $specialty));
    }

    // Função de auxílio para filtrar os dados pela especialidade
    public static function filterBySpecialty(array $data, string $especialidade): array {

        return array_filter($data, function($item) use ($especialidade) {
           
            return isset($item['descricaoEspecialidade']) && $item['descricaoEspecialidade'] === $especialidade;
        });

    }

    // Função de auxílio para converter data para timestamp Unix
    public static function convertToUnixTime(string $date): int {
        return strtotime($date);
    }

    // Função principal para obter retornos de atendimentos
    public static function getRetornoAtendimentos(array $data, array $especialidade): array {

        
        if (count($data) === 0 || !isset($especialidade['descricaoEspecialidade']) || empty($especialidade['descricaoEspecialidade'])) {
            return [];
        }

        $descricaoEspecialidade = $especialidade['descricaoEspecialidade'];

        $filteredData = Helper::filterBySpecialty($data, $descricaoEspecialidade);

        return array_map(function($item) {
            return Helper::getUnixTimeFromDate($item['dataPrazoRetorno']);
        }, $filteredData);

    }

    public static function formatDate($date, $currentFormat = 'd/m/Y', $formatTo = 'Y-m-d H:i:s') {
        // Parse the date from 'DD/MM/YYYY' to 'YYYY-MM-DD' using DateTime
        $dateTime = \DateTime::createFromFormat($currentFormat, $date, new \DateTimeZone('America/Sao_Paulo'));

        return $dateTime;
    }

    // Função de auxílio para converter data para timestamp Unix
    public static function getUnixTimeFromDate(string $date) {

        $dateFormated = Helper::formatDate($date);

        return $dateFormated->getTimestamp();
    }

    // Função principal para obter dias únicos do agendamento
    public static function getUniqueDaysFromSchedule(array $data, array $daysNotReturn): array {

        if (count($data) === 0) {
            return [];
        }

        return array_reduce($data, function ($acc, $item) use ($daysNotReturn) {

            $dataAgendaUnix = Helper::getUnixTimeFromDate($item['dataAgenda']);

            $isOutReturn = array_reduce($daysNotReturn, function ($carry, $dataPrazoRetorno) use ($dataAgendaUnix) {
                return $carry && $dataAgendaUnix > $dataPrazoRetorno;
            }, true);

            if ($isOutReturn) {
                $acc[] = $item;
            }

            return $acc;

        }, []);
    }

    // Função para verificar se uma data está dentro do mês corrente
    public static function isWithinCurrentMonth($date) {

        // Definindo o fuso horário de São Paulo
        $timezone = new \DateTimeZone('America/Sao_Paulo');
        
        // Obtendo o início e o fim do mês atual no fuso horário de São Paulo
        $currentMonthStart = new \DateTime('first day of this month', $timezone);
        $currentMonthStart->setTime(0, 0, 0); // Início do dia

        $currentMonthEnd = new \DateTime('last day of this month', $timezone);
        $currentMonthEnd->setTime(23, 59, 59); // Final do dia

        // Convertendo a data de entrada para o fuso horário de São Paulo
        $date = \DateTime::createFromFormat('Y-m-d', $date, $timezone);
        if ($date === false) {
            // Handle error if date parsing fails
            return null;
        }

        // Convertendo para timestamp
        $currentMonthStartTimestamp = $currentMonthStart->getTimestamp();
        $currentMonthEndTimestamp = $currentMonthEnd->getTimestamp();
        $dateTimestamp = $date->getTimestamp();

        // Verificando se a data está dentro do mês atual
        $isAfter = $dateTimestamp >= $currentMonthStartTimestamp && $dateTimestamp <= $currentMonthEndTimestamp;
        $daysBeforeOrAfter = ($dateTimestamp - $currentMonthStartTimestamp) / (60 * 60 * 24);

        return (object) [
            'isAfter' => $isAfter,
            'daysBeforeOrAfter' => $daysBeforeOrAfter
        ];

    }

    /**
     * Encontra o índice do primeiro item que satisfaz a condição dada.
     *
     * @param array $items O array onde a busca será realizada.
     * @param callable $callback Função de callback que define a condição de busca.
     * @return int O índice do item encontrado ou -1 se nenhum item satisfizer a condição.
     */
    public static function findIndex(array $items, callable $callback): int
    {
        foreach ($items as $index => $item) {
            if ($callback($item, $index, $items)) {
                return $index;
            }
        }
        return -1; // Retorna -1 se nenhum item for encontrado
    }
}
