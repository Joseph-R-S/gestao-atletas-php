<?php

use Livro\Control\Page;
use Livro\Widgets\Container\Panel;

/**
 * Exibe código-fonte
 */
class ViewSource extends Page
{
    private $form; // formulário

    public function onView(array $param)
    {
        ini_set('highlight.comment', "#808080");
        ini_set('highlight.default', "#FFFFFF"); // Texto base blanco
        ini_set('highlight.html',    "#C0C0C0");
        ini_set('highlight.keyword', "#62d3ea"); // Cian
        ini_set('highlight.string',  "#FFC472"); // Naranja

        $class = $param['source'];
        $file = "App/Control/{$class}.php";
        if (file_exists($file)) {
            $panel = new Panel('Código-fonte: ' . $class);
            $panel->id = 'source-panel';

            $codigo = highlight_file($file, TRUE);

            // Usamos 'bg-dark' y 'text-white' de Bootstrap 5
            $html_final = "<pre class='bg-dark text-white text-start p-4
             rounded font-monospace overflow-auto' 
             style='max-height: 75vh; line-height: 0.75;'>
             {$codigo}</pre>";

            $panel->add($html_final);

            parent::add($panel);
        }
    }
}
