<?php

namespace App\Controllers;

use App\Database\Transaction;
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
            Transaction::open('db');
            $id = (int) $param['id'];
            $atleta = new Atleta;
            $atleta->delete($id);
            Transaction::close();
            header("Location: index.php?class=AtletaList");
            exit;
        } catch (Exception $e) {
            Transaction::rollback();
            echo "<h1>Erro ao remover:</h1> " . $e->getMessage();
        }
    }

    public function show()
    {
        try {
            Transaction::open('db');

            $atletas = Atleta::all();
            Transaction::close();
            echo $this->twig->render('atleta_list.html.twig', ['atletas' => $atletas]);
        } catch (Exception $e) {
            Transaction::rollback();
            print 'Erro ao carregar as atletas ' . $e->getMessage();
        }
    }
}
