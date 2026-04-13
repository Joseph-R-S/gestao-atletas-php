<?php

namespace App\Models;

use App\Database\Connection;
use PDOException;

class Avaliacao
{
    public static function save(array $dados)
    {
        try {
            $conn = Connection::open('db');

            $sql = "INSERT INTO avaliacoes (atleta_id, objetivo_id, nivel_id, tipo_esporte_id, 
                    foto_frente_path, foto_costa_path, foto_lado_path, meta_especifica, 
                    peso, altura, intensidade, dias_treino_semana) 
                    VALUES 
                    (:atleta_id, :objetivo_id, :nivel_id, :tipo_esporte_id, 
                    :foto_frente_path, :foto_costa_path, :foto_lado_path, :meta_especifica, 
                    :peso, :altura, :intensidade, :dias_treino_semana)";
            $result = $conn->prepare($sql);

            $result->execute([
                ':atleta_id' => $dados['atleta_id'],
                ':objetivo_id' => $dados['objetivo_id'] ?? null,
                ':nivel_id' => $dados['nivel_id'] ?? null,
                ':tipo_esporte_id' => $dados['tipo_esporte_id'] ?? null,
                ':foto_frente_path' => $dados['foto_frente_path'] ?? null,
                ':foto_costa_path' => $dados['foto_costa_path'] ?? null,
                ':foto_lado_path' => $dados['foto_lado_path'] ?? null,
                ':meta_especifica' => $dados['meta_especifica'],
                ':peso' => $dados['peso'] ?? 0,
                ':altura' => $dados['altura'] ?? 0,
                ':intensidade' => $dados['intensidade'] ?? null,
                ':dias_treino_semana' => $dados['dias_treino_semana'] ?? 0,
            ]);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao salvar: " .   $e->getMessage());
        }
    }
}
