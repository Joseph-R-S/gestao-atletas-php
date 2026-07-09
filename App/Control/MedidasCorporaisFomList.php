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
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Hidden;
use Livro\Widgets\Form\Text;
use Livro\Widgets\Wrapper\BootstrapNotebookWrapper;
use Livro\Widgets\Wrapper\DatagridWrapper;

class MedidasCorporaisFomList extends Page
{
    private FormWrapper $form_medidas;

    public function __construct()
    {
        parent::__construct();

        $this->form_medidas = new FormWrapper((new Form('form_medidas')));

        $atleta_id    = new Hidden('id');
        $peso = new Entry('peso');
        $altura = new Entry('altura');
        $peito = new Entry('peito');
        $cintura = new Entry('cintura');
        $quadril = new Entry('quadril');
        $braco = new Entry('braco');
        $coxa = new Entry('coxa');
        $panturrilha = new Entry('panturrilha');

        $this->form_medidas->addField('Id', $atleta_id, '');
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

        $this->datagrid->addColumn($data_medicao);
        $this->datagrid->addColumn($peso);
        $this->datagrid->addColumn($altura);
        $this->datagrid->addColumn($imc);
        $this->datagrid->addColumn($tmb);

        $box = new TVBox;
        $box->add($this->form_medidas);
        $box->add($this->datagrid);
        parent::add($box);
    }

    public function onSave()
    {
        $dados = $this->form_medidas->getData();
        $atleta_id = $dados->id;
        $dados->id = '';
        $dados->atleta_id = $atleta_id;

        try {
            Transaction::open('livro');

            $medidas = new MedidasCorporais;
            $medidas->fromArray((array) $dados);
            $medidas->store();

            Transaction::close();
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
                $repository = new Repository('MedidasCorporais');
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
                    $imc = self::indiceMasaCorporal($medida->peso, $altura_float);

                    //calculo da TASA METABOLICA BASAL
                    $tmb = self::tasaMetabolicaBasal($medida->peso, $altura, $edad, $sexo);

                    $medida->tmb = $tmb;
                    $medida->imc = number_format($imc['imc'], 2) . ' ' . $imc['result'];
                    $medida->altura = $altura_float;

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


    public function floatToIntScaled(float $value): int
    {
        $scale = 100;
        return (int) round($value * $scale);
    }

    public function indiceMasaCorporal(float $peso, float $altura): array
    {
        $cal = $peso / ($altura * $altura);

        if($cal < 18.5){
            $string = ' Abaixo do peso';
        } elseif($cal < 24.9){
            $string = ' Peso normal';
        }elseif($cal < 29.9){
            $string = ' Pré-obesidade';
        }else{
            $string = ' Obesidade';
        }
        $imc = [];
        $imc['imc'] = $cal;
        $imc['result'] = $string;
        return $imc;
    }

    public function tasaMetabolicaBasal(float $peso, int|float $altura, object $edad, string $sexo) : float
    {
        if ($sexo == 'm') {
            $ajuste_sexo = 5;
        } else {
            $ajuste_sexo = -161;
        }
        return (10 * $peso) + (6.25 * $altura) - (5 * $edad->y) + $ajuste_sexo;
    }

    //$fa factor de actividad
    public function  tasaMetabolicaTotal(float $tmb, $fa) {
        
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
