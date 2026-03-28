<?php
require_once 'Classes/Atleta.php';

class AtletaList
{
    private string $html;
    public function __construct()
    {
        $this->html = file_exists('html/list.html') ? file_get_contents('html/list.html') : '';
    }

    public function delete(array $param)
    {
        try
        {
            $id = (int) $param['id'];
            Atleta::delete($id);
            header("Location: AtletaList.php");
            exit;
        }catch (Exception $e) {
        echo "<h1>Erro ao remover:</h1> " . $e->getMessage();
        }
    }

    public function load()
    {
        try
        {
            $atletas = Atleta::all();
            $items = '';
            foreach ($atletas as $atleta)
            {
                $item = file_exists('html/item.html') ? file_get_contents('html/item.html') : '';
                $item = str_replace('{id}', $atleta['id'], $item);
                $item = str_replace('{nome_completo}', $atleta['nome_completo'], $item);
                $item = str_replace('{email}', $atleta['email'], $item);
                $item = str_replace('{sexo}', $atleta['sexo'], $item);
                $item = str_replace('{telefone}', $atleta['telefone'], $item);
                $item = str_replace('{data_nascimento}', $atleta['data_nascimento'], $item);

                $items .= $item;
            }

             $this->html = str_replace('{items}', $items, $this->html);

        }catch(Exception $e)
        {
            print 'Erro ao carregar as atletas ' . $e->getMessage();
        }
    }

    public function show()
    {
        $this->load();
        print $this->html;
    }
}

$list = new AtletaList();

if (isset($_GET['method']) && $_GET['method'] == 'delete') {
    $list->delete($_GET);
}

$list->show(); 