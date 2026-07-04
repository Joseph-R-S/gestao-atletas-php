<?php

use Livro\Control\Page;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Widgets\Container\Panel;

/**
 * Vendas por mês
 */
class VendasMesChart extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        $loader = new \Twig\Loader\FilesystemLoader('App/Resources');
        $twig = new \Twig\Environment($loader);

        // 1. Inicializamos el contenedor de parámetros arriba para que siempre exista
        $replaces = array();
        $replaces['title']  = 'Vendas por mês';
        $replaces['labels'] = json_encode([]); // Valores vacíos por defecto por si falla la BD
        $replaces['data']   = json_encode([]);
        
        try {
            // inicia transação com o banco 'livro'
            Transaction::open('livro');
            
            $vendas = Venda::getVendasMes();
            
            // 2. CORRECCIÓN CLAVE: Inyectamos las llaves y valores aquí adentro, donde $vendas sí existe plenamente.
            if (!empty($vendas)) {
                $replaces['labels'] = json_encode(array_keys($vendas));
                $replaces['data']   = json_encode(array_values($vendas));
            }

            Transaction::close(); // finaliza a transação
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }

        // 3. Renderizamos la vista con los reemplazos ya procesados
        $content = $twig->render('vendas_mes.html', $replaces);

        // cria um painél para conter o formulário
        $panel = new Panel('Vendas/mês');
        $panel->add($content);

        parent::add($panel);
    }
}