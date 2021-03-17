<?php
namespace Src\Gateway;

class Funcionario {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function inserir($statement,$statement1){
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

    public function findAll()
    {
        $statement = "
            SELECT matricula_funcionario as matricula, 
                nome from dados_funcionario df, dados_pessoais dp 
            WHERE df.id = dp.id_funcionario ;
        ";

        try {
            $statement = $this->db->query($statement);

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findFuncionario($matricula)
    {
        $statement = [
            "SELECT *
            FROM funcionario
            WHERE matricula=(:matricula);"
        ];
        $statement1 = [
            [
                'matricula' => $matricula
            ],
        ];

        return $this->selecionar($statement,$statement1);
    }

    public function findCorporativo($matricula)
    {
        $statement = "
            SELECT
                df2.matricula_funcionario as matricula, chave_c , cartao_bb, cartao_capital, 
                cartao_bbts,num_contrato
            FROM dados_corporativos dc, dados_funcionario df2 
            WHERE df2.matricula_funcionario =? 
                and df2.id = dc.id_funcionario;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($matricula));
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function findPessoal($matricula)
    {
        $statement = " 
            SELECT df.matricula_funcionario as matricula, nome, cpf, rg
            FROM dados_funcionario df, dados_pessoais dp 
            WHERE df.matricula_funcionario =? 
                and df.id = dp.id_funcionario;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($matricula));
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function insertFuncionario(Array $input){
        $statement = [
            "INSERT INTO funcionario (matricula)
            VALUES (:matricula);",
            "INSERT INTO administrativo (matricula_funcionario,data_cadastro,data_alteracao) 
            VALUES (:matricula,CURDATE(),CURDATE());",
            "INSERT INTO seguranca (id_administrativo)
            VALUES (LAST_INSERT_ID());",
            "INSERT INTO senhas (id_seguranca,senhas)
            VALUES (LAST_INSERT_ID(),md5('12345'));",
            "INSERT INTO dados_funcionario (matricula_funcionario,data_cadastro,data_alteracao)
            VALUES (:matricula,CURDATE(),CURDATE());",
            "INSERT INTO dados_pessoais(id_funcionario,nome)
            VALUES (LAST_INSERT_ID(),:nome);"
        ];
        $statement1 = [
            [
                'matricula' => $input['matricula']
            ],
            [
                'matricula' => $input['matricula']
            ],
            [],
            [],
            [
                'matricula' => $input['matricula']
            ],
            [
                'nome' => $input['nome']
            ],
        ];

        return $this->inserir($statement,$statement1);
    }

    public function insertCorporativos(Array $input)
    {
        $data_contratacao = date('Y-m-d 00:00:00',strtotime($input['data_contratacao']));
        $statement = [
            "SELECT id_funcionario 
            FROM dados_corporativos dc , dados_funcionario df 
            WHERE dc.id_funcionario = df.id 
                AND df.matricula_funcionario = :matricula;"
        ];
        $statement1 = [
            [
                'matricula' => $input['matricula']
            ],
        ];

        if(!is_bool($this->selecionar($statement,$statement1))){
            return false;
        }

        $statement = [
            "INSERT INTO dados_funcionario (matricula_funcionario,data_cadastro,data_alteracao)
            VALUES (:matricula,CURDATE(),CURDATE());",
            "INSERT INTO dados_corporativos (id_funcionario,chave_c,cartao_bb,cartao_capital,cartao_bbts,num_contrato,data_contratacao)
            VALUES (LAST_INSERT_ID(),:chave_c,:cartao_bb,:cartao_capital,:cartao_bbts,:num_contrato,:data_contratacao);"
        ];
        $statement1 = [
            [
                'matricula' => $input['matricula']
            ],
            [
                'chave_c' => $input['chave_c'] ?? null,
                'cartao_bb' => $input['cartao_bb'] ?? null,
                'cartao_capital' => $input['cartao_capital'] ?? null,
                'cartao_bbts' => $input['cartao_bbts'] ?? null,
                'num_contrato' => $input['num_contrato'] ?? null,
                'data_contratacao' => $input['data_contratacao'] ?? null
            ],
        ];

        return $this->inserir($statement,$statement1);
    }

    public function updatePessoal(Array $input)
    {
        $statement = [
            "SELECT df.id from dados_pessoais dp , dados_funcionario df where dp.id_funcionario = df.id and df.matricula_funcionario = :matricula;"
        ];
                
        $statement1 = [
            [
                'matricula' => $input['matricula']
            ],
        ];

        $result = $this->selecionar($statement,$statement1);
        if(is_bool($result)){
            return false;
        }

                
        $statement = [
            "UPDATE dados_funcionario 
            SET data_alteracao = CURDATE()
            WHERE id=:id_funcionario;",
            "UPDATE dados_pessoais
            SET cpf = :cpf, rg = :rg
            WHERE id_funcionario = :id_funcionario;"
        ];
        
        $statement1 = [
            [
                'id_funcionario' => $result[0]['id']
            ],
            [
                'id_funcionario' => $result[0]['id'],
                'cpf' => $input['cpf'] ?? null,
                'rg' => $input['rg'] ?? null
            ]
        ];

        return $this->inserir($statement,$statement1);
    }

    public function updateCorporativo(Array $input)
    {
        $statement = [
            "SELECT df.id 
            FROM dados_corporativos dc , dados_funcionario df 
            WHERE dc.id_funcionario = df.id 
                AND df.matricula_funcionario = :matricula;"
        ];
                
        $statement1 = [
            [
                'matricula' => $input['matricula']
            ],
        ];

        $result = $this->selecionar($statement,$statement1);
        if(is_bool($result)){
            return false;
        }
        
                
        $statement = [
            "UPDATE dados_funcionario 
            SET data_alteracao = CURDATE()
            WHERE id=:id_funcionario;",
            "UPDATE dados_corporativos
            SET chave_c = :chave_c, cartao_bb = :cartao_bb, cartao_capital = :cartao_capital,
                cartao_bbts = :cartao_bbts, num_contrato = :num_contrato, data_contratacao = :data_contratacao
            WHERE id_funcionario = :id_funcionario;"
        ];
        
        $statement1 = [
            [
                'id_funcionario' => $result[0]['id']
            ],
            [
                'id_funcionario' => $result[0]['id'],
                'chave_c' => $input['chave_c'] ?? null,
                'cartao_bb' => $input['cartao_bb'] ?? null,
                'cartao_capital' => $input['cartao_capital'] ?? null,
                'cartao_bbts' => $input['cartao_bbts'] ?? null,
                'num_contrato' => $input['num_contrato'] ?? null,
                'data_contratacao' => $input['data_contratacao'] ?? null
            ]
        ];

        return $this->inserir($statement,$statement1);
    }

    // public function delete($id)
    // {
    //     $statement = "
    //         DELETE FROM person
    //         WHERE id = :id;
    //     ";

    //     try {
    //         $statement = $this->db->prepare($statement);
    //         $statement->execute(array('id' => $id));
    //         return $statement->rowCount();
    //     } catch (\PDOException $e) {
    //         exit($e->getMessage());
    //     }    
    // }
}