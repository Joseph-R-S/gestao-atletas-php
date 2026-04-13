<?php

namespace App\Controllers;

use App\Models\Atleta as Atleta;
use Exception;

class AtletaList
{
    private \Twig\Environment $twig;
    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    public function delete(array $param)
    {
        try {
            $id = (int) $param['id'];
            Atleta::delete($id);
            header("Location: index.php?class=AtletaList");
            exit;
        } catch (Exception $e) {
            echo "<h1>Erro ao remover:</h1> " . $e->getMessage();
        }
    }

    public function show()
    {
        try {
            $atletas = Atleta::all();
            echo $this->twig->render('atleta_list.html.twig', ['atletas' => $atletas]);
        } catch (Exception $e) {
            print 'Erro ao carregar as atletas ' . $e->getMessage();
        }
    }
}
