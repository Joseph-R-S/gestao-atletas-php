<?php
use Livro\Control\TAction;
use Livro\Control\Page;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Database\Transaction;
use Livro\Traits\EditTrait;
use Livro\Widgets\Container\TVBox;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\DatagridWrapper;
use Livro\Widgets\Wrapper\FormWrapper;

class AtletaList extends Page{
    private FormWrapper $form;
    private DatagridWrapper $datagrid;
    private $loaded;
    private $connection;
    private $activeRecord;

    public function __construct()
    {
        parent::__construct();

        $this->form = new FormWrapper(new Form('form_busca_atleta'));
        $this->form->setTitle('Atletas');

        $nome = new Entry('nome');
        $this->form->addField('Nome', $nome);
        $this->form->addAction('Buscar', new TAction(array($this, 'onReload')));
        $this->form->addAction('Novo', new TAction(array(new AtletaForm, 'onEdit')));

        $this->datagrid = new DatagridWrapper(new Datagrid);

        $id   = new DatagridColumn('id', 'ID', 'center', '10%');
        $nome   = new DatagridColumn('nome', 'Nome', 'left', '40%');
        $cell_phone   = new DatagridColumn('telefone', 'Telefone', 'left', '20%');
        $email   = new DatagridColumn('email', 'email', 'left', '30%');

        // adiciona as colunas à Datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($cell_phone);
        $this->datagrid->addColumn($email);


        $this->datagrid->addAction( 'Editar Cadastro', new TAction([new AtletaForm, 'onEdit']), 'id', 'fa fa-edit fa-lg blue');
        $this->datagrid->addAction( 'Objetivo', new TAction([new PerfilAtleta, 'onEdit']), 'id', 'fa fa-line-chart fa-lg');
        $this->datagrid->addAction( 'Medidas', new TAction([new MedidasCorporaisFomList, 'onEdit']), 'id', 'fa fa-eye fa-lg blue');
       
        
        $box = new TVBox;
        $box->style = 'width: 100%; display: block;';
        $box->add($this->form);
        $box->add($this->datagrid);
        
        parent::add($box);
    }

    public function  onReload() {
        Transaction::open('livro');
        $repository = new Repository('Atletas');
        $criteria = new Criteria;
        $criteria->setProperty('order', 'id');
        $dados = $this->form->getData();
        if($dados->nome){
            $criteria->add('nome', 'like', "%{$dados->nome}%");
        }
        $atletas = $repository->load($criteria);
        $this->datagrid->clear();

        if($atletas){
            foreach($atletas as $atleta){
                $this->datagrid->addItem($atleta);
            }
        }
        $this->loaded = true;
        Transaction::close();
    }

    
    /**
     * Exibe a página
     */
    public function show()
    {
         // se a listagem ainda não foi carregada
         if (!$this->loaded)
         {
	        $this->onReload();
         }
         parent::show();
    }
}
?>