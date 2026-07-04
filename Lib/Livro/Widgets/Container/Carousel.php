<?php

namespace Livro\Widgets\Container; 

use Livro\Widgets\Base\Element;

class Carousel extends Element
{
    public function __construct(string $id, array $images)
    {
        // Configuramos o próprio componente como a div principal do Carrossel
        parent::__construct('div');
        $this->class = 'carousel slide';
        $this->id = $id;
        $this->{"data-bs-ride"} = "carousel"; // Faz o carrossel girar sozinho
        $this->style = "margin: 20px auto; max-width: 500px; display: block;";


        // 1. INDICADORES (Gerados dinamicamente com base no array de imagens)
        $indicator = new Element('div');
        $indicator->class = 'carousel-indicators';

        foreach ($images as $index => $imgData) {
            $btn = new Element('button');
            $btn->type = 'button';
            $btn->{"data-bs-target"} = "#{$id}";
            $btn->{"data-bs-slide-to"} = (string)$index;
            $btn->{"aria-label"} = "Slide " . ($index + 1);

            // Apenas o primeiro indicador recebe o estado ativo
            if ($index === 0) {
                $btn->class = 'active';
                $btn->{"aria-current"} = "true";
            }

            $indicator->add($btn);
        }
        $this->add($indicator);

        // 2. ITEMS / IMAGENS (Gerados dinamicamente)
        $inner = new Element('div');
        $inner->class = "carousel-inner";

        foreach ($images as $index => $imgData) {
            $item = new Element('div');

            // O primeiro item DEVE ter a classe active
            $item->class = ($index === 0) ? "carousel-item active" : "carousel-item";
            $item->style = "height: 350px;";
            $img = new Element('img');
            $img->src = $imgData['src'];
            $img->class = "d-block w-100";
            $img->style = "object-fit: cover; height: 100%;";
            $img->alt = $imgData['alt'] ?? "Imagem";
            $item->add($img);
            $inner->add($item); // Adiciona o item corrigido no container interno
        }
        $this->add($inner);

        // 3. BOTÕES DE CONTROLE (Anterior / Próximo)
        $btn_prev = new Element('button');
        $btn_prev->class = "carousel-control-prev";
        $btn_prev->type = "button";
        $btn_prev->{"data-bs-target"} = "#{$id}";
        $btn_prev->{"data-bs-slide"} = "prev";

        $prev_icon = new Element("span");
        $prev_icon->class = "carousel-control-prev-icon";
        $prev_icon->{"aria-hidden"} = "true";
        $btn_prev->add($prev_icon);

        $btn_next = new Element('button');
        $btn_next->class = "carousel-control-next";
        $btn_next->type = "button";
        $btn_next->{"data-bs-target"} = "#{$id}";
        $btn_next->{"data-bs-slide"} = "next";

        $next_icon = new Element("span");
        $next_icon->class = "carousel-control-next-icon";
        $next_icon->{"aria-hidden"} = "true";
        $btn_next->add($next_icon);

        $this->add($btn_prev);
        $this->add($btn_next);
    }
}
