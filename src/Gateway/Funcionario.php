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
                chave_c , cartao_bb, cartao_capital, 
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
            SELECT nome, cpf, rg
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
        $statement = [
            "SELECT id_funcionario from dados_corporativos dc , dados_funcionario df where dc.id_funcionario = df.id and df.matricula_funcionario = :matricula;"
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
            "INSERT INTO dados_corporativos (id_funcionario,chave_c,cartao_bb,cartao_capital,cartao_bbts,num_contrato)
            VALUES (LAST_INSERT_ID(),:chave_c,:cartao_bb,:cartao_capital,:cartao_bbts,:num_contrato);"
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
            ],
        ];

        return $this->inserir($statement,$statement1);
    }

    public function insertPessoais(Array $input)
    {
        $statement = [
            "SELECT df.matricula_funcionario from dados_pessoais dp , dados_funcionario df where dp.id_funcionario = df.id and df.matricula_funcionario = :matricula;"
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
            "INSERT INTO dados_pessoais(id_funcionario,nome,cpf,rg)
            VALUES (LAST_INSERT_ID(),:nome,:cpf,:rg);"
        ];
        $statement1 = [
            [
                'matricula' => $input['matricula']
            ],
            [
                'nome' => $input['nome'] ?? null,
                'cpf' => $input['cpf'] ?? null,
                'rg' => $input['rg'] ?? null
            ],
        ];

        return $this->inserir($statement,$statement1);
    }

    // public function update($id, Array $input)
    // {
    //     $statement = "
    //         UPDATE person
    //         SET 
    //             firstname = :firstname,
    //             lastname  = :lastname,
    //             firstparent_id = :firstparent_id,
    //             secondparent_id = :secondparent_id
    //         WHERE id = :id;
    //     ";

    //     try {
    //         $statement = $this->db->prepare($statement);
    //         $statement->execute(array(
    //             'id' => (int) $id,
    //             'firstname' => $input['firstname'],
    //             'lastname'  => $input['lastname'],
    //             'firstparent_id' => $input['firstparent_id'] ?? null,
    //             'secondparent_id' => $input['secondparent_id'] ?? null,
    //         ));
    //         return $statement->rowCount();
    //     } catch (\PDOException $e) {
    //         exit($e->getMessage());
    //     }    
    // }

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