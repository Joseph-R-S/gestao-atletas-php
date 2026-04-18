<?php

namespace App\Models;

class Avaliacao extends Record
{
    const TABLENAME = 'avaliacoes';
    
    /*
    public static function save(array $dados)
    {
        try {
            $conn = Transaction::get();

            $sql = "INSERT INTO avaliacoes (atleta_id, objetivo_id, nivel_id, tipo_esporte_id, 
                    foto_frente_path, foto_costa_path, foto_lado_path, meta_especifica, 
                    peso, altura, intensidade, dias_treino_semana) 
                    VALUES 
                    (:atleta_id, :objetivo_id, :nivel_id, :tipo_esporte_id, 
                    :foto_frente_path, :foto_costa_path, :foto_lado_path, :meta_especifica, 
                    :peso, :altura, :intensidade, :dias_treino_semana)";
            $stmt = $conn->prepare($sql);

            $params = [
                ':atleta_id' => $dados['atleta_id'],
                ':objetivo_id' => $dados['objetivo_id'] ?? null,
                ':nivel_id' => $dados['nivel_id'] ?? null,
                ':tipo_esporte_id' => $dados['tipo_esporte_id'] ?? null,
                ':foto_frente_path' => $dados['foto_frente_path'] ?? null,
                ':foto_costa_path' => $dados['foto_costa_path'] ?? null,
                ':foto_lado_path' => $dados['foto_lado_path'] ?? null,
                ':meta_especifica' => $dados['meta_especifica'] ?? null,
                ':peso' => $dados['peso'] ?? 0,
                ':altura' => $dados['altura'] ?? 0,
                ':intensidade' => $dados['intensidade'] ?? null,
                ':dias_treino_semana' => $dados['dias_treino_semana'] ?? 0,
            ];

            Transaction::logs($sql, $params);
            $result = $stmt->execute($params);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao salvar: " .   $e->getMessage());
        }
    }

    public static function find(int $id)
    {
        try {
            $conn = Transaction::get();
            $result = $conn->prepare("SELECT * FROM avaliacoes WHERE id=:id");
            $result->bindParam(':id', $id, PDO::PARAM_INT);
            $result->execute();
            return $result->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException('Erro ao buscar ' . $e->getMessage());
        }
    }

    public static function delete(int $id)
    {
        try {
            $conn = Transaction::get();
            $sql = "DELETE FROM avaliacoes WHERE id = :id";
            $params = [':id' => $id];
            $stmt = $conn->prepare($sql);
            Transaction::logs($sql, $params);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new PDOException('Erro ao deletar ' . $e->getMessage());
        }
    }

    public static function all()
    {
        try {
            $conn = Transaction::get();
            $result = $conn->query("SELECT * FROM avaliacoes ORDER BY id");
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao buscar: " .   $e->getMessage());
        }
    }*/
}
