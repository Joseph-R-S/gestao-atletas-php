<?php

namespace Livro\Widgets\Form;

use Livro\Control\ActionInterface;
use Livro\Widgets\Base\Element;

class Button extends Field implements FormElementInterface
{
    private ?ActionInterface $action = null;
    private string $label = '';
    private string $formName = '';
    private bool $isAjax = false; // <-- Nueva propiedad

    public function setAction(ActionInterface $action, string $label): void
    {
        $this->action = $action;
        $this->label = $label;
    }

    public function setFormName(string $name): void
    {
        $this->formName = $name;
    }

    /**
     * Define si la acción se ejecutará vía Ajax
     */
    public function setAjax(bool $isAjax = true): void
    {
        $this->isAjax = $isAjax;
    }

    public function show(): void
    {
        $tag = new Element('button');
        $tag->name = $this->name;
        $tag->type = 'button';
        $tag->add($this->label);

        if ($this->value) {
            $tag->value = $this->value;
        }

        if ($this->action and $this->formName) {
            $url = $this->action->serialize();

            if ($this->isAjax) {
                // Comportamiento Ajax: recolecta los datos y los envía vía fetch/XMLHttpRequest
                $tag->onClick = "executaAjax('{$url}', '{$this->formName}');";
            } else {
                // Comportamiento Submit tradicional
                $tag->onClick = "document.{$this->formName}.action='{$url}'; " .
                    "document.{$this->formName}.submit();";
            }
        }

        if ($this->properties) {
            foreach ($this->properties as $property => $value) {
                $tag->$property = $value;
            }
        }

        $tag->show();
    }
}
