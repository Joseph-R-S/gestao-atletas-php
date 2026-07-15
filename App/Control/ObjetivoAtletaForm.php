<?php

use Livro\Control\Page;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\FormWrapper;
use Livro\Control\Action;
use Livro\Control\TAction;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Widgets\Container\Panel;
use Livro\Widgets\Container\TNotebook;
use Livro\Widgets\Container\TVBox;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Hidden;
use Livro\Widgets\Form\Text;
use Livro\Widgets\Wrapper\BootstrapNotebookWrapper;

class ObjetivoAtletaForm extends Page
{
    private FormWrapper $form;
    private BootstrapNotebookWrapper $notebook;

    public function __construct()
    {
        parent::__construct();

        $this->form = new FormWrapper((new Form('form')));

        $id          = new Hidden('atleta_id');
        $objetivo_id = new Combo('objetivo_id');
        $descricao   = new Text('descricao');
        $prazo_meses = new Combo('prazo_meses');
        $motivo      = new Text('motivo');

        $num = array();
        for ($i = 1; $i <= 12; $i++) {
            $num[$i] = $i;
        }

        $prazo_meses->addItems($num);

        try {
            Transaction::open('livro');
            $objetivos = Objetivos::all();
            $items = array();
            foreach ($objetivos as $objetivo) {
                $items[$objetivo->id] = $objetivo->descricao;
            }
            $objetivo_id->addItems($items);

            // Importante: Manter o primeiro parâmetro mapeado corretamente com o nome do campo
            $this->form->addField('Atleta ID', $id, '');
            $this->form->addField('Objetivo', $objetivo_id, '3');
            $this->form->addField('Descricao', $descricao, '9');
            $this->form->addField('Meses', $prazo_meses, '3');
            $this->form->addField('Motivo', $motivo, '9');

            Transaction::close();
        } catch (Exception $e) {
            new Message('erro', 'Erro ao carregar objetivos: ' . $e->getMessage());
            Transaction::rollback();
        }

        $this->form->addAction('Salvar', new Action([$this, 'onSave']));

        if (isset($_GET['id'])) {
            Transaction::open('livro');
            $atleta = Atletas::find((int) $_GET['id']);
            $panel = new Panel('Objetivo do atleta ' . $atleta->nome);
            $id->setValue($_GET['id']);
            Transaction::close();
        } else {
            $panel = new Panel('Perfil do atleta');
        }

        $panel->add($this->form);
        $box = new TVBox;
        $box->add($panel);
        parent::add($panel);
    }

    /**
     * Salva os dados do formulário
     */
    public function onSave()
    {
        try {
            Transaction::open('livro');
            $dados = $this->form->getData();
            $dados_array = json_decode(json_encode($dados), true);

            $objetivo_atleta = new ObjetivoAtleta;
            $objetivo_atleta->fromArray((array) $dados_array);
            $objetivo_atleta->store();
            $this->form->setData($dados);
            Transaction::close();
            new Message('info', 'Dados salvos com sucesso!');
        } catch (Exception $e) {
            new Message('error', 'Erro ao salvar: ' . $e->getMessage());
            Transaction::rollback();
        }
    }

    public function onEdit(array $param)
    {
        try {
            if (isset($param['id'])) {
                $atleta_id = (int) $param['id'];
                
                Transaction::open('livro');
                
                $repository = new Repository('ObjetivoAtleta');
                $criteria = new Criteria;
                
                // 1. Buscamos apenas o último objetivo do atleta direto do banco
                $criteria->setProperty('order', 'id desc');
                $criteria->setProperty('limit', 3);
                $criteria->add('atleta_id', '=', $atleta_id);
                
                $objetivos = $repository->load($criteria);
                $stdObject = new \stdClass;
                $stdObject->atleta_id = $atleta_id;

                if (!empty($objetivos)) {
                    $ultimo = $objetivos[0];
                    $dadosPuros = $ultimo->toArray();
                    foreach ($dadosPuros as $campo => $valor) {
                        if ($campo !== 'id' && $campo !== 'atleta_id') {
                            $stdObject->$campo = $valor;
                        }
                    }
                }
                
                // 4. Enviamos de volta ao formulário de forma segura
                $this->form->setData($stdObject);
                
                Transaction::close();
            }
        } catch (Exception $e) {
            new Message('error', 'Erro ao buscar atleta: ' . $e->getMessage());
            Transaction::rollback();
        }
    }
}