<?php

use Livro\Control\Page;
use Livro\Control\Action;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Text;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Wrapper\FormWrapper;

class ContatoForm extends Page
{
    private FormWrapper $form;

    public function __construct()
    {
        parent::__construct();
        $this->form = new FormWrapper(new Form('form_contato'));
        $this->form->setTitle('Formulário de contato');

        $nome = new Entry('nome');
        $email = new Entry('email');
        $assunto = new Combo('assunto');
        $mensagem = new Text('mensagem');

        $this->form->addField('Nome', $nome);
        $this->form->addField('Email', $email);
        $this->form->addField('Assunto', $assunto);
        $this->form->addField('Mensagem', $mensagem);

        $assunto->addItems([
            '1' => 'Sugestão',
            '2' => 'Reclamação',
            '3' => 'Suporte',
            '4' => 'Cobrança'
        ]);

        $mensagem->setSize('50%', '100px');

        $this->form->addAction('Enviar', new Action([$this, 'onSend']));

        parent::add($this->form);
    }

    public function onSend(array $params) {
        try {
        $data = $this->form->getData();
        $this->form->setData($data);
            if(empty($data->email)){
                throw new Exception('Email vazio');
            }

            if(empty($data->assunto)){
                throw new Exception('Assunto vazio');
            }
        
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
        }

    }
}
