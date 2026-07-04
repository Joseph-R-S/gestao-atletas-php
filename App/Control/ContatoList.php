<?php

use Livro\Control\Action;
use Livro\Control\Page;

use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Wrapper\DatagridWrapper;
use Livro\Widgets\Dialog\MessageSweetAlert;

class ContatoList extends Page
{
    private DatagridWrapper $datagrid;
    public function __construct()
    {
        parent::__construct();
        //DatagridWrapper tem a logica de exibição
        $this->datagrid = new DatagridWrapper(new Datagrid);

        $codigo = new DatagridColumn('id','Código','center', '10%');
        $nome = new DatagridColumn('nome','nome','left', '20%');
        $email = new DatagridColumn('email','Email','left', '30%');
        $assunto = new DatagridColumn('assunto','Assunto','left', '30%');

        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($email);
        $this->datagrid->addColumn($assunto);

        $nome->setTransformer( function($value) {
            return strtoupper($value);
        });
        
        $this->datagrid->addAction('visualizar', new Action([$this, 'onVizualizar']), 'nome');
        parent::add($this->datagrid);

    }

    public function onReload(){
        $this->datagrid->clear();
        $m1 = new stdClass;
        $m1->id = 1;
        $m1->nome = 'Ana Bepdatleth';
        $m1->email = 'ana@email';
        $m1->assunto = 'cobrança 1';

        $m2 = new stdClass;
        $m2->id = 2;
        $m2->nome = 'Lolo';
        $m2->email = 'lolo@email';
        $m2->assunto = 'duvida';
        $this->datagrid->addItem($m1);
        $this->datagrid->addItem($m2);
    }

    public function show() : void
    {
        $this->onReload();
        parent::show();
    }

    public function  onVizualizar(array $param) : void {
        new MessageSweetAlert('info', "Voce clicou no registro {$param['nome']}");
    }
}