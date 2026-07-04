<?php

namespace Livro\Widgets\Form;

use Livro\Widgets\Base\Element;

class Combo extends Field implements FormElementInterface
{
    private array $items = []; // Inicializado como array vacío para evitar Fatal Errors

    /**
     * Adiciona os itens do combo (chave => valor)
     */
    public function addItems(array $items): void
    {
        $this->items = $items;
    }

    // El método setFormName fue removido porque un select no lo necesita ni lo usa en show()

    /**
     * Exibe o widget na tela
     */
    public function show(): void
    {
        // 1. Instanciamos la etiqueta principal de tipo select
        $tag = new Element('select');
        $tag->class = 'form-select';
        $tag->name = $this->name; // Propiedad heredada de Field
        $tag->id = $this->name;

        // 2. Creamos la opción por defecto en blanco (Valor 0 o vacío)
        $option = new Element('option');
        $option->add('');
        $option->value = ''; 
        $tag->add($option);

        // 3. Iteramos sobre los ítems provistos para armar las opciones
        if ($this->items) {
            foreach ($this->items as $chave => $item) {
                $option = new Element('option');
                $option->value = $chave; 
                $option->add($item); 

                // Si la clave coincide con el valor actual del campo (heredado de Field), se marca como seleccionado
                if ($chave == $this->value) {
                    $option->selected = 1;
                }
                $tag->add($option);
            }
        }

        // 4. Verificamos si es editable. Usamos 'disabled' ya que HTML no soporta 'readonly' en selectores
        if (!$this->getEditable()) {
            $tag->disabled = '1';
        }

        // 5. Inyectamos clases de Bootstrap o propiedades dinámicas externas (sumando estilos si existen)
        if ($this->properties) {
            foreach ($this->properties as $property => $value) {
                if ($property === 'size') {
                    continue; 
                }

                if ($property === 'style') {
                    $tag->style .= "; {$value}";
                } else {
                    $tag->$property = $value;
                }
            }
        }

        // 6. Renderizamos el select completo
        $tag->show();
    }
}