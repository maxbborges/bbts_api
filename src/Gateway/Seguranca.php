<?php
namespace Src\Gateway;

Class Seguranca {
    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function inserir($statement,$statement1){
        try {
            $this->db->beginTransaction();
            foreach($statement as $key=>$value){
                $state = $this->db->prepare($value);
                $result = $state->execute($statement1[$key]);
                if (!$result){
                    $this->db->rollBack();
                    return $result;
                }
                $state->closeCursor();
            }

            $this->db->commit();
            return $result;

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function selecionar($statement,$statement1){
        try {
            $this->db->beginTransaction();
            foreach($statement as $key=>$value){
                $state = $this->db->prepare($value);
                $state->execute($statement1[$key]);
                $result = $state->fetchAll(\PDO::FETCH_ASSOC);
                $state->closeCursor();
            }

            if (!$result or count($result)<1){
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return $result;

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function trocaSenha(Array $input){
        $statement = [
            "SELECT s.id as id_senhas, a.id as id_administrativo  
            FROM senhas s, administrativo a, seguranca s2
            WHERE a.matricula_funcionario = :matricula
                AND s2.id_administrativo = a.id
                AND s.id_seguranca = s2.id 
                AND s.senhas=md5(:senha);"
        ];

        $statement1 = [
            [
                'matricula' => $input['matricula'],
                'senha'=> $input['senhaOld']
            ],
        ];

        $senha = $this->selecionar($statement,$statement1);
        if(!$senha){
            return false;
        }

        $statement6 = [
            "UPDATE senhas SET senhas=md5(:senha) WHERE id = :id_senhas;",
            "UPDATE administrativo SET data_alteracao = CURDATE() WHERE id=:id_administrativo;"
        ];

        $statement7 = [
            [
                'senha' => $input['senhaNew'],
                'id_senhas'=> $senha[0]['id_senhas']
            ],
            [
                'id_administrativo' => $senha[0]['id_administrativo']
            ],
        ];

        return $this->inserir($statement6,$statement7);
    }

    public function autenticar(Array $input)
    {
        $matricula = isset($input['matricula']) ? $input['matricula'] : NULL;
        $senha = isset($input['senha']) ? $input['senha'] : NULL;

        if (!$matricula or !$senha){
            return false;
        }

        $statement = [
            "SELECT 'matricula' as chave, a.matricula_funcionario as valor
            FROM senhas s, administrativo a, seguranca s2
            WHERE a.matricula_funcionario = :matricula
                AND s2.id_administrativo = a.id
                AND s.id_seguranca = s2.id 
                AND s.senhas=md5(:senha)
            UNION ALL 
            SELECT 'regras', count(role) 
            FROM roles r2 , administrativo a, seguranca s2 
            WHERE a.matricula_funcionario = :matricula
                AND s2.id_administrativo = a.id
                AND r2.id_seguranca = s2.id
            UNION 
            SELECT 'funcionario', nome 
            FROM dados_pessoais dp , dados_funcionario df 
            WHERE df.matricula_funcionario = :matricula
                AND dp.id_funcionario = df.id;"
        ];

        $statement1 = [
            [
                'matricula' => $matricula,
                'senha'=> $senha
            ],
        ];

        try {
            $this->db->beginTransaction();
            foreach($statement as $key=>$value){
                $state = $this->db->prepare($value);
                $state->execute($statement1[$key]);
                $result = $state->fetchAll(\PDO::FETCH_ASSOC);
                $state->closeCursor();
            }

            if (!$result or count($result)<3){
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return $result;

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}