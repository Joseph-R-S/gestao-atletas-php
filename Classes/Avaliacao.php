<?php
require_once 'Conection.php';
require_once 'Atleta.php';
class Avaliacao
{
    public static function save(array $avaliacao)
    {
        try {
            $conn = Connection::open();

            $param = [
                ':atleta_id' => $avaliacao['atleta_id'],
                ':objetivo_id' => $avaliacao['objetivo_id'],
                ':nivel_id' => $avaliacao['nivel_id'],
                ':tipo_esporte_id' => $avaliacao['tipo_esporte_id'],
                ':foto_frente_path' => $avaliacao['foto_frente_path'],
                ':foto_costa_path' => $avaliacao['foto_costa_path'],
                ':foto_lado_path' => $avaliacao['foto_lado_path'],
                ':meta_especifica ' => $avaliacao['meta_especifica '],
                ':meta_especifica ' => $avaliacao['meta_especifica '],
            ];

        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar: " .   $e->getMessage());
        }
    }
}