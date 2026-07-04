<?php

use Livro\Control\Page;
use Livro\Control\Action;
use Livro\Database\Transaction;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Text;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Form\CheckGroup;
use Livro\Widgets\Form\RadioGroup;
use Livro\Widgets\Wrapper\FormWrapper;
use App\Model\Funcionario;
use Livro\Log\LoggerTXT;
use Livro\Widgets\Dialog\MessageSweetAlert;

use Livro\Traits\DeleteTrait;
use Livro\Traits\ReloadTrait;
use Livro\Traits\SaveTrait;
use Livro\Traits\EditTrait;

class FuncionarioForm extends Page
{
    private FormWrapper $form;

    use DeleteTrait;
    use ReloadTrait {
        onReload as onReloadTrait;
    }
    use SaveTrait {
        onSave as onSaveTrait;
    }

    private $connection;
    private $activeRecord;

    public function __construct()
    {
        parent::__construct();

        $this->connection   = 'livro';
        $this->activeRecord = Funcionario::class;

        $this->form = new FormWrapper(new Form('form_funcionario'));
        $this->form->setTitle('Cadastro de funcionarios');

        $id = new Entry('id');
        $nome = new Entry('nome');
        $endereco = new Entry('endereco');
        $email = new Entry('email');
        $departamento = new Combo('departamento');
        $idiomas = new CheckGroup('idiomas');
        $contratacao = new RadioGroup('contratacao');

        $id->setEditable(false);
        $this->form->addField('Codigo', $id);
        $this->form->addField('Nome', $nome);
        $this->form->addField('Endereço', $endereco);
        $this->form->addField('Email', $email);
        $this->form->addField('Departamento', $departamento);
        $this->form->addField('Idiomas', $idiomas);
        $this->form->addField('Contratação', $contratacao);

        $departamento->addItems([
            '1' => 'RH',
            '2' => 'Atendimento',
            '3' => 'Engenharia',
            '4' => 'Produção'
        ]);

        $idiomas->addItems([
            '1' => 'Portugues',
            '2' => 'Inglês',
            '3' => 'Espanhol',
            '4' => 'Frances'
        ]);

        $contratacao->addItems([
            '1' => 'Estagiario',
            '2' => 'PJ',
            '3' => 'CLT',
            '4' => 'Sócio'
        ]);

        $this->form->addAction('Salvar', new Action([$this, 'onSave']));
        $this->form->addAction('Limpar', new Action([$this, 'onClear']));

        parent::add($this->form);
    }

    public function onSave()
    {
        $this->onSaveTrait();
        $this->onReload();
    }

    public function onEdit(array $param)
    {
        try {
            $id = isset($param['id']) ? $param['id'] : null;
            if (empty($id)) {
                return;
            }
            Transaction::open('livro');
            Transaction::setLogger(new LoggerTXT('funcionario.txt'));
            $funcionario = Funcionario::find($id);
            if ($funcionario) {
                if (isset($funcionario->idiomas) && is_string($funcionario->idiomas)) {
                    $funcionario->idiomas = explode(',', $funcionario->idiomas);
                }
                $this->form->setData($funcionario);
            } else {
                new MessageSweetAlert('error', 'Funcionário não encontrado.');
            }
            Transaction::close();
        } catch (Exception $e) {
            new MessageSweetAlert('error', $e->getMessage());
            Transaction::rollback();
        }
    }

    public function onClear() {
        $this->form->clear();
    }
}
