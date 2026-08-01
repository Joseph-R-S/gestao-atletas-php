<?php

use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Database\Transaction;
use Livro\Traits\ReloadTrait;
use Livro\Traits\SaveTrait;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Entry;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Wrapper\FormWrapper;

class AlimentosForm extends Page
{
    private string $activeRecord;
    private string $connection;
    use ReloadTrait {
        onReload as onReloadTrait;
    }

    use SaveTrait {
        onSave as onSaveTrait;
    }
    public function __construct()
    {
        parent::__construct();
        $this->activeRecord = 'Alimentos';
        $this->connection = 'livro';

        $this->form = new FormWrapper((new Form('form_medidas')));

        $id = new Entry('id');
        $nome = new Entry('nome');
        $medida = new Combo('medida');
        $quantidade = new Entry('quantidade');
        $calorias = new Entry('calorias');
        $proteinas = new Entry('proteinas');
        $gorduras = new Entry('gorduras');
        $carbohidratos = new Entry('carbohidratos');
        $fibra = new Entry('fibra');
        $tipo = new Combo('tipo');

        $id->setEditable(FALSE);
        $medida->addItems(Alimentos::PORCAO);
        $tipo->addItems(Alimentos::TIPO);

        $this->form->setTitle('Alimentos');

        $this->form->addField('Id', $id, '12');
        $this->form->addField('Tipo', $tipo, '4');
        $this->form->addField('Nome', $nome, '4');
        $this->form->addField('Medida', $medida, '4');
        $this->form->addField('Quantidade', $quantidade, '4');
        $this->form->addField('Calorias', $calorias, '4');
        $this->form->addField('Proteinas', $proteinas, '4');
        $this->form->addField('Gorduras', $gorduras, '4');
        $this->form->addField('Carbohidratos', $carbohidratos, '4');
        $this->form->addField('Fibra', $fibra, '4');

        $this->form->addAction('Salvar', new Action([$this, 'onSave']));
        $this->form->addAction('Limpar', new Action([$this, 'onClear']));
        $page_destino = new Action([$this, 'onSetPage']);
        $this->form->addAction('Ver todos', $page_destino);

        parent::add($this->form);
    }

    public function onSetPage(){
        Page::pageDestino('AlimentoFormList', 'onReload');
    }

    public function onSave()
    {
        $this->onSaveTrait();
    }

    public function onEdit(array $param) {
        try {
            if(isset($param['id'])){
                $id = $param['id'];
                
                Transaction::open('livro');
                $atleta = Alimentos::find($id);
                if($atleta){
                    $stdObject = new \stdClass;
                    $dadosPuros = $atleta->toArray();
                    foreach($dadosPuros as $campo => $valor){
                        $stdObject->$campo = $atleta->$campo;
                    }
                    $stdObject->id = $atleta->id;
                    $this->form->setData($stdObject);
                }
                Transaction::close();
            }
        } catch (Exception $e) {
            new Message('erro', 'Erro ao buscar alimento');
        }
    }

    public function onClear() {
        $this->form->clear();
    }
}
