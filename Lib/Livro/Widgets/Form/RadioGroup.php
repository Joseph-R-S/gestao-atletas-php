<?php

namespace Livro\Widgets\Form;

use Livro\Widgets\Base\Element;

class RadioGroup extends Field implements FormElementInterface
{
    private array $items = [];

    /**
     * Adiciona os elementos do Radio (chave => valor)
     */
    public function addItems(array $items): void
    {
        $this->items = $items;
    }

public function show(): void
    {
        // 1. Creamos la caja que envuelve todas las opciones (masculino, feminino)
        $tag = new Element('div');
        
        // 🌟 Forzamos a que las opciones individuales se alineen de lado a lado con Flexbox
        $tag->class = 'd-flex flex-row flex-wrap gap-1 align-items-center mt-0';

        if ($this->items) {
            foreach ($this->items as $index => $label_text) {
                
                // ID único para enlazar correctamente cada círculo con su texto
                $id_unico = $this->name . '_' . $index;

                // Contenedor individual de la opción
                $wrapper = new Element('div');
                $wrapper->class = 'd-flex flex-wrap gap-3 mt-2';

                $radio = new Element('input');
                $radio->type  = 'radio';
                $radio->name  = $this->name;
                $radio->value = $index;
                $radio->id    = $id_unico;
                $radio->class = 'form-check-input';

                if ($index == $this->value) {
                    $radio->checked = '1';
                }

                $label = new Element('label');
                $label->class = 'form-check-label';
                $label->for   = $id_unico;
                $label->add($label_text);

                // Armando la estructura oficial de Bootstrap 5
                $wrapper->add($radio);
                $wrapper->add($label);
                
                // Añadimos la opción al contenedor horizontal
                $tag->add($wrapper);
            }
        }
        
        // Renderizamos el conjunto completo
        $tag->show();
    }
}
