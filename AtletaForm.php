<?php
require_once 'Classes/Atleta.php'; 

class AtletaForm
{
    private string $html;
    private array $data;
    public function __construct()
    {
        $this->html = file_exists('html/form.html') ? file_get_contents('html/form.html') : '';
        $this->data = ['id'           => '', 
                    'nome_completo'   => '', 
                    'email'           => '', 
                    'sexo'            => '', 
                    'telefone'        => '', 
                    'data_nascimento' => '',
                    ];
    }

    public function edit(array $dados)
    {
        try
        {
            $id = (int) $dados['id'];
            $this->data = Atleta::find($id);
        }catch(Exception $e)
        {
            echo "<h1>Erro ao buscar:</h1> " . $e->getMessage();
        }
    }

    public function save(array $dados)
    {
        try
        {
            Atleta::save($dados);
            $this->data = $dados;
            echo "<h1>Dados salvos com sucesso!</h1>";
            echo "<a href='AtletaList.php'>Voltar</a>";
        }catch(Exception $e)
        {
            echo "<h1>Erro ao cadastrar:</h1> " . $e->getMessage();
        }
    }

    public function show()
    {
        $this->html = str_replace('{id}', $this->data['id'] ?? '', $this->html);
        $this->html = str_replace('{nome_completo}', $this->data['nome_completo'] ?? '', $this->html);
        $this->html = str_replace('{email}', $this->data['email'] ?? '', $this->html);
        $this->html = str_replace('{telefone}', $this->data['telefone'] ?? '', $this->html);
        $this->html = str_replace('{data_nascimento}', $this->data['data_nascimento'] ?? '', $this->html);

    if (isset($this->data['sexo'])) {
        $valor_sexo = $this->data['sexo']; // Pode ser 'M' ou 'F'
        
        // Procura a tag 'value="M"' ou 'value="F"' e insere o 'selected' nela
        $this->html = str_replace(
            "value='{$valor_sexo}'", 
            "value='{$valor_sexo}' selected=1", 
            $this->html
        );
    }
    
    print $this->html;
    }
   
}

$form = new AtletaForm();

if ($_POST) {
    $form->save($_POST);
}

if (isset($_GET['id'])) {
    $form->edit($_GET);
}

$form->show();