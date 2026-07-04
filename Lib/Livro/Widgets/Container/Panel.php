<?php

namespace Livro\Widgets\Container;

use Livro\Widgets\Base\Element;

/**
 * Empacota elementos em painel Bootstrap 5 (Cards)
 * @author Pablo Dall'Oglio / Adaptado para Bootstrap 5
 */
class Panel extends Element
{
    private Element $body;
    private Element $footer;

    /**
     * Constrói o painel
     */
    public function __construct($panel_title = NULL)
    {
        parent::__construct('div');
        // 'card shadow-sm mb-4' (añade sombra sutil y margen inferior)
        $this->class = 'card shadow-sm mb-4';

        if ($panel_title) {
            $this->setTitle($panel_title);
        }

        $this->body = new Element('div');
        // 'card-body p-4' (más padding interno para dar aire al diseño)
        $this->body->class = 'card-body p-4';
        parent::add($this->body);

        $this->footer = new Element('div');
        // 🏁 CORREÇÃO BS5: 'panel-footer' se convierte en 'card-footer bg-white border-top-0 p-3'
        // 'border-top-0' remueve la línea tosca y 'bg-white' mantiene la continuidad si el body es blanco
        $this->footer->class = 'card-footer bg-white border-top-1 p-2';
    }

    public function setTitle(string $panel_title) : void
    {
        $head = new Element('div');
        // 'panel-heading' se convierte en 'card-header bg-light py-3'
        $head->class = 'card-header bg-light py-3';

        // 'h5' con clases de tipografía se ve más estilizado y limpio que un h4 plano
        $label = new Element('h5');
        $label->class = 'card-title mb-0 text-secondary fw-bold';
        $label->add($panel_title);

        $head->add($label);
        parent::add($head);
    }

    public function getTitle() : string
    {
        return $this->panel_title;
    }

    /**
     * Adiciona conteúdo
     */
    public function add(mixed $content)
    {
        $this->body->add($content);
    }

    /**
     * Adiciona rodapé
     */
    public function addFooter(Element $footer)
    {
        $this->footer->add($footer);
        parent::add($this->footer);
    }
}
