<?php

namespace App\Controllers;

use App\Database\Transaction;
use App\Models\Atleta as Atleta;
use App\Models\Record;
use App\Models\Avaliacao as Avaliacao;
use Exception;

class AvaliacaoForm
{
    private \Twig\Environment $twig;

    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    public function show(array $param = [])
    {
        try {
            $id = isset($param['atleta_id']) ? (int)$param['atleta_id'] : null;

            if (!$id) {
                throw new Exception("ID do atleta não fornecido.");
            }
            Transaction::open('db');
            $atleta = new Atleta();
            $atleta->load($id);
            Transaction::close();

            echo $this->twig->render('avaliacao_form.html.twig', ['atleta' => $atleta]);
        } catch (Exception $e) {
            Transaction::rollback();

            $atleta = null;
            if (isset($id) && $id) {
                Transaction::open('db');
                $atleta = new Atleta;
                $atleta->load($id);
                Transaction::close();
            }
            echo $this->twig->render('avaliacao_form.html.twig', [
                'mensagem_erro' => "Erro ao carregar: " . $e->getMessage()
            ]);
        }
    }

    public function save(array $param)
    {
        try {
            $fotos = ['foto_frente_path', 'foto_costa_path', 'foto_lado_path'];
            $folder = 'uploads/';

            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);

                file_put_contents($folder . '/.htaccess', "Options -Executables\n<Files *>\n    SetHandler default-handler\n</Files>");
            }
            foreach ($fotos as $campo) {
                if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION);
                    $newName = "atleta_" . $param['atleta_id'] . "_" . $campo . "_" . time() . "." . $extension;
                    $destination = $folder . $newName;

                    if (move_uploaded_file($_FILES[$campo]['tmp_name'], $destination)) {
                        $param[$campo] = $destination;
                    }
                } else {
                    $param[$campo] = null;
                }
            }
            Transaction::open('db');
            $avaliacao = new Avaliacao;
            $avaliacao->fromArray($param);
            $avaliacao->store();
            $atleta = new Atleta;
            $atleta->load(($param['atleta_id']));
            Transaction::close();

            echo $this->twig->render('avaliacao_form.html.twig', [
                'atleta' => $atleta,
                'mensagem_sucesso' => "Avaliação salva com sucesso!"
            ]);
        } catch (Exception $e) {
            Transaction::rollback();
            echo $this->twig->render('avaliacao_form.html.twig', [
                'mensagem_erro' => "Erro crítico: " . $e->getMessage()
            ]);
        }
    }
}
