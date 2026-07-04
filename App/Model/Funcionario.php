<?php

namespace App\Model;

use Livro\Database\Record;

class Funcionario extends Record
{
    const TABLENAME = 'funcionarios';

    public ?int $id = null;
    public ?string $nome = null;
    public ?string $endereco = null;
    public ?string $email = null;
    public mixed $departamento = null;
    public mixed $idiomas = null;
    public ?string $contratacao = null;
}
