<?php

use Livro\Database\Record;
use Livro\Database\Transaction;

class Cidade extends Record
{
    const TABLENAME = 'cidade';
    private Estado $estado;

    /**
     * Retorna o objeto Estado relacionado (Protegido com transação)
     */
    public function get_estado()
    {
        if (empty($this->estado)) {
            $this->estado = new Estado($this->id_estado);

        }

        return $this->estado;
    }

    /**
     * Retorna diretamente o nome do estado
     */
    public function get_nome_estado()
    {
        // ✅ Ahora llamamos a get_estado() para que abra y cierre la transacción correctamente
        $obj_estado = $this->get_estado();

        return $obj_estado ? $obj_estado->nome : '';
    }

    /**
     * MÉTODO MÁGICO INTERCEPTOR
     */
    public function __get(string $propriedade)
    {
        if ($propriedade == 'nome_estado') {
            return $this->get_nome_estado();
        }

        if ($propriedade == 'estado') {
            return $this->get_estado();
        }

        return parent::__get($propriedade);
    }
}