<?php
require_once 'Connection.php';
class Atleta
{
    public static function save(array $atleta)
    {
        try
        {
            $conn = Connection::open('db');

            $params = [
                ':nome_completo'    => $atleta['nome_completo'],
                ':email'            => $atleta['email'],
                ':sexo'             => $atleta['sexo'],
                ':telefone'         => $atleta['telefone'],
                ':data_nascimento'  => $atleta['data_nascimento']
            ];

            if (empty($atleta['id'])) 
            {
                $sql = "INSERT INTO atletas (nome_completo, email, sexo, telefone, data_nascimento) 
                VALUES (:nome_completo, :email, :sexo, :telefone, :data_nascimento)";
            } else 
            {
                $sql = "UPDATE atletas SET 
                    nome_completo   = :nome_completo,
                    email           = :email,
                    sexo            = :sexo,
                    telefone        = :telefone,
                    data_nascimento = :data_nascimento
                    WHERE id = :id";

                    $params[':id'] = $atleta['id'];
            }
            $result = $conn->prepare($sql);
            $result->execute($params);
        }catch(PDOException $e)
        {
           throw new Exception("Erro ao salvar: " .   $e->getMessage());
        }
    }

    public static function find(int $id)
    {
        try
        {
            $conn = Connection::open('db');
            $result = $conn->prepare("SELECT * FROM atletas WHERE id=:id");
            $result->bindParam(':id', $id, PDO::PARAM_INT); 
            $result->execute();
            return $result->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e)
        {
            throw new Exception('Erro ao buscar ' . $e->getMessage());
   
        }
    }

    public static function delete(int $id)
    {
        try 
        {
            $conn = Connection::open('db');
            $result = $conn->prepare("DELETE FROM atletas WHERE id = :id");
            return $result->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception('Erro ao deletar ' . $e->getMessage());
        } 
    }

    public static function all()
    {
        try 
        {
            $conn = Connection::open('db');
            $result = $conn->query("SELECT * FROM atletas ORDER BY id");
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erro ao buscar ' . $e->getMessage());
        } 
    }
}