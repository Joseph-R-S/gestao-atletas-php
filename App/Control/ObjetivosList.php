<?php

use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Database\Transaction;
use Livro\Traits\DeleteTrait;
use Livro\Traits\ReloadTrait;
use Livro\Widgets\Container\TVbox;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\DatagridWrapper;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Wrapper\FormWrapper;

/*
* Página dos objetivos
*/
class ObjetivosList extends Page{
    private FormWrapper $form;
    private DatagridWrapper $datagrid;
    private string $connection;
    private string $activeRecord;
    private array $filters = [];
    private $loaded;

    use DeleteTrait;
    use ReloadTrait{
        ReloadTrait::onReload as onReloadTrait;
    }

    public function __construct()
    {
        parent::__construct();

        $this->activeRecord = 'Objetivos';
        $this->connection = 'livro';

        // instancia um formulário
        $this->form = new FormWrapper(new Form('form_busca_objetivos'));
        $this->form->setTitle('Objetivos');

        $descricao = new Entry('descricao');

        $this->form->addField('Descrição',   $descricao, '100%');
        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));
        $this->form->addAction('Cadastrar', new Action(array(new ObjetivosForm, 'onEdit')));

        // instancia objeto Datagrid
        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $id   = new DatagridColumn('id',             'Código',    'center',  '10%');
        $descricao = new DatagridColumn('descricao',      'Descrição', 'left',   '80%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($descricao);

        $this->datagrid->addAction('Editar',  new Action([new ObjetivosForm, 'onEdit']), 'id', 'fa fa-edit fa-lg blue');

        $box = new TVbox;
        $box->style = 'width: 100%; display: block;';
        $box->add($this->form);
        $box->add($this->datagrid);
        parent::add($box);
    }

    
    public function onReload()
    {
       $dados = $this->form->getData();
       
        // verifica se o usuário preencheu o formulário
        if ($dados->descricao) {
            // filtra pela descrição do produto
            $this->filters[] = ['descricao', 'like', "%{$dados->descricao}%", 'and'];
        }

        $this->onReloadTrait();

        try {
            Transaction::open('livro');

            $items = $this->datagrid->getItems();
            if($items){
                foreach($items as $objetivo){
                    $objetivo->descricao;
                }
            }
            Transaction::close();
        } catch (Exception $e) {
            Transaction::rollback();
            new Message('error', $e->getMessage());
        }

        $this->loaded = true;
    }

    /**
     * Exibe a página
     */
    public function show()
    {
        // se a listagem ainda não foi carregada
        if (!$this->loaded) {
            $this->onReload();
        }
        parent::show();
    }
}



?>