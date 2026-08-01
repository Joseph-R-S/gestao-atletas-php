<?php

namespace Livro\Widgets\Datagrid;

use Livro\Control\ActionInterface;

class Datagrid
{
    private array $columns = [];
    private array $items = [];
    private array $actions = [];
    private $properties = [];

    public function __construct()
    {
        $this->columns = [];
        $this->actions = [];
        $this->items = [];
        $this->properties = [];
    }

    //metodo para adicionar una Columna 
    public function addColumn(DatagridColumn $object): void
    {
        $this->columns[] = $object;
    }

    public function addAction(string $label, ActionInterface $action, string $field, $imagem = null): void
    {
        $this->actions[] = [
            'label' => $label,
            'action' => $action,
            'field' => $field,
            'imagem' => $imagem
        ];
    }

    public function addItem(object $object): void
    {
        $this->items[] = $object;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function clear(): void
    {
        $this->items = [];
    }

    public function setProperty(string $property, mixed $value): void
    {
        $this->properties[$property] = $value;
    }

    public function getProperty(): mixed
    {
        return $this->properties;
    }
}
