<?php

namespace App\Services;

use App\Api\Pessoa;
use App\Helpers\Helper;

use App\Validators\User\createValidator;
use Exception;

class UserService
{
    private Pessoa $api;
    private createValidator $validator;

    public function __construct() {
        $this->api = new Pessoa();
    } 

    public function get(array $params): array
    {
        return $this->api->get($params);
    }

    public function create(array $params): array
    {
        return $this->api->post($params);
    }

    public function createIfNotExist(array $params): array
    {
        
        try {

            $userExists = true;

            $data = $this->get($params);

            if (!isset($data['idPessoa'])) {
                $userExists         = false;
                $response           = $this->create($params);
                $params['idPessoa'] = $response['dados']['idPessoa'];
            }
            
            return [
                "userExists" => $userExists, 
                ...$params
            ];
            
            
        } catch (Exception $error) {
            // Log the error if necessary
            throw $error;
        }
    }

    
}