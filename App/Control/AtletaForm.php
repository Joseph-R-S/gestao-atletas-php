<?php

use Livro\Control\Page;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Form\Entry;
use Livro\Control\Action;
use Livro\Widgets\Dialog\Message;
use Livro\Widgets\Form\RadioGroup;
use Livro\Database\Transaction;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Date;
use Livro\Widgets\Wrapper\FormWrapper;

class AtletaForm extends Page
{
    private FormWrapper $form;
    public function __construct()
    {
        parent::__construct();

        $this->form = new FormWrapper(new Form('form'));
        $this->form->setTitle('Cadastro do Atleta');

        $id    = new Entry('id');
        $id->id = 'id_atleta';
        $id->onchange = "App.ejecutar('index.php?class=AtletaForm&method=onBuscaAtleta', {id_atleta: this.value})";
        $nome    = new Entry('nome');
        $nome->id = 'nome_atleta';
        $email    = new Entry('email');
        $telefone    = new Entry('telefone');
        $data_nascimento = new Date('data_nascimento');
        $sexo    = new RadioGroup('sexo');
        $ocupacao = new Entry('ocupacao');
        $estado_civil = new Combo('estado_civil');
        $endereco = new Entry('endereco');

        $estados_civil = ['s' => 'solteiro', 'c' => 'casado', 'v' => 'viuvo', 'd' => 'divorciado'];
        $estado_civil->addItems($estados_civil);

        $id->setEditable(FALSE);
        
        $sexos = ['m' => 'masculino', 'f' => 'feminino'];
        $sexo->addItems($sexos);

        $this->form->addField('Id', $id, '2');
        $this->form->addField('Nome', $nome, '5');
        $this->form->addField('Email', $email, '5');

        $this->form->addField('Telefone', $telefone, '3');
        $this->form->addField('Data nascimento', $data_nascimento, '3');
        $this->form->addField('Sexo', $sexo, '2');
        $this->form->addField('Ocupação', $ocupacao, '4');

        $this->form->addField('Estado civil', $estado_civil, '3');
        $this->form->addField('Endereço', $endereco, '9');

        $this->form->addAction('Salvar', new Action([$this, 'onSave']));
        parent::add($this->form);
    }

    /**
     * Salva os dados do formulário
     */
    public function onSave()
    {
        try {
            Transaction::open('livro');

            $dados = $this->form->getData();
            $this->form->setData($dados);
            $atleta = new Atletas; // instancia objeto
            
            $atleta->fromArray((array) $dados); // carrega os dados
  
            $atleta->store(); // armazena o objeto no banco de dados
            Transaction::close();
            new Message('info', 'Dados armazenados com sucesso');
        } catch (Exception $e) {
            // exibe a mensagem gerada pela exceção
            new Message('error', $e->getMessage());
            // desfaz todas alterações no banco de dados
            Transaction::rollback();
        }
    }
    
    public function onEdit(array $param) {
        try {
            if(isset($param['id'])){
                $id = $param['id'];
                
                Transaction::open('livro');
                $atleta = Atletas::find($id);
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
            new Message('erro', 'Erro ao buscar atleta');
        }
    }

    public function onBuscaAtleta()
    {
        try {
            // Obtener el término de búsqueda enviado por GET o POST
            $id_atleta = $_POST['id_atleta'] ?? $_GET['id_atleta'] ?? null;

            if (empty($id_atleta)) {
                throw new \Exception("ID o nombre de atleta no proporcionado.");
            }

            Transaction::open('livro'); // Abre la conexión

            // Buscar el registro en la base de datos
            $atleta = Atletas::find($id_atleta);

            Transaction::close(); // Cierra la conexión

            if ($atleta) {
                // Preparamos los datos del atleta en un array/objeto
                $datosAtleta = [
                    'id'       => $atleta->id,
                    'nome'     => $atleta->nome,
                    'peso'     => $atleta->peso,
                    'altura'   => $atleta->altura,
                    'idade'    => $atleta->idade,
                    'objetivo' => $atleta->objetivo ?? 'No definido'
                ];

                // Retornamos la instrucción Ajax en JSON
                $respuesta = [
                    // Acción 1: Imprimir en la consola JS (para inspeccionar los datos)
                    [
                        'tipo'   => 'executeJS',
                        //'script' => "console.log('Atleta encontrado:', " . json_encode($datosAtleta) . ");"
                    ],
                    // Acción 2: Autocompletar el campo 'nome' del formulario
                    [
                        'tipo'   => 'setValue',
                        'target' => 'nome_atleta',
                        'data'   => $atleta->nome
                    ]
                ];
            } else {
                throw new \Exception("Atleta con ID {$id_atleta} no fue encontrado.");
            }

            header('Content-Type: application/json');
            echo json_encode($respuesta);
            exit;

        } catch (\Throwable $e) {
            Transaction::rollback();
            
            header('Content-Type: application/json');
            echo json_encode([
                [
                    'tipo'   => 'executeJS',
                    'script' => "console.error('Error al buscar atleta:', '" . addslashes($e->getMessage()) . "');"
                ]
            ]);
            exit;
        }
    }
}
