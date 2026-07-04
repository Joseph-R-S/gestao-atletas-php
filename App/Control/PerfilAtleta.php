<?php

use Livro\Control\Page;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\FormWrapper;
use Livro\Control\Action;
use Livro\Control\TAction;
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

class PerfilAtleta extends Page
{
    // Usamos la propiedad de la clase de manera consistente
    private FormWrapper $form;
    private BootstrapNotebookWrapper $notebook;

    public function __construct()
    {
        parent::__construct();

        $this->form = new FormWrapper((new Form('form')));

        $id    = new Hidden('atleta_id');
        $objetivo_id = new Combo('objetivo_id');
        $descricao = new Text('descricao');
        $prazo_meses = new Combo('prazo_meses');
        $motivo = new Text('motivo');

                $num = array();
        for ($i = 1; $i <= 12; $i++) {
            $num[$i] = $i;
        }

        $prazo_meses->addItems($num);

        //para agregar los objetivos
        try {
            Transaction::open('livro');
            $objetivos = Objetivos::all();
            $items = array();
            foreach ($objetivos as $objetivo) {
                $items[$objetivo->id] = $objetivo->descricao;
            }
            $objetivo_id->addItems($items);

            $this->form->addField('', $id, '');
            $this->form->addField('Objetivo', $objetivo_id, '3');
            $this->form->addField('Descricao', $descricao, '9');
            $this->form->addField('Meses', $prazo_meses, '3');
            $this->form->addField('Motivo', $motivo, '9');

            Transaction::close();
        } catch (Exception $e) {
            new Message('erro', 'erro ao carregar objetivos');
            Transaction::rollback();
        }

        $this->form->addAction('Salvar', new Action([$this, 'onSave']));

        //para colocar o nome do atleta no
        if ($_GET['id']) {
            Transaction::open('livro');
            $atleta = Atletas::find((int) $_GET['id']);
            $panel = new Panel('Perfil do atleta ' . $atleta->nome);
            Transaction::close();
        } else {
            $panel = new Panel('Perfil do atleta');
        }

        //formulario de medidas_corporais
        $this->form_medidas = new FormWrapper((new Form('form_medidas')));

        $peso = new Entry('peso');
        $altura = new Entry('altura');

        $this->form_medidas->addField('', $id, '');
        $this->form_medidas->addField('Peso', $peso, '3');
        $this->form_medidas->addField('Altura', $altura, '3');

        $this->form_medidas->addAction('Salvar', new Action([$this, 'onSave']));

        $this->notebook = new BootstrapNotebookWrapper(new TNotebook);
        $this->notebook->appendPage('Objetivo', $this->form);
        $this->notebook->appendPage('Medidas', $this->form_medidas);

        $panel->add($this->notebook);
        $box = new TVBox;
        $box->add($panel);
        $box->add($this->notebook);
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

            // Usamos la propiedad global para recuperar los datos saneados por el método mágico __call
            $dados = $this->form->getData();
            $this->form->setData($dados);

            $objetivo_atleta = new ObjetivoAtleta;
            $objetivo_atleta->fromArray((array) $dados_array);
            $objetivo_atleta->store();

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
                $atleta_id = $param['id'];

                Transaction::open('livro');
                $atleta = Atletas::find($atleta_id);

                if ($atleta) {
                    $stdObject = new \stdClass;
                    $dadosPuros = $atleta->toArray();
                    foreach ($dadosPuros as $campo => $valor) {
                        $stdObject->$campo = $valor;
                    }
                    $stdObject->atleta_id = $atleta->id;

                    $this->form->setData($stdObject);
                }
                Transaction::close();
            }
        } catch (Exception $e) {
            new Message('error', 'Erro ao buscar atleta: ' . $e->getMessage());
        }
    }
}
