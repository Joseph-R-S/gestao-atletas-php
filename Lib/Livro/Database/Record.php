<?php

namespace Livro\Database;

use Exception;


abstract class Record
{
    protected array $data = [];
    const TABLENAME = '';

    public function __construct(?int $id = null)
    {
        if ($id) {
            $object = $this->load($id);
            if ($object) {
                $this->fromArray($object->toArray());
            }
        }
    }

    public function __set(string $prop, mixed $value): void
    {
        if ($value === "" or $value === NULL) {
            unset($this->data[$prop]);
        } else {
            $this->data[$prop] = $value;
        }
    }

    public function  __get(string $prop)
    {
        if (array_key_exists($prop, $this->data)) {
            return $this->data[$prop];
        }

        if (method_exists($this, 'get_' . $prop)) {
            $metodo = 'get_' . $prop;
            return $this->$metodo(); // Executa o método de relacionamento automaticamente
        }

        return null;
    }

    public function isset(string $prop): string
    {
        return isset($this->data[$prop]);
    }

    public function __clone()
    {
        unset($this->data['id']);
    }

    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function getEntity(): string
    {
        $class = get_class($this);
        return constant("{$class}::TABLENAME");
    }

    public function load(int $id): ?object
    {
        $object = null;
        $sql = "SELECT * FROM {$this->getEntity()} WHERE id=" . (int) $id;

        if ($conn = Transaction::get()) {
            Transaction::log($sql);
            $result = $conn->query($sql);
            if ($result) {
                // fetchObject retorna o objeto ou false se não achar nada
                $object = $result->fetchObject(get_class($this)) ?: null;
            }
        } else {
            throw new Exception('Não ha transação ativa');
        }
        return $object;
    }

    public function store()
    {
        $prepared = $this->prepare($this->data);

        // Correçoes para o php 8
        // Capturamos el ID de forma segura desde la propiedad o el array interno
        $id = isset($this->id) ? $this->id : ($this->data['id'] ?? null);

        if (empty($id) or (!$this->load($id))) {

            if (empty($id)) {
                $new_id = $this->getLast() + 1;
                $this->id = $new_id;          // Guardamos en la propiedad pública
                $this->data['id'] = $new_id;   // Guardamos en el array por compatibilidad
                $prepared['id'] = $new_id;
            }

            $sql = "INSERT INTO {$this->getEntity()}" .
                '(' . implode(', ', array_keys($prepared)) . ')' .
                ' values' .
                '(' . implode(', ', array_values($prepared)) . ')';
        } else {

            $sql = "UPDATE {$this->getEntity()}";
            $set = [];
            foreach ($prepared as $column => $value) {
                $set[] = "$column = $value";
            }

            $sql .= " SET " . implode(', ', $set);
            $sql .= " WHERE id=" . (int) $id;
        }

        if ($conn = Transaction::get()) {
            Transaction::log($sql);
            return $conn->exec($sql);
        } else {
            throw new Exception('Não há transação ativa');
        }
    }

    public function delete(?int $id = null)
    {
        // Correção PARA PHP 8: 
        // 1. Si pasan el $id por parámetro, usamos ese.
        // 2. Si no, miramos si el objeto tiene la propiedad pública u objeto $this->id.
        // 3. Como última opción, buscamos en el array interno $this->data['id'].
        if (!$id) {
            $id = isset($this->id) ? $this->id : ($this->data['id'] ?? null);
        }

        // Si aún así no hay un ID válido, lanzamos una excepción para no ejecutar un DELETE huérfano
        if (!$id) {
            throw new Exception("Não é possível deletar: ID não definido.");
        }

        $sql = "DELETE FROM {$this->getEntity()} WHERE id=" . (int) $id;

        if ($conn = Transaction::get()) {
            Transaction::log($sql);
            return $conn->exec($sql);
        } else {
            throw new Exception('Não há transação ativa');
        }
    }

    public function getLast()
    {
        if ($conn = Transaction::get()) {
            $sql = "SELECT max(id) FROM {$this->getEntity()}";
            Transaction::log($sql);
            $result = $conn->query($sql);
            $row = $result->fetch();
            return $row[0];
        } else {
            throw new Exception('Não ha transação ativa');
        }
    }

    public static function find(int $id): object
    {
        $class_name = get_called_class();
        $ar = new $class_name;
        return $ar->load($id);
    }

    /**
     * Retorna todos objetos
     */
    public static function all()
    {
        $classname = get_called_class();
        $rep = new Repository($classname);
        return $rep->load(new Criteria);
    }

    public function prepare(array $data)
    {
        $prepared = array();
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
        } else if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        } else if ($value !== '') {
            return $value;
        } else {
            return "NULL";
        }
    }
}
