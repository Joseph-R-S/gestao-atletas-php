<?php
namespace App\Controllers;

use App\Models\Atleta as Atleta;
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

    public function __set(string $prop, string $value)
    {
        $this->data[$prop] = $value;
    }

    public function __get(string $prop)
    {
        return $this->data[$prop] ?? null;
    }

    public function edit(array $param)
    {
        try {
            $id = (int) $param['id'];
            $this->data = Atleta::find($id);
        } catch (Exception $e) {
            echo "<h1>Erro ao buscar:</h1> " . $e->getMessage();
        }
    }

    public function save(array $param)
    {
        try {
            Atleta::save($param);
            $this->data = $param;
            header("Location: index.php?class=AtletaList");
        } catch (Exception $e) {
            echo "<h1>Erro ao cadastrar:</h1> " . $e->getMessage();
        }
    }

    public function show()
    {
        try {
            echo $this->twig->render('atleta_form.html.twig', ['atleta' => $this->data]);
        } catch (Exception $e) {
            echo "Erro ao renderizar formulário: " . $e->getMessage();
        }
    }
}
