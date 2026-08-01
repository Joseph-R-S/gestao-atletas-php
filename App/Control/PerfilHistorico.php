<?php

use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Control\TAction;
use Livro\Database\Criteria;
use Livro\Database\Repository;
use Livro\Database\Transaction;
use Livro\Traits\DeleteTrait;
use Livro\Widgets\Container\TVBox;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Wrapper\DatagridWrapper;

class PerfilHistorico extends Page
{
    use DeleteTrait;

    public function __construct()
    {
        parent::__construct();

        $this->datagrid = new DatagridWrapper(new Datagrid);

        $data_medicao = new DatagridColumn('data_medicao', 'Data', 'left', '15%');
        $peso         = new DatagridColumn('peso', 'Peso', 'left', '10%');
        $altura       = new DatagridColumn('altura', 'Altura', 'left', '10%');
        $imc          = new DatagridColumn('imc', 'IMC', 'left', '20%');
        $tmb          = new DatagridColumn('tmb', 'TMB', 'left', '25%');
        $tmt          = new DatagridColumn('tmt', 'TMT', 'left', '25%');

        $this->datagrid->addColumn($data_medicao);
        $this->datagrid->addColumn($peso);
        $this->datagrid->addColumn($altura);
        $this->datagrid->addColumn($imc);
        $this->datagrid->addColumn($tmb);
        $this->datagrid->addColumn($tmt);

        $this->datagrid->addAction('Medidas', new TAction([$this, 'onVerMedidas']), 'id', 'fa fa-eye fa-lg blue');
        
        $box = new TVBox;
        $box->add($this->datagrid);
        parent::add($box);
    }

    public function onVerMedidas()
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id) {
            try {
                Transaction::open('livro');
                
                $perfil = new PerfilAtleta($id);
                
                $action = new Action([new PerfilAtletaFom, 'onEdit']);
                $action->setParameter('key', $id);
                $action->setParameter('id', $perfil->atleta_id);

                $url = $action->serialize();

                Transaction::close();

                header("Location: {$url}");
                exit;

            } catch (Exception $e) {
                Transaction::rollback();
                new Message('error', 'Erro ao carregar medidas: ' . $e->getMessage());
            }
        } else {
            new Message('error', 'Não foi possível carregar o histórico: ID do atleta não identificado.');
        }
    }

    public function onVerHistorico(array $param)
    {
        $atleta_id = $param['id'] ?? null;

        if ($atleta_id) {
            try {
                Transaction::open('livro');

                $repository = new Repository('PerfilAtleta');
                $criteria   = new Criteria;

                $criteria->setProperty('order', 'id');
                $criteria->add('atleta_id', '=', (int) $atleta_id);

                $atleta          = new Atletas($atleta_id);
                $sexo            = $atleta->sexo;
                $data_nascimento = new DateTime($atleta->data_nascimento);

                $medidas = $repository->load($criteria);

                foreach ($medidas as $medida) {
                    $nova_data = new DateTime($medida->data_medicao);

                    $altura_float = $medida->altura;
                    $altura       = PerfilAtleta::floatToIntScaled($altura_float);

                    $medida->data_medicao = $nova_data->format('d/m/Y');
                    $edad                 = $nova_data->diff($data_nascimento);

                    // Cálculo IMC
                    $imc = PerfilAtleta::indiceMasaCorporal($medida->peso, $altura_float);

                    // Cálculo da TMB e TMT
                    $tmb = PerfilAtleta::tasaMetabolicaBasal($medida->peso, $altura, $edad, $sexo);
                    $tmt = PerfilAtleta::tasaMetabolicaTotal($tmb, $medida->factor_actividad);

                    $medida->tmb    = $tmb;
                    $medida->tmt    = $tmt;
                    $medida->imc    = number_format($imc['imc'], 2) . ' ' . $imc['result'];
                    $medida->altura = $altura_float;

                    $this->datagrid->addItem($medida);
                }

                Transaction::close();

            } catch (Exception $e) {
                Transaction::rollback();
                new Message('error', 'Erro ao buscar histórico: ' . $e->getMessage());
            }
        }
    }
}