<?php

use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Traits\EditTrait;
use Livro\Traits\SaveTrait;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\FormWrapper;

class ObjetivosForm extends Page{
    private FormWrapper $form;
    private string $connection;
    private string $activeRecord;

    use SaveTrait;
    use EditTrait;

    public function __construct()
    {
        parent::__construct();

        $this->connection = 'livro';
        $this->activeRecord = 'Objetivos';

        $this->form = new FormWrapper(new Form('form_objetivos'));
        $this->form->setTitle('Objetivos');

        $id      = new Entry('id');
        $descricao = new Entry('descricao');
        $descricao->placeholder = 'exemplo: bulking';

        $id->setEditable(FALSE);
        $this->form->addField('Código',    $id, '30%');
        $this->form->addField('Descrição', $descricao, '70%');
        $this->form->addAction('Salvar', new Action(array($this, 'onSave')));
        parent::add($this->form);

    }
}
?>