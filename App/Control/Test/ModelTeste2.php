<?php

use Livro\Control\Page;
use Livro\Database\Transaction;


class ModelTeste2 extends Page
{
    #[Override]
    public function show()
    {
        try {
            Transaction::open('livro');

            
            $p1 =  Pessoa::find(1);
            $grupos = $p1->getGrupos();

            foreach($grupos as $grupo){
                print $grupo->nome . "<br>";
            }
            Transaction::close();
        } catch (Exception $e) {
            echo $e->getMessage();
            Transaction::rollback();
        }
        return parent::show();
    }
}