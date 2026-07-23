<?php

use Livro\Database\Record;

class Alimentos extends Record{
    const TABLENAME = 'alimentos';

  const PORCAO = ['g' => 'gramas',
                  'ml' => 'minilitros',
                  'unid' => 'unidade'];

    const TIPO = ['p' => 'Proteina', 'c' => 'Carbohidrato', 'g' => 'Gordura', 'pv' => 'Proteina Vegetal', 'o' => 'Otro'];
}