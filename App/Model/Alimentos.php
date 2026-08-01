<?php

use Livro\Database\Record;

class Alimentos extends Record
{
    const TABLENAME = 'alimentos';

    const PORCAO = [
        'g' => 'gramas',
        'ml' => 'minilitros',
        'unid' => 'unidade'
    ];

    const TIPO = ['p' => 'Proteina', 'c' => 'Carbohidrato', 'g' => 'Gordura', 'pv' => 'Proteina Vegetal', 'o' => 'Otro'];

    const REFEICOES = [
        '0' => 'Café da manhã',
        '1' => 'Lanche da manhã',
        '2' => 'Almoço',
        '3' => 'Lanche da tarde',
        '4' => 'Janta',
        '5' => 'Ceia',
    ];
}
