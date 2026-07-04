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

class MedidasCorporais extends Page
{
    private FormWrapper $form_medidas;

    public function __construct()
    {
        parent::__construct();

        $this->form_medidas = new FormWrapper((new Form('form_medidas')));

        $id    = new Hidden('atleta_id');
        $peso = new Entry('peso');
        $altura = new Entry('altura');
        $peito = new Entry('peito');
        $cintura = new Entry('cintura');
        $quadril = new Entry('quadril');
        $braço = new Entry('braço');
        $coxa = new Entry('coxa');
        $panturrilha = new Entry('panturrilha');

        $this->form_medidas->addField('', $id, '');
        $this->form_medidas->addField('Peso', $peso, '3');
        $this->form_medidas->addField('Altura', $altura, '3');

        $this->form_medidas->addField('Peito', $peito, '3');
        $this->form_medidas->addField('Cintura', $cintura, '3');
        $this->form_medidas->addField('Quadril', $quadril, '3');

        $this->form_medidas->addField('Braço', $braço, '3');
        $this->form_medidas->addField('Coxa', $coxa, '3');
        $this->form_medidas->addField('Panturrilha', $panturrilha, '3');

        $this->form_medidas->addAction('Salvar', new Action([$this, 'onSave']));

        $box = new TVBox;
        $box->add($this->form_medidas);
        parent::add($box);
    }

    public function onSave()
    {
        $dados = $this->form_medidas->getData();
        $altura = $dados->altura;
        $peso = $dados->peso;
        try {
            Transaction::open('livro');

            $atleta = new Atletas(1);
            $data_nascimento = new DateTime($atleta->data_nascimento);
            $sexo = $atleta->sexo;

            $hoje = new DateTime();
            if ($sexo == 'm') {
                $ajuste_sexo = 5;
            } else {
                $ajuste_sexo = -161; // Se guarda como número negativo
            }

            // Calcular la edad correctamente
            $edad = $hoje->diff($data_nascimento);
            $altura = (int) round($altura * 100);

            $tmb = (10 * $peso) + (6.25 * $altura) - (5 * $edad->y) + $ajuste_sexo;


            var_dump($tmb);
            Transaction::close();
        } catch (Exception $e) {
            new Message('error', 'Erro: ' . $e->getMessage());
            Transaction::rollback();
        }
    }

    public function floatToIntScaled(float $value): int
    {
        $scale = 100;
        return (int) round($value * $scale);
    }
}
