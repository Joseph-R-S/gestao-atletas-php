<?php

use App\Model\Funcionario;
use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Datagrid\DatagridWrapper;
use Livro\Traits\DeleteAndReloadTrait; // 💡 Importamos tu nuevo Trait


class FuncionarioList extends Page
{
    private DatagridWrapper $datagrid;
    
    // 💡 Propiedades obligatorias que usará el Trait de forma interna
    protected string $activeRecord;
    protected string $connection;
    protected bool $loaded = false;

    // 💡 Inyectamos la lógica genérica de carga y borrado
    use DeleteAndReloadTrait;

    public function __construct()
    {
        parent::__construct();
        
        // 💡 CONFIGURACIÓN CLAVE: El Trait leerá estas dos líneas para saber qué hacer
        $this->activeRecord = Funcionario::class; // Retorna 'App\Model\Funcionario'
        $this->connection   = 'livro';

        $this->datagrid = new DatagridWrapper(new Datagrid);

        $codigo   = new DatagridColumn('id', 'Código', 'center', '10%');
        $nome     = new DatagridColumn('nome', 'Nome', 'left', '20%');
        $endereco = new DatagridColumn('endereco', 'Endereço', 'left', '30%');
        $email    = new DatagridColumn('email', 'Email', 'left', '30%');

        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($endereco);
        $this->datagrid->addColumn($email);

        $nome->setTransformer(function ($value) {
            return strtoupper($value);
        });

        // Redirige al formulario externo
        $this->datagrid->addAction('Editar', new Action([new FuncionarioForm, 'onEdit']), 'id');

        // Llama a onDelete que ahora vive de forma genérica en el Trait
        $this->datagrid->addAction('Deletar', new Action([$this, 'onDelete']), 'id');

        parent::add($this->datagrid);
    }
}
