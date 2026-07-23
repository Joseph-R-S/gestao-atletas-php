<?php

use Livro\Control\Action;
use Livro\Control\Page;
use Livro\Widgets\Container\TVBox;
use Livro\Widgets\Datagrid\Datagrid;
use Livro\Widgets\Datagrid\DatagridColumn;
use Livro\Widgets\Datagrid\PageNavigation;

class EjemploNavegacionPage extends Page
{
    private Datagrid $datagrid;
    private PageNavigation $pageNavigation;

    public function __construct()
    {
        parent::__construct();

        // 1. Instanciar DataGrid y definir columnas
        $this->datagrid = new Datagrid;

        $id   = new DatagridColumn('id', 'ID', 'center', '20%');
        $nome = new DatagridColumn('nome', 'Alimento (Simulado)', 'left', '80%');

        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);

        // 2. Instanciar la Paginación
        $this->pageNavigation = new PageNavigation;
        $this->pageNavigation->setAction(new Action([$this, 'onReload']));

        // 3. Montar el contenedor visual
        $box = new TVBox;
        $box->style = 'width: 100%; max-width: 800px; margin: 20px auto;';
        $box->add($this->datagrid);
        $box->add($this->pageNavigation);

        parent::add($box);
    }

    public function onReload(array $param = [])
    {
        // -----------------------------------------------------------
        // SIMULACIÓN DE BASE DE DATOS (50 registros en memoria)
        // -----------------------------------------------------------
        $alimentosFalsos = [];
        for ($i = 1; $i <= 50; $i++) {
            $alimentosFalsos[] = (object) [
                'id'   => $i,
                'nome' => "Alimento Simulado Nº {$i}"
            ];
        }

        // 1. Obtener la página actual desde $param o $_GET (por defecto 1)
        $currentPage = 1;
        if (isset($param['page']) && !empty($param['page'])) {
            $currentPage = (int) $param['page'];
        } elseif (isset($_GET['page']) && !empty($_GET['page'])) {
            $currentPage = (int) $_GET['page'];
        }

        // 2. Configurar el límite y el offset
        $limit  = 10;
        $offset = ($currentPage - 1) * $limit;
        $totalRegistros = count($alimentosFalsos); // 50 elementos

        // 3. Cortar el array equivalente a lo que haría LIMIT / OFFSET en SQL
        $registrosPagina = array_slice($alimentosFalsos, $offset, $limit);

        // 4. Renderizar las filas en el DataGrid
        $this->datagrid->clear();
        foreach ($registrosPagina as $alimento) {
            $this->datagrid->addItem($alimento);
        }

        // 5. Enviar las métricas al PageNavigation
        $this->pageNavigation->setCount($totalRegistros);
        $this->pageNavigation->setLimit($limit); // 👈 Usar setLimit
        $this->pageNavigation->setPage($currentPage);
    }

    public function show()
    {
        // Cargar los datos la primera vez que se muestra la página
        $this->onReload();
        parent::show();
    }
}
