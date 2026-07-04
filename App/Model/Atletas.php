<?php

use Livro\Database\Record;

class Atletas extends Record
{
    const TABLENAME = 'atletas';

    /**
     * Retorna a cidade.
     * Executado sempre se for acessada a propriedade "->cidade"
     */
    public function get_cidade()
    {
        if (empty($this->cidade))
            $this->cidade = new Cidade($this->id_cidade);
        
        return $this->cidade;
    }
}
