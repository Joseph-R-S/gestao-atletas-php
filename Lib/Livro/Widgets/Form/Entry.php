<?php

namespace Livro\Widgets\Form;

use Livro\Widgets\Base\Element;

class Entry extends Field implements FormElementInterface
{
    public function show(): void
    {
        // 1. Instanciamos la etiqueta HTML de tipo input
        $tag = new Element('input');
        $tag->class = 'form-control';
        $tag->type = 'text';
        $tag->name = $this->name;
        $tag->value = $this->value;
        $tag->id = $this->name;
        

        // 2. Verificamos si el campo es editable (usando $this de forma directa)
        if (!$this->getEditable()) {
            $tag->readonly = '1';
        }

        // 3. Cargamos las propiedades HTML dinámicas adicionales (como maxlenght,placeholder, etc.)
        if ($this->properties) {
            foreach ($this->properties as $property => $value) {
                // Si el usuario define un estilo personalizado externamente, lo sumamos al width existente
                if ($property === 'style') {
                    $tag->style .= "; {$value}";
                } else {
                    $tag->$property = $value;
                }
            }
        }
        
        // 4. Renderiza el elemento HTML final en la pantalla
        $tag->show();
    }
}