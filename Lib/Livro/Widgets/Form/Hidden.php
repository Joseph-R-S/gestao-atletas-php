<?php

namespace Livro\Widgets\Form;

use Livro\Widgets\Base\Element;

class Hidden extends Field implements FormElementInterface
{
    public function show(): void
    {
        // 1. Instanciamos la etiqueta HTML de tipo input
        $tag = new Element('input');
        $tag->type = 'hidden';
        $tag->name = $this->name;
        $tag->value = $this->value;
        
        // 2. Cargamos propiedades dinámicas adicionales por si necesitas pasarle un id o atributo extra
        if ($this->properties) {
            foreach ($this->properties as $property => $value) {
                $tag->$property = $value;
            }
        }
        
        // 3. Renderiza el elemento HTML oculto en el DOM
        $tag->show();
    }
}