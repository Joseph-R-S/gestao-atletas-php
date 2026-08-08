<?php

use Livro\Control\Page;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\FormWrapper;
use Livro\Control\Action;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Widgets\Container\Panel;
use Livro\Widgets\Container\TVBox;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Hidden;
use Livro\Widgets\Form\Text;    

class ObjetivoAtletaForm extends Page
{
    private FormWrapper $form;

    public function __construct()
    {
        parent::__construct();

        $this->form = new FormWrapper(new Form('form_objetivo_atleta'));

        $id          = new Hidden('atleta_id');
        $objetivo_id = new Combo('objetivo_id');
        $descricao   = new Text('descricao');
        $prazo_meses = new Combo('prazo_meses');
        $motivo      = new Text('motivo');

        // Opciones de plazo en meses (1 a 12)
        $num = [];
        for ($i = 1; $i <= 12; $i++) {
            $num[$i] = $i . ($i === 1 ? ' mês' : ' meses');
        }
        $prazo_meses->addItems($num);

        // Cargar combo de objetivos
        try {
            Transaction::open('livro');
            $objetivos = Objetivos::all();
            $items = [];
            foreach ($objetivos as $objetivo) {
                $items[$objetivo->id] = $objetivo->descricao;
            }
            $objetivo_id->addItems($items);
            Transaction::close();
        } catch (Exception $e) {
            new Message('erro', 'Erro ao carregar objetivos: ' . $e->getMessage());
            Transaction::rollback();
        }

        // Mapeo de campos en el formulario
        $this->form->addField('', $id, '');
        $this->form->addField('Objetivo', $objetivo_id, '3');
        $this->form->addField('Descrição', $descricao, '9');
        $this->form->addField('Prazo', $prazo_meses, '3');
        $this->form->addField('Motivo', $motivo, '9');

        $this->form->addAction('Salvar', new Action([$this, 'onSave']));

        // Título dinámico del Panel
        $nomeAtleta = '';
        if (isset($_GET['id'])) {
            try {
                Transaction::open('livro');
                $atleta = Atletas::find((int) $_GET['id']);
                if ($atleta) {
                    $nomeAtleta = $atleta->nome;
                    $id->setValue($_GET['id']);
                }
                Transaction::close();
            } catch (Exception $e) {
                Transaction::rollback();
            }
        }

        $tituloPanel = !empty($nomeAtleta) ? 'Objetivo do atleta: ' . $nomeAtleta : 'Perfil do atleta';
        $panel = new Panel($tituloPanel);
        $panel->add($this->form);

        $box = new TVBox;
        $box->add($panel);
        parent::add($box);
    }

    /**
     * Salva os dados do formulário criando um novo histórico de objetivo ativo
     */
    public function onSave()
    {
        try {
            Transaction::open('livro');

            $dados = $this->form->getData();

            if (empty($dados->atleta_id)) {
                throw new Exception('ID do atleta não informado.');
            }

            // 1. Inactivar todos los objetivos previos del atleta
            $repository = new Repository('ObjetivoAtleta');
            $criteria = new Criteria;
            $criteria->add('atleta_id', '=', (int) $dados->atleta_id);
            $criteria->add('activo', '=', true);
            
            $objetivosAntigos = $repository->load($criteria);
            if ($objetivosAntigos) {
                foreach ($objetivosAntigos as $objAntigo) {
                    $objAntigo->activo = false;
                    $objAntigo->store();
                }
            }

            // 2. Guardar el nuevo objetivo como activo
            $objetivo_atleta = new ObjetivoAtleta;
            $objetivo_atleta->atleta_id   = $dados->atleta_id;
            $objetivo_atleta->objetivo_id = $dados->objetivo_id;
            $objetivo_atleta->descricao   = $dados->descricao;
            $objetivo_atleta->prazo_meses = $dados->prazo_meses;
            $objetivo_atleta->motivo      = $dados->motivo;
            $objetivo_atleta->activo      = true;
            $objetivo_atleta->store();

            $this->form->setData($dados);
            Transaction::close();

            new Message('info', 'Objetivo salvo com sucesso!');
        } catch (Exception $e) {
            new Message('error', 'Erro ao salvar: ' . $e->getMessage());
            Transaction::rollback();
        }
    }

    /**
     * Carga el objetivo activo actual del atleta en el formulario
     */
    public function onEdit(array $param)
    {
        try {
            if (isset($param['id'])) {
                $atleta_id = (int) $param['id'];

                Transaction::open('livro');

                $repository = new Repository('ObjetivoAtleta');
                $criteria = new Criteria;
                $criteria->add('atleta_id', '=', $atleta_id);
                $criteria->add('activo', '=', true);
                $criteria->setProperty('order', 'id desc');
                $criteria->setProperty('limit', 1);

                $objetivos = $repository->load($criteria);

                $stdObject = new \stdClass;
                $stdObject->atleta_id = $atleta_id;

                if (!empty($objetivos)) {
                    $ultimo = $objetivos[0];
                    $dadosPuros = $ultimo->toArray();

                    foreach ($dadosPuros as $campo => $valor) {
                        // Omitimos la PK de objetivo_atleta para forzar un INSERT nuevo en onSave
                        if ($campo !== 'id') {
                            $stdObject->$campo = $valor;
                        }
                    }
                }

                $this->form->setData($stdObject);
                Transaction::close();
            }
        } catch (Exception $e) {
            new Message('error', 'Erro ao buscar dados do atleta: ' . $e->getMessage());
            Transaction::rollback();
        }
    }
}