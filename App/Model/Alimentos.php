<?php

use Livro\Database\Record;

class Alimentos extends Record
{
    const TABLENAME = 'alimentos';

    const PORCAO = [
        'g'    => 'gramas',
        'ml'   => 'mililitros',
        'cdc'  => 'colher de chá',
        'cds'  => 'colher de sopa', // Útil si más adelante agregas colher de sopa
        'unid' => 'unidade',
    ];

    const TIPO = [
        'p' => 'Proteina',
        'c' => 'Carbohidrato',
        'g' => 'Gordura',
        'f' => 'Fruta',
        'v' => 'Vegetal',
        's' => 'Salada',
        'o' => 'Outro'
    ];

    const REFEICOES = [
        '0' => 'Café da manhã',
        '1' => 'Lanche da manhã',
        '2' => 'Almoço',
        '3' => 'Lanche da tarde',
        '4' => 'Janta',
        '5' => 'Ceia',
    ];
}
