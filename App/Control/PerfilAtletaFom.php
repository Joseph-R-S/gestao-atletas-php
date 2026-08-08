<?php

use Livro\Control\Page;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\FormWrapper;
use Livro\Control\Action;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Session\Session;
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
        new Session;
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

        $id = $_GET['id'] ?? null;
        $atleta_id->setValue($id);
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

        $ir_dieta = new Action([$this, 'onIrDieta']);
        $ir_dieta->setProperty('class', 'btn btn-success');
        $this->form_medidas->addAction('Criar Dieta', $ir_dieta);

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
                $key = $param['key'];
                Transaction::open('livro');
                $repository = new Repository('PerfilAtleta');
                $criteria = new Criteria;
                $criteria->add('atleta_id', '=', (int) $id);

                $perfils = $repository->load($criteria);
                if ($perfils) {
                    $perfil = end($perfils);
                    $perfil->id = '';
                    $stdObject = new \stdClass;
                    $dadosPuros = $perfil->toArray();
                    foreach ($dadosPuros as $campo => $valor) {
                        $stdObject->$campo = $perfil->$campo;
                    }
                    $stdObject->key = $key;
                    $this->form_medidas->setData($stdObject);
                }
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
                    $stdObject->key = $key;
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
            $class = $this->activeRecord;

            $object = $class::find($key); // instancia objeto
            $object->delete(); // deleta objeto do banco de dados
            Transaction::close(); // finaliza a transação

            Page::pageDestino('PerfilHistorico', 'onVerHistorico', $param['key'], $param['id']);
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
        }
    }

    /**
     * Redirige a la pantalla de creación de dietas pasando el ID del atleta
     */
    public function onIrDieta()
    {
        $dadosObj = $this->form_medidas->getData();
        $atleta_id = $dadosObj->id ?? null;
        $atleta_session = Session::getValue('atleta_dieta');

        if ($atleta_id) {
            try {
                Transaction::open('livro');

                // 1. Buscamos el registro del atleta
                $atleta = Atletas::find($atleta_id);

                if (!$atleta) {
                    throw new Exception("Atleta não encontrado no sistema.");
                }
                // Si no hay sesión o es otro atleta, inicializamos el stdClass
                if (!$atleta_session || ($atleta_session->id_atleta != $atleta->id)) {
                    $atleta_session = new \stdClass;
                    $atleta_session->id_atleta = $atleta->id;
                    $atleta_session->nome      = $atleta->nome;
                }

                // 2. Preparamos la estructura de sesión del atleta
                $atleta_session->factor_actividade = $dadosObj->factor_actividad;
                $atleta_session->peso              = $dadosObj->peso ?? null;
                $atleta_session->altura            = $dadosObj->altura ?? null;

                // 3. Calculamos TMB/TMT si tenemos los datos completos
                $this->setarAtleta($atleta->data_nascimento, $dadosObj->altura, $dadosObj->peso, $atleta->sexo, $atleta_session);
                // 4. Guardamos en la sesión
                Session::setValue('atleta_dieta', $atleta_session);

                Transaction::close();

                // 5. Redirigimos a la página de la dieta
                Page::pageDestino('DietaForm', 'onSetDieta', $atleta_id, $atleta_id);
            } catch (Exception $e) {
                Transaction::rollback();
                new Message('error', 'Erro ao preparar dados da dieta: ' . $e->getMessage());
            }
        } else {
            new Message('error', 'Selecione um atleta válido antes de montar a dieta.');
        }
    }

    public function setarAtleta($data_nascimento, $altura, $peso, $sexo, $atleta_session)
    {
        $data_nascimento = new DateTime($data_nascimento);
        $edad = $data_nascimento->diff(new DateTime('now'));

        $altura_scaled = PerfilAtleta::floatToIntScaled($altura);
        $tmb = PerfilAtleta::tasaMetabolicaBasal($peso, $altura_scaled, $edad, $sexo);
        $tmt = PerfilAtleta::tasaMetabolicaTotal($tmb, $atleta_session->factor_actividade);
        $atleta_session->tasa_metabolica = number_format((float)$tmt, 2, '.', '');
        Session::setValue('atleta_dieta', $atleta_session);
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
