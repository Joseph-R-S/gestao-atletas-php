<?php

namespace App\Models;

use App\Database\Transaction as Transaction;
use PDO;
use PDOException;

class Atleta
{
    public static function save(array $atleta)
    {
        try {
            $conn = Transaction::get();

            $params = [
                ':nome_completo'    => $atleta['nome_completo'],
                ':email'            => $atleta['email'],
                ':sexo'             => $atleta['sexo'],
                ':telefone'         => $atleta['telefone'],
                ':data_nascimento'  => $atleta['data_nascimento']
            ];

            if (empty($atleta['id'])) {
                $sql = "INSERT INTO atletas (nome_completo, email, sexo, telefone, data_nascimento) 
                VALUES (:nome_completo, :email, :sexo, :telefone, :data_nascimento)";
            } else {
                $sql = "UPDATE atletas SET 
                    nome_completo   = :nome_completo,
                    email           = :email,
                    sexo            = :sexo,
                    telefone        = :telefone,
                    data_nascimento = :data_nascimento
                    WHERE id = :id";

                $params[':id'] = $atleta['id'];
            }
            $stmt = $conn->prepare($sql);
            Transaction::logs($sql, $params);
            $result = $stmt->execute($params);
            return $result;
        } catch (PDOException $e) {
            throw new PDOException("Erro ao salvar: " .   $e->getMessage());
        }
    }

    public static function find(int $id)
    {
        try {
            $conn = Transaction::get();
            $sql = "SELECT * FROM atletas WHERE id=:id";
            $result = $conn->prepare($sql);
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
            $sql = "DELETE FROM atletas WHERE id = :id";
            $stmt = $conn->prepare($sql);
            Transaction::logs($sql);
            $result = $stmt->execute([':id' => $id]);
            return $result;
        } catch (PDOException $e) {
            throw new PDOException('Erro ao deletar ' . $e->getMessage());
        }
    }

    public static function all(mixed $filter = '')
    {
        try {
            $conn = Transaction::get();
            $sql = "SELECT * FROM atletas";
            $params = [];
            if ($filter !== "") {
                if (is_numeric($filter)) {
                    $sql .= " WHERE id = :filter";
                    $params[':filter'] = (int)$filter;
                } else {
                    $sql .= " WHERE nome LIKE :filter";
                    $params[':filter'] = "%{$filter}%";
                }
            }
            $sql .= " ORDER BY id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException('Erro ao buscar ' . $e->getMessage());
        }
    }
}
