<?php
namespace Src\Gateway;

class Evento {
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
            SELECT 
                f.id,f.status,f.numero_abonos,f.adiantamento,
                a.data_cadastro,f.data_inicio, f.data_fim, 
                f.tipo, a.matricula_funcionario, 
                (SELECT 
                    nome 
                    FROM dados_pessoais dp , dados_funcionario df 
                    WHERE df.matricula_funcionario = a.matricula_funcionario 
                        and dp.id_funcionario = df.id) as nome  
            FROM ferias_abonos f, faltas_folgas ff , administrativo a 
            WHERE f.id_faltas_folgas = ff.id 
                and ff.id_administrativo = a.id 
                and YEAR(f.data_inicio)>=YEAR(CURDATE()) 
                and MONTH(f.data_inicio)>=MONTH(CURDATE())
            ORDER BY f.data_inicio;
        ";

        try {
            $statement = $this->db->query($statement);

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function findbyDate($data)
    {
        $statement = "
        SELECT *
        FROM ferias_abonos fa, faltas_folgas ff, administrativo a2 
        WHERE fa.data_inicio = '$data'
       and fa.id_faltas_folgas = ff.id
      and ff.id_administrativo = a2.id ;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if ($result){
                return $result;
            }
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function findMatricula($matricula)
    {
        $statement = "
        SELECT fa.id as id_ferias_abonos , a.id as id_administrativo,
            nome,data_inicio,data_fim,a.data_cadastro, 
            a.data_alteracao,fa.status 
        FROM ferias_abonos fa , faltas_folgas ff, administrativo a, 
            dados_funcionario df, dados_pessoais dp 
        WHERE ff.id_administrativo = a.id 
            and fa.id_faltas_folgas = ff.id 
            and dp.id_funcionario = df.id 
            and df.matricula_funcionario =$matricula
            and a.matricula_funcionario =$matricula
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function insertFeriasAbonos(Array $input)
    {
        $data_inicio = date('Y-m-d 00:00:00',strtotime($input['data_inicio']));
        $data_fim = date('Y-m-d 23:59:59',strtotime($input['data_fim']));

        if ($data_inicio<date() or $data_fim<$data_inicio){
            return false;
        }

        $statement = [
            "INSERT INTO administrativo (matricula_funcionario,data_cadastro,data_alteracao) 
            VALUES (:matricula,CURDATE(),CURDATE());",
            "INSERT INTO faltas_folgas (id_administrativo)
            VALUES (LAST_INSERT_ID());",
            "INSERT INTO ferias_abonos(id_faltas_folgas,numero_abonos,adiantamento,data_inicio,data_fim,status,tipo)
            VALUES (LAST_INSERT_ID(),:numero_abonos,:adiantamento,:data_inicio,:data_fim,'pendente',:tipo);"
        ];
        $statement1 = [
            [
                'matricula' => $input['matricula']
            ],
            [],
            [
                'numero_abonos'  => $input['numero_abonos'],
                'adiantamento' => $input['adiantamento'],
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'tipo' => $input['tipo']
            ],
        ];

        return $this->inserir($statement,$statement1);
    }

    public function insertOutros(Array $input)
    {
        $statement = "
            INSERT INTO administrativo (matricula_funcionario,data_cadastro,data_alteracao)
            VALUES (:matricula,:data_cadastro,:data_alteracao);
            INSERT INTO faltas_folgas (id_administrativo)
            VALUES (LAST_INSERT_ID());
            INSERT INTO outros (id_faltas_folgas,tipo,data_inicio,data_fim)
            VALUES (LAST_INSERT_ID(),:tipo,:data_inicio,:data_fim);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'matricula' => $input['matricula'],
                'data_cadastro' => $input['data_cadastro'],
                'data_alteracao' => $input['data_alteracao'],
                'data_inicio' => $input['data_inicio'] ?? null,
                'data_fim' => $input['data_fim'] ?? null,
                'tipo' => $input['tipo'] ?? null,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($id_adm, Array $input)
    {
        $statement = "
            UPDATE administrativo
            SET data_alteracao = :data_alteracao
            WHERE id = :id_adm;
            UPDATE ferias_abonos 
            SET status = :status
            WHERE id = :id_ferias_abonos
        ";
        
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id_adm' => (int) $id_adm,
                'data_alteracao' => $input['data_alteracao'],
                'status'=>$input['status'],
                'id_ferias_abonos'=>$input['id_ferias_abonos'],
            ));

            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }
}