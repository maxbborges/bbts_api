<?php

namespace Src\Controller;
use Src\Gateway\Funcionario;
use Src\Gateway\Evento;
use Src\Gateway\Seguranca;

class APIController {
    private $db;
    private $requestMethod;
    private $parametros;
    private $gateway;
    private $tipo;

    private $funcionario;
    private $evento;

    // Contrutor para a classe FuncionarioController
    public function __construct($db, $requestMethod, $parametros)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->gateway = $parametros['gateway'];
        $this->tipo=$parametros['tipo'];
        unset($parametros['gateway']);
        unset($parametros['tipo']);
        unset($parametros['/']);
        $this->parametros = $parametros;

        $this->funcionario = new Funcionario($db);
        $this->evento = new Evento($db);
        $this->login = new Seguranca($db);
    }

    // Função para tratamento da requisição GET, POST, PUT e DELETE
    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->parametros) {
                    $response = $this->get($this->parametros);
                } else {
                    $response = $this->getAll();
                };
                break;
            case 'POST':
                if($this->gateway == 'login'){
                    $response = $this->login();
                } else if ($this->gateway == 'trocaSenha'){
                    $response = $this->trocaSenha();
                } else {
                    $response = $this->create();
                };
                break;
            case 'PUT':
                $response = $this->update($this->parametros);
                break;
            case 'DELETE':
                $response = $this->delete($this->userId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }
    
    // Função para recuperação de todos os parametros: Evento, Funcionario, 
    private function getAll()
    {
        switch ($this->gateway){
            case 'evento':
                $result = $this->evento->findAll();
                break;
                
            case 'funcionario':
                $result = $this->funcionario->findAll();
                break;
            
            default:
                $result = array('Necessário GATEWAY para consulta (GETALL)!');
        }
            
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Função para recuperar dado especifico: Evento, Funcionario
    private function get($parametros)
    {
        switch ($this->gateway){
            case 'evento':
                if ($this->tipo=='data'){
                    $result = $this->evento->findbyDate($parametros['data']);
                } else{
                    $result = $this->evento->findMatricula($parametros['matricula']);
                }
                
                break;
                
            case 'funcionario':
                if ($this->tipo=='pessoal'){
                    $result = $this->funcionario->findPessoal($parametros['matricula']);
                } else if ($this->tipo=='corporativo'){
                    $result = $this->funcionario->findCorporativo($parametros['matricula']);
                } else if ($this->tipo=='funcionario'){
                    $result = $this->funcionario->findFuncionario($parametros['matricula']);
                } else {
                    return $this->unprocessableEntityResponse();
                }
                break;
            
            default:
                $result = array('Necessário GATEWAY para consulta (GET)!');
        }
        
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Função para inserção de dados
    private function create()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        switch ($this->gateway){
            case 'evento':
                if (! $this->validate($input)) {
                    return $this->unprocessableEntityResponse();
                }
                
                if ($this->tipo=='ferias_abonos'){
                    $result = $this->evento->insertFeriasAbonos($input);
                } else if ($this->tipo=='outros') {
                    $this->evento->insertOutros($input);
                } else {
                    return $this->unprocessableEntityResponse();
                }
            
                break;
                
            case 'funcionario':
                if (! $this->validate($input)) {
                    return $this->unprocessableEntityResponse();
                }

                if ($this->tipo=='funcionario'){
                    $result = $this->funcionario->insertFuncionario($input);
                } else if ($this->tipo=='dados_corporativos'){
                    $result = $this->funcionario->insertCorporativos($input);
                } else if ($this->tipo=='dados_pessoais') {
                    $this->funcionario->insertPessoais($input);
                } else {
                    return $this->unprocessableEntityResponse();
                }
                break;
            
            default:
                $result = array('Num parametro Encontrado GET!');
        }

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode($result);
        // $response['body'] = $result ? json_encode($result) : false;
        return $response;
    }

    // Função para atualização de dados
    private function update($parametros)
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        switch ($this->gateway){
            case 'evento':
                $result = $this->evento->find($parametros['matricula']);
                if (! $result) {
                    return $this->notFoundResponse();
                }

                if ($this->tipo=='ferias_abonos'){
                    if (! $this->validate($input)) {
                        return $this->unprocessableEntityResponse();
                    }
                    $this->evento->update($id, $input);
                    
                } else if ($this->tipo=='outros') {
                    if (! $this->validate($input)) {
                        return $this->unprocessableEntityResponse();
                    }
                    $this->evento->update($id, $input);
                } else {
                    return $this->unprocessableEntityResponse();
                }
                break;
            case 'funcionario':
                $result = $this->funcionario->find($parametros['matricula']);
                if (! $result) {
                    return $this->notFoundResponse();
                }

                if ($this->tipo=='dados_corporativos'){
                    if (! $this->validate($input)) {
                        return $this->unprocessableEntityResponse();
                    }
                    // $this->funcionario->update($id, $input);
                } else if ($this->tipo=='dados_pessoais') {
                    if (! $this->validate($input)) {
                        return $this->unprocessableEntityResponse();
                    }
                    // $this->funcionario->update($id, $input);
                } else {
                    return $this->unprocessableEntityResponse();
                }
                break;
            default:
                $error = array('Num parametro Encontrado PUT!');
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = $error ? json_encode($error) : null;
        return $response;
    }

    // Função para Deletar dados
    private function delete($id)
    {
        // $result = $this->personGateway->find($id);
        // if (! $result) {
        //     return $this->notFoundResponse();
        // }
        // $this->personGateway->delete($id);
        // $response['status_code_header'] = 'HTTP/1.1 200 OK';
        // $response['body'] = null;
        // return $response;
    }

    private function trocaSenha(){
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validate($input)) {
            return $this->unprocessableEntityResponse();
        }

        $result = $this->login->trocaSenha($input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function login(){
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validate($input)) {
            return $this->unprocessableEntityResponse();
        }

        $result = $this->login->autenticar($input);

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function validate($input)
    {
        // if (! isset($input['firstname'])) {
        //     return false;
        // }
        // if (! isset($input['lastname'])) {
        //     return false;
        // }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}