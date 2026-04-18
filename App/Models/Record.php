<?php

namespace App\Models;

use App\Database\Transaction;

use PDO;
use PDOException;

abstract class Record
{
    const TABLENAME = '';
    protected array $data;

    public function __construct(?int $id = null)
    {
        if ($id) {
            $object = $this->load($id);
            if ($object) {
                $this->fromArray($object->toArray());
            }
        }
    }
    public function __set(string $prop, mixed $value)
    {
        if ($value === NULL) {
            unset($this->data[$prop]);
        } else {
            $this->data[$prop] = $value;
        }
    }

    public function __get(string $prop)
    {
        if (isset($this->data[$prop])) {
            return $this->data[$prop];
        }
    }

    public function __isset(string $prop)
    {
        return isset($this->data[$prop]);
    }

    public function __clone()
    {
        unset($this->data['id']);
    }

    public function fromArray(array $data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return $this->data;
    }

    //find virou load na clase record
    public function load(int $id)
    {
        try {
            $conn = Transaction::get();
            $sql = "SELECT * FROM {$this->getEntity()} WHERE id = :id";
            $result = $conn->prepare($sql);
            $result->execute([':id' => $id]);

            $dados = $result->fetch(PDO::FETCH_ASSOC);
            if ($dados) {
                $this->fromArray($dados);
            }
            return $dados;
        } catch (PDOException $e) {
            throw new PDOException('Erro ao buscar: ' . $e->getMessage());
        }
    }

    //o metodo delete já esta funcionando
    public function delete(int $id)
    {
        try {
            $conn = Transaction::get();
            $sql = "DELETE FROM {$this->getEntity()} WHERE id = :id";
            $stmt = $conn->prepare($sql);
            Transaction::logs($sql);
            $result = $stmt->execute([':id' => $id]);
            return $result;
        } catch (PDOException $e) {
            throw new PDOException('Erro ao deletar ' . $e->getMessage());
        }
    }

    //
    public function store()
    {
        try {
            $conn = Transaction::get();
            $params = [];

            if (empty($this->data['id'])) {
                unset($this->data['id']);
            }
            
            if (empty($this->data['id'])) {
                // INSERT - Dinâmico usando placeholders
                $columns = implode(', ', array_keys($this->data));
                $placeholders = ':' . implode(', :', array_keys($this->data));
                $sql = "INSERT INTO {$this->getEntity()} ({$columns}) VALUES ({$placeholders})";

                foreach ($this->data as $key => $value) {
                    $params[":$key"] = $value;
                }
            } else {
                // UPDATE - Dinâmico
                $set = [];
                foreach ($this->data as $column => $value) {
                    if ($column !== 'id') {
                        $set[] = "$column = :$column";
                    }
                    $params[":$column"] = $value;
                }
                $sql = "UPDATE {$this->getEntity()} SET " . implode(', ', $set) . " WHERE id = :id";
            }

            $stmt = $conn->prepare($sql);
            Transaction::logs($sql, $params);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao salvar: " . $e->getMessage());
        }
    }

    //so coloquei getEntity
    public function ally(mixed $filter = '')
    {
        try {
            $conn = Transaction::get();
            $sql = "SELECT * FROM {$this->getEntity()}";
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

    public static function all(mixed $filter = '')
    {
        try {
            $conn = Transaction::get();
            // Aqui usamos 'static' para pegar a TABLENAME da classe que chamou (Atleta ou Avaliacao)
            $table = static::TABLENAME;
            $sql = "SELECT * FROM {$table}";
            $params = [];

            if ($filter !== "") {
                if (is_numeric($filter)) {
                    $sql .= " WHERE id = :filter";
                    $params[':filter'] = (int)$filter;
                } else {
                    $sql .= " WHERE nome_completo LIKE :filter";
                    $params[':filter'] = "%{$filter}%";
                }
            }

            $sql .= " ORDER BY id";
            $stmt = $conn->prepare($sql);
            Transaction::logs($sql, $params);
            $stmt->execute($params);

            // A MÁGICA: fetchAll com PDO::FETCH_CLASS
            // Isso retorna um array de Objetos Atleta já preenchidos!
            return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
        } catch (PDOException $e) {
            throw new PDOException('Erro ao buscar lista: ' . $e->getMessage());
        }
    }

    public function getEntity()
    {
        //para obter a classe filha
        $class = get_class($this);

        return constant("{$class}::TABLENAME");
    }

    public function prepare(array $data)
    {
        $prepared = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $prepared[$key] = $this->escape($value);
            }
        }
        return $prepared;
    }

    public function escape(mixed $value)
    {
        if (is_string($value) and (!empty($value))) {
            $value = addslashes($value);
            return "'$value'";
        } elseif (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        } elseif ($value == '') {
            return $value;
        } else {
            return "NULL";
        }
    }
}
