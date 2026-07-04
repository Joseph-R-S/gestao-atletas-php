<?php

namespace Livro\Widgets\Form;

use Livro\Widgets\Base\Element;

class Text extends Field implements FormElementInterface
{
    private mixed $height = '100px';

    public function setSize(mixed $width, mixed $height = null): void
    {
        $this->size = $width;

        if (isset($height)) {
            $this->height = is_numeric($height) ? $height . 'px' : $height;
        }
    }

public function show(): void
    {
        // 1. Instanciamos la etiqueta textarea con la clase base de Bootstrap 5
        $tag = new Element('textarea');
        $tag->class = 'form-control';
        $tag->name = $this->name;

        // 🌟 SOLUCIÓN: Quitamos el width inline. Dejamos que Bootstrap controle el ancho.
        // Mantenemos la altura dinámica y permitimos el resize vertical si el usuario lo desea.
        $tag->style = "height: {$this->height}; resize: vertical;";
        $tag->style .= "resize: none;";

        // 2. Si no es editable, aplicamos el bloqueo nativo (readonly o disabled)
        if (!parent::getEditable()) {
            $tag->readonly = '1';
            $tag->class .= ' bg-light'; // Toque visual de Bootstrap para campos bloqueados
        }

        // 3. Sanitizamos y agregamos el contenido
        $tag->add(htmlspecialchars((string)$this->value));

        // 4. Inyectamos las propiedades dinámicas externas
        if ($this->properties) {
            foreach ($this->properties as $property => $value) {
                // 🌟 Ignoramos 'size' para evitar que meta size="12" en el HTML del textarea
                if ($property === 'size') {
                    continue;
                }

                if ($property === 'class') {
                    $tag->class .= " {$value}";
                } elseif ($property === 'style') {
                    $tag->style .= "; {$value}";
                } else {
                    $tag->$property = $value;
                }
            }
        }
        
        $tag->show();
    }
}
