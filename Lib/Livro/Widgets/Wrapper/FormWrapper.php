<?php

namespace Livro\Widgets\Wrapper;

use Livro\Widgets\Container\Panel;
use Livro\Widgets\Form\Form;
use Livro\Widgets\Base\Element;
use Livro\Widgets\Form\Button;
use Livro\Widgets\Form\CheckGroup;
use Livro\Widgets\Form\Combo;
use Livro\Widgets\Form\Hidden;
use Livro\Widgets\Form\RadioGroup;

class FormWrapper
{
    private Form $decorated;
    private array $children = [];

    public function __construct(Form $form)
    {
        $this->decorated = $form;
    }

    public function add(mixed $child): void
    {
        $this->children[] = $child;
    }

    //chama sempre que o metodo não existe
    //redireciona para decorated
    public function __call(string $method, array $parameters): mixed
    {
        return call_user_func_array([$this->decorated, $method], $parameters);
    }

    public function show(): void
    {
        $element = new Element('form');
        // formulario horizontal
        $element->class = 'row g-3';
        $element->enctype = 'multipart/form-data';
        $element->method = 'POST';
        $element->name = $this->decorated->getName();
        $element->width = '100%';

        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $element->add($child);
            }
        } else {
            foreach ($this->decorated->getFields() as $field) {

                $size = $field->getSize();

                if ($size) {
                    $gridClass = 'col-md-' . $size;
                } else {
                    $gridClass = 'col-md-12';
                }

                $group = new Element('div');
                $group->class = $gridClass;

                // 1. El Label PRINCIPAL siempre mantiene sus negritas fijas
                $label = new Element('label');
                $label->class = 'form-label fw-semibold d-block';
                $label->for = $field->getName();
                $label->add($field->getLabel());

                // 2. Clases exclusivas para componentes
                if ($field instanceof Combo) {
                    $field->setProperty('class', 'form-select');
                } elseif ($field instanceof RadioGroup || $field instanceof CheckGroup) {
                    // Contenedor flex para que las opciones se pongan de lado a lado limpiamente
                    $field->setProperty('class', 'd-flex flex-wrap gap-3 mt-2');
                } elseif ($field instanceof Hidden) {
                    $field->setProperty('class', 'visually-hidden');
                    $label->class = 'visually-hidden';
                    $group->class = 'visually-hidden';
                } else {
                    $field->setProperty('class', 'form-control');
                }

                $group->add($label);
                $group->add($field);

                // TEXTO DE AYUDA (Removido el bloque duplicado de abajo que causaba conflictos)
                $helpText = $field->getProperty('form-text');
                if ($helpText) {
                    $helpElement = new Element('div');
                    $helpElement->class = 'form-text text-muted small mt-1';
                    $helpElement->add($helpText);

                    $field->setProperty('aria-describedby', 'help_' . $field->getName());
                    $helpElement->id = 'help_' . $field->getName();
                    $group->add($helpElement);
                }

                $element->add($group);
            }
        }

        $footer = new Element('div');
        $footer->class = 'offset-sm-1 col-sm-10 d-flex gap-2';
        foreach ($this->decorated->getAction() as $label => $action) {
            $name = strtolower(str_replace(' ', '_', $label));
            $button = new Button($name);

            $customClass = $action->getProperty('class');
            if ($customClass) {
                $button->setProperty('class', $customClass);
            } else {
                $button->setProperty('class', 'btn btn-primary');
            }

            $button->setAction($action, $label);
            $button->setFormName($this->decorated->getName());
            $footer->add($button);
        }
        $panel = new Panel($this->decorated->getTitle());
        $panel->add($element);
        $panel->addFooter($footer);
        $panel->show();
    }
}
