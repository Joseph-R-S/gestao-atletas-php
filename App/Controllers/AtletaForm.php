<?php
namespace App\Controllers;

use App\Database\Transaction;
use App\Models\Atleta as Atleta;
use App\Services\LoggerTXT;
use Exception;

class AtletaForm
{
    private \Twig\Environment $twig;
    private array $data = [];

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader('templates');
        //para poder fazer debug em twig, pode ser tirado despois, agora só porque estou aprendendo
        $this->twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);

        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    public function __set(string $prop, string $value) { $this->data[$prop] = $value;}

    public function __get(string $prop) { return $this->data[$prop] ?? null; }

    public function edit(array $param)
    {
        try {
            Transaction::open('db');
            Transaction::setLogger( new LoggerTXT('log.txt'));
            $id = (int) $param['id'];
            $this->data = Atleta::find($id);
            Transaction::close();
        } catch (Exception $e) {
            Transaction::rollback();
            echo "<div id='toast'>Erro ao buscar:</div> " . $e->getMessage();
        }
    }

    public function save(array $param)
    {
        try {
            Transaction::open('db');
            Transaction::setLogger( new LoggerTXT('log.txt'));
            Atleta::save($param);
            
            $this->data = $param;

            Transaction::close();
            header("Location: index.php?class=AtletaList");
            exit;
        } catch (Exception $e) {
            Transaction::rollback();
            echo "Erro: Este número de telefone já está cadastrado para outro atleta! " . $e->getMessage();
        }
    }

    public function show()
    {
        try {
            echo $this->twig->render('atleta_form.html.twig', ['atleta' => $this->data]);
        } catch (Exception $e) {
            echo "<div id='toast'>Erro ao renderizar formulário:</div> " . $e->getMessage();
        }
    }
}
