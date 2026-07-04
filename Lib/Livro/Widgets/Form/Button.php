<?php

namespace Livro\Widgets\Form;

use Livro\Control\ActionInterface;
use Livro\Widgets\Base\Element;

class Button extends Field implements FormElementInterface
{
    private ?ActionInterface $action = null;
    private string $label = '';
    private string $formName = '';

    /**
     * Define a ação do botão (função a ser executada)
     * Usamos el $label recibido aquí para guardarlo en la propiedad local
     */
    public function setAction(ActionInterface $action, string $label): void {
        $this->action = $action;
        $this->label = $label;
    }

    /**
     * Define o nome do formulario para a ação do botão
     */
    public function setFormName(string $name): void {
        $this->formName = $name;
    }

    public function show(): void
    {
        // 1. Instanciamos la etiqueta HTML button
        $tag = new Element('button');
        $tag->name = $this->name; // Propiedad heredada de Field
        $tag->type = 'button';
        
        // 2. Insertamos el texto del botón
        $tag->add($this->label);

        // 3. Si el botón tiene asignado un valor (heredado de Field), se lo pasamos al atributo HTML value
        if ($this->value) {
            $tag->value = $this->value;
        }

        // 4. Armamos el comportamiento dinámico JavaScript para el envío del formulario
        if ($this->action AND $this->formName) {
            $url = $this->action->serialize();
            $tag->onClick = "document.{$this->formName}.action='{$url}'; " .
                            "document.{$this->formName}.submit();";
        }

        // 5. Cargamos las propiedades HTML dinámicas adicionales (como clases de Bootstrap)
        if ($this->properties) { // Propiedad heredada de Field
            foreach ($this->properties as $property => $value) {
                $tag->$property = $value;
            }
        }

        // 6. Renderizamos en pantalla
        $tag->show();
    }
}