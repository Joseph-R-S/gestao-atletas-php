<?php
require_once 'Classes/Atleta.php';

class AvaliacaoForm {
    private string $html;
    private array $data;

    public function __construct() {
        $this->html = file_exists('html/avaliacao_form.html') ? file_get_contents('html/avaliacao_form.html') : '';
        $this->data = [
            'id' => '', 'atleta_id' => '', 'objetivo_id' => '', 'nivel_id' => '',
            'tipo_esporte_id' => '', 'meta_especifica' => '', 'peso' => '', 'altura' => ''
        ];

        $this->carregarAtleta($_GET['atleta_id']);
    }

    private function carregarAtleta(int $id) {
        $atleta = Atleta::find($id);

        $this->html = str_replace('{nome_completo}', $atleta['nome_completo'], $this->html);
    }

    public function save(array $dados) {
        try {
            // Aqui você usará um método Avaliacao::save($dados) similar ao do Atleta
            echo "<h1>Avaliação salva com sucesso!</h1>";
        } catch (Exception $e) {
            echo "<h1>Erro:</h1> " . $e->getMessage();
        }
    }

    public function show() {
        // Substituição dos campos de texto e números
        foreach ($this->data as $key => $value) {
            $this->html = str_replace('{'.$key.'}', $value ?? '', $this->html);
        }
        print $this->html;
    }
}

// Gatilho de execução
$form = new AvaliacaoForm();
if ($_POST) { $form->save($_POST); }
$form->show();