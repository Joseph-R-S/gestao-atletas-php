<?php

use Livro\Control\Page;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\FormWrapper;
use Livro\Control\Action;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Widgets\Container\TVBox;
use Livro\Widgets\Dialog\Question;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Hidden;

class PerfilAtletaFom extends Page
{
    private string $activeRecord;
    private string $connection;
    private FormWrapper $form_medidas;

    public function __construct()
    {
        parent::__construct();
        $this->activeRecord = 'PerfilAtleta';
        $this->connection = 'livro';

        $this->form_medidas = new FormWrapper((new Form('form_medidas')));
        $key = new Hidden('key');
        $atleta_id    = new Entry('id');
        $peso = new Entry('peso');
        $altura = new Entry('altura');
        $peito = new Entry('peito');
        $cintura = new Entry('cintura');
        $quadril = new Entry('quadril');
        $braco = new Entry('braco');
        $coxa = new Entry('coxa');
        $panturrilha = new Entry('panturrilha');

        $atleta_id->setEditable(FALSE);
        $atleta_id->setValue($_GET['id']);
        $key->setValue($_GET['key']);
        $factor_actividad = new Combo('factor_actividad');

        $factor_actividad->addItems(PerfilAtleta::FACTORES_ACTIVIDADE);
        $this->form_medidas->addField('key', $key, '');
        $this->form_medidas->addField('Id', $atleta_id, '');
        $this->form_medidas->addField('Factor de actividad', $factor_actividad, '3');
        $this->form_medidas->addField('Peso', $peso, '3');
        $this->form_medidas->addField('Altura', $altura, '3');

        $this->form_medidas->addField('Peito', $peito, '3');
        $this->form_medidas->addField('Cintura', $cintura, '3');
        $this->form_medidas->addField('Quadril', $quadril, '3');

        $this->form_medidas->addField('Braço', $braco, '3');
        $this->form_medidas->addField('Coxa', $coxa, '3');
        $this->form_medidas->addField('Panturrilha', $panturrilha, '3');

        $this->form_medidas->addAction('Salvar', new Action([$this, 'onSave']));
        $ver_historico = new Action([$this, 'onVerHistorico']);
        $ver_historico->setProperty('class', 'btn btn-info');
        $this->form_medidas->addAction('Ver historico', $ver_historico);

        $box = new TVBox;
        $box->style = 'width: 100%; display: flex;';
        $box->add($this->form_medidas);
        parent::add($box);
    }

    public function onSave()
    {
        $dados = $this->form_medidas->getData();
        $atleta_id = $dados->id; // Este es el ID del atleta que venía oculto
        $dados->id = '';
        unset($dados->key);
        $dados->atleta_id = $atleta_id;
        try {
            Transaction::open('livro');
            $medidas = new PerfilAtleta;
            $medidas->fromArray((array) $dados);
            $medidas->store();
            if ($dados->id == "") {
                $dados->id = $dados->atleta_id;
                $this->form_medidas->setData($dados);
            }
            new Message('info', 'Dados salvos');
            Transaction::close();
        } catch (Exception $e) {
            new Message('error', 'Erro: ' . $e->getMessage());
            Transaction::rollback();
        }
    }

    public function onReload()
    {
        $this->loaded = true;
    }

    public function onSetMedidas(array $param)
    {
        try {
            if (isset($param['id'])) {
                $id = $param['id'];
                Transaction::open('livro');
                $repository = new Repository('PerfilAtleta');
                $criteria = new Criteria;
                $criteria->add('atleta_id', '=', (int) $id);

                $medidas = $repository->load($criteria);
                if ($medidas) {
                    $medida = end($medidas);
                    $medida->id = '';
                    $stdObject = new \stdClass;
                    $dadosPuros = $medida->toArray();
                    foreach ($dadosPuros as $campo => $valor) {
                        $stdObject->$campo = $medida->$campo;
                    }
                    $this->form_medidas->setData($stdObject);
                }
                Transaction::close();
            }
        } catch (Exception $e) {
            new Message('erro', 'Erro ao buscar atleta');
        }
    }

    public function onEdit(array $param)
    {
        try {
            if (isset($param['key'])) {
                $key = $param['key'];
                Transaction::open('livro');
                $atleta = PerfilAtleta::find($key);
                if ($atleta) {
                    $stdObject = new \stdClass;
                    $dadosPuros = $atleta->toArray();
                    foreach ($dadosPuros as $campo => $valor) {
                        $stdObject->$campo = $atleta->$campo;
                    }
                    $stdObject->id = $atleta->atleta_id;
                    $this->form_medidas->setData($stdObject);
                }
                $excluir = new Action([$this, 'onDelete']);
                $excluir->setProperty('class', 'btn btn-danger');
                $this->form_medidas->addAction('Excluir',  $excluir,);
                Transaction::close();
            }
        } catch (Exception $e) {
            new Message('erro', 'Erro ao buscar atleta');
        }
    }

    /**
     * Método intermediario para capturar el ID del formulario y redirigir
     */
    public function onVerHistorico()
    {
        $dadosObj = $this->form_medidas->getData();
        $atleta_id = $dadosObj->id ?? null;

        if ($atleta_id) {
            // Creamos una acción apuntando a la clase destino
            Page::pageDestino('PerfilHistorico', 'onVerHistorico', $atleta_id, $atleta_id);
        } else {
            new Message('error', 'Não foi possível carregar o histórico: ID do atleta não identificado.');
        }
    }

    /**
     * Pergunta sobre a exclusão de registro
     */
    function onDelete()
    {
        $dados = $this->form_medidas->getData();

        $id = $dados->id;
        $key = $dados->key; // obtém o parâmetro $id
        $action1 = new Action([$this, 'Delete']);
        $action1->setParameter('key', $key);
        $action1->setParameter('id', $id);
        new Question('Deseja realmente excluir o registro?', $action1);
        $this->form_medidas->setData($dados);
    }

    /**
     * Exclui um registro
     */
    function Delete(array $param)
    {
        try {
            $key = $param['key']; // obtém a chave
            Transaction::open($this->connection); // inicia transação com o BD
            var_dump($param);
            $class = $this->activeRecord;

            $object = $class::find($key); // instancia objeto
            $object->delete(); // deleta objeto do banco de dados
            Transaction::close(); // finaliza a transação

            Page::pageDestino('PerfilHistorico', 'onVerHistorico', $param['key'], $param['id']);
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
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
