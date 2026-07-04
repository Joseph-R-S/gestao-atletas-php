<?php

namespace Livro\Widgets\Form;


use Livro\Control\ActionInterface;
use Livro\Widgets\Base\Element;

class Form 
{
    protected string $title = '';
    protected string $name;
    protected array $fields = []; 
    protected array $actions = []; 

    public function __construct(string $name = 'my_form')
    {
        $this->setName($name);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
   
    //*
    /* $size usando classe de bootstrap col-md-1  ate col-md-12
    */
    public function addField(string $label, FormElementInterface $object, mixed $size = 'col-md-12'): void
    {
        // Uso estricto de métodos del contrato
        $object->setSize($size);
        $object->setLabel($label);
        $this->fields[$object->getName()] = $object;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function addAction(string $label, ActionInterface $action): void
    {
        $this->actions[$label] = $action;
    }

    public function getAction(): array
    {
        return $this->actions;
    }

    public function setData(object $object): void
    {
        foreach ($this->fields as $name => $field) {
            //Acceso dinámico a las propiedades del objeto de datos ($object->$name)
            if ($name and isset($object->$name)) {
                $field->setValue($object->$name); 
            }
        }
    }

    public function getData($type = 'stdClass'): object
    {
        $object = new $type;
        foreach ($this->fields as $name => $field) {
            $value = $_POST[$name] ?? '';
            $object->$name = $value; // Creación dinámica de la propiedad ($object->$name)
        }
        return $object;
    }
}
?>
