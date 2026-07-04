<?php

use App\Model\Funcionario;
use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Wrapper\DatagridWrapper;
use Livro\Widgets\Dialog\MessageSweetAlert;
use Livro\Widgets\Dialog\Question;
use Livro\Database\Transaction;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Widgets\Container\Vbox;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\FormWrapper;

use Livro\Traits\DeleteTrait;
use Livro\Traits\ReloadTrait;
use Livro\Traits\SaveTrait;
use Livro\Traits\EditTrait;

class FormularioBuscaList extends Page
{
    private DatagridWrapper $datagrid;
    private FormWrapper $form; //formulario

    protected string $activeRecord;
    protected string $connection;
    protected array $filters = []; // Para el trait
    protected bool $loaded = false;

    use EditTrait;
    use DeleteTrait;
    use ReloadTrait {
        onReload as onReloadTrait;
    }
    use SaveTrait {
        onSave as onSaveTrait;
    }

    public function __construct()
    {
        parent::__construct();

        $this->connection   = 'livro';
        $this->activeRecord = Funcionario::class;

        //instacia formulario
        $this->form = new FormWrapper(new Form('form_busca_funvionarios'));
        $this->form->setTitle('Bucar funcionario');
        //campo do formulario
        $nome = new Entry('nome');

        $this->form->addField('Nome', $nome, '300px');

        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));
        $this->form->addAction('Novo', new Action(array(new FuncionarioForm, 'onEdit')));


        //DatagridWrapper tem a logica de exibição
        //tem que colocar ao final assim new DatagridWrapper(new Datagrid)
        $this->datagrid = new DatagridWrapper(new Datagrid);

        $codigo = new DatagridColumn('id', 'Código', 'center', '10%');
        $nome = new DatagridColumn('nome', 'Nome', 'left', '25%');
        $endereco = new DatagridColumn('endereco', 'Endereço', 'left', '30%');
        $email = new DatagridColumn('email', 'Email', 'left', '25%');

        $this->datagrid->addColumn($codigo);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($endereco);
        $this->datagrid->addColumn($email);

        $nome->setTransformer(function ($value) {
            return strtoupper($value);
        });

        //action para poder editar um registro
        $this->datagrid->addAction('Editar', new Action([new FuncionarioForm, 'onEdit']), 'id');

        /*action para poder excluir um registro
        * o metodo onDelete é criado na nesta mesma classe
        */
        $this->datagrid->addAction('Deletar', new Action([$this, 'onDelete']), 'id');
        //instacio uma caixa vertical
        $box = new Vbox;
        $box->style = 'display:block margin: 20px';
        //adiciono o form a caixa e o datagrid
        $box->add($this->form);
        $box->add($this->datagrid);

        //para aparecer na tela
        parent::add($box);
    }

    /**
     * Captura los filtros del formulario y delega la carga al Trait
     */
    public function onReload(): void
    {
        // 1. Limpiamos los filtros anteriores
        $this->filters = [];

        // 2. Obtenemos los datos que el usuario escribió en la pantalla
        $dados = $this->form->getData();

        // 3. Si escribió algo, lo empaquetamos en el formato que el Trait entiende
        if (!empty($dados->nome)) {
            // [campo, operador, valor, tipo (opcional)]
            $this->filters[] = ['nome', 'like', "%{$dados->nome}%"];
        }

        // 4. 🔥 Llamamos a la lógica pesada del Trait. Él abrirá la transacción y cargará todo filtrado
        $this->loaded = true;
        $this->onReloadTrait();
        
    }

    public function show(): void
    {
        // Se a listagem ainda não foi carregada na requisição atual, força o onReload
        if (!$this->loaded) {
            $this->onReload();
        }
        parent::show();
    }
}
