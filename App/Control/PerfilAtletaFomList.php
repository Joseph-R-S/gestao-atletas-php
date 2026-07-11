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
use Livro\Traits\DeleteTrait;
use Livro\Widgets\Container\TVBox;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Dialog\Question;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Hidden;
use Livro\Widgets\Wrapper\DatagridWrapper;

class PerfilAtletaFomList extends Page
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

        $atleta_id    = new Entry('id');
        $peso = new Entry('peso');
        $altura = new Entry('altura');
        $peito = new Entry('peito');
        $cintura = new Entry('cintura');
        $quadril = new Entry('quadril');
        $braco = new Entry('braco');
        $coxa = new Entry('coxa');
        $panturrilha = new Entry('panturrilha');

        $factor_actividad = new Combo('factor_actividad');

        $factor_actividad->addItems(PerfilAtleta::FACTORES_ACTIVIDADE);

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

        $this->datagrid = new DatagridWrapper(new Datagrid);

        $data_medicao   = new DatagridColumn('data_medicao', 'Data', 'left', '15%');
        $peso   = new DatagridColumn('peso', 'Peso', 'left', '10%');
        $altura   = new DatagridColumn('altura', 'Altura', 'left', '10%');
        $imc   = new DatagridColumn('imc', 'IMC', 'left', '20%');
        $tmb = new DatagridColumn('tmb', 'TMB', 'left', '25');
        $tmt = new DatagridColumn('tmt', 'TMT', 'left', '25');

        
        $this->datagrid->addColumn($data_medicao);
        $this->datagrid->addColumn($peso);
        $this->datagrid->addColumn($altura);
        $this->datagrid->addColumn($imc);
        $this->datagrid->addColumn($tmb);
        $this->datagrid->addColumn($tmt);
        
        $delete = new TAction([$this, 'onDelete']);
        $this->datagrid->addAction( 'Excluir',  $delete,  'id', 'fa fa-trash fa-lg text-danger', 'atleta_id');
        $box = new TVBox;
        $box->add($this->form_medidas);
        $box->add($this->datagrid);
        parent::add($box);
    }

    public function onSave()
    {
        $dados = $this->form_medidas->getData();
        $atleta_id = $dados->id; // Este es el ID del atleta que venía oculto
        $dados->id = '';
        $dados->atleta_id = $atleta_id;

        try {
            Transaction::open('livro');

            $medidas = new PerfilAtleta;
            $medidas->fromArray((array) $dados);
            $medidas->store();

            Transaction::close();

            $_GET['id'] = $dados->atleta_id;
            $this->onReload();
        } catch (Exception $e) {
            new Message('error', 'Erro: ' . $e->getMessage());
            Transaction::rollback();
        }
    }

    public function onReload()
    {
        try {
            $dados = $this->form_medidas->getData();
            $dados->atleta_id = $_GET['id'];
            if ($dados->atleta_id) {
                Transaction::open('livro');
                $repository = new Repository('PerfilAtleta');
                $criteria = new Criteria;

                $criteria->setProperty('order', 'id');

                $criteria->add('atleta_id', '=', (int) $dados->atleta_id);
                $atleta = new Atletas($dados->atleta_id);
                $sexo = $atleta->sexo;
                $data_nascimento = new DateTime($atleta->data_nascimento);

                $medidas = $repository->load($criteria);
                $this->datagrid->clear();
                foreach ($medidas as $medida) {
                    $nova_data = new DateTime($medida->data_medicao);

                    $altura_float = $medida->altura;
                    $altura = self::floatToIntScaled($altura_float);
                    $medida->data_medicao = $nova_data->format('d/m/Y');
                    $edad = $nova_data->diff($data_nascimento);

                    // Caculo IMC
                    $imc = PerfilAtleta::indiceMasaCorporal($medida->peso, $altura_float);

                    //calculo da TASA METABOLICA BASAL
                    $tmb = PerfilAtleta::tasaMetabolicaBasal($medida->peso, $altura, $edad, $sexo);

                    $tmt = PerfilAtleta::tasaMetabolicaTotal($tmb, $medida->factor_actividad);
                    $medida->tmb = $tmb;
                    $medida->tmt = $tmt;
                    $medida->imc = number_format($imc['imc'], 2) . ' ' . $imc['result'];
                    $medida->altura = $altura_float;

                    $tmt = PerfilAtleta::tasaMetabolicaTotal($tmb, $medida->factor_actividad);
                    $this->datagrid->addItem($medida);
                }
                Transaction::close();
            }
            $this->loaded = true;
        } catch (Exception $e) {
            new Message('error', 'Erro ao recarregar histórico de medidas: ' . $e->getMessage());
            Transaction::rollback();
        }
    }

    public function onEdit(array $param)
    {
        try {
            if (isset($param['id'])) {
                $id = $param['id'];

                Transaction::open('livro');
                $atleta = Atletas::find($id);
                if ($atleta) {
                    $stdObject = new \stdClass;
                    $dadosPuros = $atleta->toArray();
                    foreach ($dadosPuros as $campo => $valor) {
                        $stdObject->$campo = $atleta->$campo;
                    }
                    $stdObject->id = $atleta->id;
                    $this->form_medidas->setData($stdObject);
                }
                Transaction::close();
            }
        } catch (Exception $e) {
            new Message('erro', 'Erro ao buscar atleta');
        }
    }

        /**
     * Pergunta sobre a exclusão de registro
     */
    function onDelete(array $param)
    {
        $id = $param['id']; // obtém o parâmetro $id
        $action1 = new Action(array($this, 'Delete'));
        $action1->setParameter('id', $id);
        
        new Question('Deseja realmente excluir o registro?', $action1);

        $this->onReload(); // recarrega a datagrid
    }

    /**
     * Exclui um registro
     */
    function Delete(array $param)
    {
        try
        {
            $id = $param['id']; // obtém a chave
            Transaction::open( $this->connection ); // inicia transação com o BD
            
            $class = $this->activeRecord;
            
            $object = $class::find($id); // instancia objeto
            $object->delete(); // deleta objeto do banco de dados
            Transaction::close(); // finaliza a transação
            $this->onReload(); // recarrega a datagrid
            new Message('info', "Registro excluído com sucesso");
        }
        catch (Exception $e)
        {
            new Message('error', $e->getMessage());
        }
    }

    public function floatToIntScaled(float $value): int
    {
        $scale = 100;
        return (int) round($value * $scale);
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
