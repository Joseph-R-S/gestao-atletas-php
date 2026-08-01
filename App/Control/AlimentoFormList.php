<?php

use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Control\TAction;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Database\Transaction;
use Livro\Traits\DeleteTrait;
use Livro\Traits\ReloadTrait;
use Livro\Widgets\Container\Panel;
use Livro\Widgets\Container\TVBox;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Datagrid\PageNavigation;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\DatagridWrapper;
use Livro\Widgets\Wrapper\FormWrapper;

class AlimentoFormList extends Page
{
    private FormWrapper $form;     // formulário de buscas
    private DatagridWrapper $datagrid; // listagem
    private $loaded;
    protected string $connection;
    protected string $activeRecord;

    use DeleteTrait;
    use ReloadTrait {
        onReload as onReloadTrait;
    }
    public function __construct()
    {
        parent::__construct();
        $this->activeRecord = 'Alimentos';
        $this->connection   = 'livro';
        $this->form = new FormWrapper(new Form('form_busca_alimentos'));

        $nome = new Entry('nome');
        $this->form->addField('Nome', $nome, '100%');
        $this->form->addAction('Buscar', new Action(array($this, 'onReload')));

        $this->datagrid = new DatagridWrapper(new Datagrid);

        // instancia as colunas da Datagrid
        $id   = new DatagridColumn('id',         'Código', 'center', '10%');
        $nome     = new DatagridColumn('nome',       'Nome',    'left', '20%');
        $calorias = new DatagridColumn('calorias',   'Calorias', 'left', '15%');
        $proteinas   = new DatagridColumn('proteinas', 'Poteinas', 'left', '15%');
        $gorduras   = new DatagridColumn('gorduras', 'Gorduras', 'left', '15%');
        $carbohidratos = new DatagridColumn('carbohidratos', 'Carb', 'left', '15%');

        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($calorias);
        $this->datagrid->addColumn($proteinas);
        $this->datagrid->addColumn($gorduras);
        $this->datagrid->addColumn($carbohidratos);
        $this->datagrid->addAction('Editar', new Action([new AlimentosForm, 'onEdit']), 'id', 'fa fa-edit fa-lg blue');
        $this->datagrid->addAction('Excluir',  new Action([$this, 'onDelete']),  'id', 'fa fa-trash fa-lg text-danger');

        $this->pageNavigation = new PageNavigation;
        $this->pageNavigation->setAction(new Action([$this, 'onReload']));
        $this->pageNavigation->enableCounters();
        
        $box = new TVBox;
        $box->add($this->form);
        $panel = new Panel();
        $panel->add($this->form);
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        parent::add($panel);
    }

public function onReload(array $param = [])
{
    try {
        Transaction::open($this->connection);
        $repository = new Repository($this->activeRecord);
        $criteria = new Criteria;

        // 1. Obtener los datos del formulario de búsqueda
        $dados = $this->form->getData();
        if (isset($dados->nome) && !empty($dados->nome)) {
            $criteria->add('nome', 'like', "%{$dados->nome}%");
        }

        // 2. Concatena los filtros antes de contar
        $criteriaCount = clone $criteria;
        $count = $repository->count($criteriaCount);

        // 3. FIX: Captura la página desde $param o desde $_GET, garantizando entero >= 1
        $currentPage = 1;
        if (isset($param['page']) && !empty($param['page'])) {
            $currentPage = (int) $param['page'];
        } elseif (isset($_GET['page']) && !empty($_GET['page'])) {
            $currentPage = (int) $_GET['page'];
        }

        $limit  = 10;
        $offset = ($currentPage - 1) * $limit;

        $criteria->setProperty('order', 'id');
        $criteria->setProperty('limit', $limit);
        $criteria->setProperty('offset', $offset);

        $alimentos = $repository->load($criteria);
        
        $this->datagrid->clear();

        if ($alimentos) {
            foreach ($alimentos as $alimento) {
                $this->datagrid->addItem($alimento);
            }
        }

        // 4. Enviar los valores correctos a la paginación
        $this->pageNavigation->setCount($count);
        $this->pageNavigation->setProperty('limit', $limit);
        $this->pageNavigation->setPage($currentPage); 
        
        $this->form->setData($dados);
        $this->loaded = true;
        
        Transaction::close();
    } catch (\Exception $e) {
        Transaction::rollback();
        echo $e->getMessage();
    }
}

    public function show()
    {
        // se a listagem ainda não foi carregada
        if (!$this->loaded) {
            $this->onReload();
        }
        parent::show();
    }
}
