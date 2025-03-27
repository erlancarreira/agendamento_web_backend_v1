<?php 

namespace App\Validators;

use InvalidArgumentException;

class ParamsValidator
{ 

    protected $validations;
    protected $className = '';

    public function __construct($validations)
    {
        $this->validations = $validations;
    }


    public function setClassName($className) {
        $this->className = $className;
    }

    public function validate(array $params): void
    {
        foreach ($this->validations as $key) {

            if (!isset($params[$key]) || empty($params[$key])) {
                
                throw new InvalidArgumentException('Todos os parametros são requeridos ' . $key );
            }
            
        }
    }
}