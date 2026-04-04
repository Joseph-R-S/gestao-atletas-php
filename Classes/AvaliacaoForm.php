<?php
class AvaliacaoForm {
    private Twig\Environment $twig;
    private array $data = [];

    public function __construct() {
        $loader = new \Twig\Loader\FilesystemLoader('templates');
        $this->twig = new \Twig\Environment($loader, [
        'debug' => true,
        ]);

        $this->twig->addExtension(new \Twig\Extension\DebugExtension());

        $this->carregarAtleta($_GET['atleta_id']);
    }

    private function carregarAtleta(int $id) {
        $atleta = Atleta::find($id);
        
        echo $this->twig->render('AvaliacaoForm.html.twig', [$atleta => $this->data]);
        
    }

    public function save(array $dados) {
        try {
            //Avaliacao::save($dados);
            echo "<h1>Avaliação salva com sucesso!</h1>";
        } catch (Exception $e) {
            echo "<h1>Erro:</h1> " . $e->getMessage();
        }
    }

    public function show() {

        print $this->html;
    }
}

// Gatilho de execução
$form = new AvaliacaoForm();
if ($_POST) { $form->save($_POST); }
$form->show();