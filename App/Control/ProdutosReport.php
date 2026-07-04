<?php

use Livro\Control\Page;
use Livro\Widgets\Dialog\Message;
use Livro\Database\Transaction;
use Livro\Widgets\Container\Panel;

/**
 * Relatório de vendas
 */
class ProdutosReport extends Page
{
    /**
     * método construtor
     */
    public function __construct()
    {
        parent::__construct();

        $loader = new \Twig\Loader\FilesystemLoader('App/Resources');
        $twig = new \Twig\Environment($loader);

        // vetor de parâmetros para o template
        $replaces = array();

        // gerador Barcode em HTML
        $generator = new Picqer\Barcode\BarcodeGeneratorHTML();

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd
        );

        $writer = new \BaconQrCode\Writer($renderer);

        try {
            // inicia transação com o banco 'livro'
            Transaction::open('livro');
             //devuelve um objeto array de objetos de la classe producto
            $produtos_obj = Produto::all();
            $produtos_array = array(); // Creamos un nuevo array limpio

            foreach ($produtos_obj as $produto) {
                // 1. Extraemos los datos internos del objeto como un array asociativo
                $item = $produto->toArray();

                // 2. Generamos el Barcode y el QR usando los datos del array
                $item['barcode'] = $generator->getBarcode((string)$item['id'], 'C128', 3, 50);
                $item['qrcode']  = $writer->writeString($item['id'] . ' ' . $item['descricao']);

                // 3. Guardamos el producto procesado
                $produtos_array[] = $item;
            }
            
            // Le pasamos el array limpio a Twig
            $replaces['produtos'] = $produtos_array;
            Transaction::close(); // finaliza a transação
        } catch (Exception $e) {
            new Message('error', $e->getMessage());
            Transaction::rollback();
        }

        $content = $twig->render('produtos_report.html', $replaces);

        // cria um painél para conter o formulário
        $panel = new Panel('Produtos');
        $panel->add($content);
        parent::add($panel);
    }
}
