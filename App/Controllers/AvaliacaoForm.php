<?php

namespace App\Controllers;

use App\Models\Atleta as Atleta;
use App\Models\Avaliacao as Avaliacao;
use Exception;

class AvaliacaoForm
{
    private \Twig\Environment $twig;
    private array $param = [];

    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    public function show(array $param = [])
    {
        $id = isset($param['atleta_id']) ? (int)$param['atleta_id'] : null;
        if ($id) {
            $id = (int) $param['atleta_id'];
            $id = isset($param['atleta_id']) ? (int)$param['atleta_id'] : null;
            if ($id) {
                $atleta = Atleta::find($id);
                echo $this->twig->render('avaliacao_form.html.twig', ['atleta' => $atleta]);
            }
        }
    }

    public function save(array $param)
    {
        try {
            $fotos = ['foto_frente_path', 'foto_costa_path', 'foto_lado_path'];
            $folder = 'uploads/';

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);

                file_put_contents($folder . '/.htaccess', "Options -Executables\n<Files *>\n    SetHandler default-handler\n</Files>");
            }
            foreach ($fotos as $campo) {
                if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION);
                    $newName = time() . '-' . $campo . '.' . $extension;
                    $destination = $folder . $newName;

                    if (move_uploaded_file($_FILES[$campo]['tmp_name'], $destination)) {
                        $param[$campo] = $destination;
                    }
                } else {
                    $param[$campo] = null;
                }
            }
            Avaliacao::save($param);

            $atleta = Atleta::find($param['atleta_id']);

            echo $this->twig->render('avaliacao_form.html.twig', [
                'atleta' => $atleta,
                'mensagem_sucesso' => "Avaliação salva com sucesso!"
            ]);
        } catch (Exception $e) {
            $atleta = Atleta::find($param['atleta_id']);
            echo $this->twig->render('avaliacao_form.html.twig', [
                'atleta' => $atleta,
                'mensagem_erro' => "Erro: " . $e->getMessage()
            ]);
        }
    }
}
