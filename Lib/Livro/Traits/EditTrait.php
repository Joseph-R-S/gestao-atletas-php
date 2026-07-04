<?php

namespace Livro\Traits;

use Livro\Database\Transaction;
use Livro\Widgets\Dialog\Message;
use Exception;

trait EditTrait
{
    function onEdit($param)
    {
        try {
            if (isset($param['id'])) {
                $id = $param['id'];

                // Si la propiedad llega vacía por el ciclo de vida, le ponemos 'livro' por defecto
                $conn = !empty($this->connection) ? $this->connection : 'livro';
                Transaction::open($conn);

                $class = $this->activeRecord;

                // Si usas namespaces en el futuro o namespacing dinámico:
                // Si $class no tiene barra '\' y existe App\Model, lo busca ahí
                if (!class_exists($class) && class_exists("App\\Model\\" . $class)) {
                    $class = "App\\Model\\" . $class;
                }

                $object = $class::find($id);


                if ($object) {
                    $stdObject = new \stdClass;

                    // Pegamos o array de dados puros do Active Record de forma genérica
                    // A maioria das classes Record possui o método toArray() ou getData()
                    $dadosPuros = method_exists($object, 'toArray') ? $object->toArray() : (method_exists($object, 'getData') ? $object->getData() : get_object_vars($object));

                    // O foreach genérico que você sugeriu:
                    foreach ($dadosPuros as $campo => $valor) {
                        // O $object->$campo vai disparar o __get() magicamente e pegar o valor correto
                        $stdObject->$campo = $object->$campo;
                    }
                    $this->form->setData($stdObject);
                } else {
                    // Usar la alerta que tu proyecto sí reconoce
                    if (class_exists('Message')) {
                        new Message('error', 'Registro não encontrado.');
                    }
                }

                Transaction::close();
            }
        } catch (Exception $e) {
            // Evita que el catch muera en silencio si 'Message' no existe
            if (class_exists('Message')) {
                new Message('error', $e->getMessage());
            } else {
                echo "Erro no EditTrait: " . $e->getMessage();
            }
            Transaction::rollback();
        }
    }
}
